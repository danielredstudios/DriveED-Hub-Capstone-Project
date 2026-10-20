# Phase 1 & Phase 2 — Completed Changes

> Driving School Management System — Laravel Multi-Tenant Application  
> Branch: `Tiara-Branch`  
> Date: February 2025

---

## Table of Contents

1. [Phase 1: Security & Critical Fixes](#phase-1-security--critical-fixes)
2. [Phase 2: MVC Architecture & Code Cleanup](#phase-2-mvc-architecture--code-cleanup)
3. [Files Modified](#files-modified)
4. [Files Deleted](#files-deleted)
5. [Summary Statistics](#summary-statistics)

---

## Phase 1: Security & Critical Fixes

### 1.1 Cross-Tenant Data Access (C1–C3)

**Problem:** `BookingController`, `PaymentController`, and `ProgressController` accepted any record ID without verifying it belonged to the current school, allowing cross-tenant data access.

**Fix:** Added `abort_if($model->school_id !== $school->id, 404)` guard to all exposed methods.

| Controller | Methods Protected |
|---|---|
| `BookingController` | `show`, `update`, `destroy`, `updateStatus`, `confirmBooking`, `removeFromQueue` |
| `PaymentController` | `show`, `edit`, `update`, `destroy` |
| `ProgressController` | `show`, `edit`, `update`, `destroy` |

### 1.2 Mass Assignment Protection (M1)

**Problem:** `Admin`, `Student`, and `Instructor` models were missing `failed_login_attempts`, `locked_until`, and `last_login_at` from their `$fillable` arrays. Account lockout logic silently failed because these fields were blocked by mass assignment protection.

**Fix:** Added all three fields to `$fillable` in each model and registered `locked_until` and `last_login_at` as `datetime` casts.

- `app/Models/Admin.php`
- `app/Models/Student.php`
- `app/Models/Instructor.php`

### 1.3 Broken Relationship (M2)

**Problem:** `EnrollmentRequest::sessionCompletions()` used `hasMany(Progress::class, 'enrollment_request_id')` — wrong model class and non-existent foreign key.

**Fix:** Changed to `hasMany(SessionCompletion::class, 'enrollment_id')`.

### 1.4 Auth Guard Failure (C4)

**Problem:** `CourseModuleController` used `Auth::user()` which calls the default `web` guard (maps to the unused `User` model). Since no user authenticates via the `web` guard, this always returned `null`, crashing all 9 methods.

**Fix:** Added a private `resolveAuthRole()` helper that checks the `admin`, `instructor`, and `student` guards in sequence. Updated all 9 methods to use the resolved role.

### 1.5 Silent Data Loss — Enrollment Foreign Key (C5)

**Problem:** `GuestController::enroll()` used `'student_id'` as the key when creating `EnrollmentRequest` records. The model's `$fillable` uses `learner_id`, so the FK was silently dropped by mass assignment protection — enrollment requests were created without any learner link.

**Fix:** Changed both the duplicate-check query and the create data array to use `'learner_id'`.

### 1.6 Runtime Crash — Type Hint (C6)

**Problem:** `TheoreticalCompletionController::revoke()` had type hint `Enrollment $enrollment`, but the `Enrollment` class was never imported. The correct model is `EnrollmentRequest`.

**Fix:** Changed to `EnrollmentRequest $enrollment`.

### 1.7 Email Enumeration (G40)

**Problem:** `AuthController` returned different error messages for failed login attempts across admin, instructor, and student guards — including "X attempts remaining" — which revealed whether accounts existed.

**Fix:** Normalized all three guards to return: `"The provided credentials do not match our records."`

### 1.8 Password Policy (G44)

**Problem:** Login form accepted `min:6` for passwords, but password reset required `min:8`, creating inconsistency.

**Fix:** Changed login validation to `min:8` across all guards.

### 1.9 Test Route Exposure (L12)

**Problem:** Debug/test routes were accessible in production.

**Fix:** Wrapped all test routes in `if (app()->environment('local', 'development'))`.

### 1.10 Login Rate Limiting (L13, G43)

**Problem:** No rate limiting on authentication endpoints, enabling brute-force attacks.

**Fix:**
- Added `throttle:5,1` to school login POST and system admin login POST
- Added `throttle:3,1` to password reset POST

### 1.11 School Active-Status Check (L19)

**Problem:** Disabled schools could still be accessed via their slug URL.

**Fix:** Added active-status check in `EnsureSchoolContext` middleware: `abort(403)` if `$school->status !== 'active'`.

### 1.12 XSS via Inline Event Handlers (A8, S6, S8, S13)

**Problem:** Blade's `{{ }}` escapes HTML entities but not JavaScript string delimiters inside `onclick` attributes. Names like `O'Brien` or crafted payloads could break out of the JS context.

**Fix:** Replaced all vulnerable `onclick` handlers with `data-*` attributes and event delegation JavaScript in 4 files:

| File | Handlers Replaced |
|---|---|
| `school/admin/user-management.blade.php` | 5 (editStudent, editInstructor, viewStudent, viewInstructor, deleteUser) |
| `system-admin/schools.blade.php` | 1 (confirmDelete) |
| `system-admin/admins.blade.php` | 1 (confirmDeleteAdmin) |
| `system-admin/users.blade.php` | 2 (confirmDeleteUser × 2) |

---

## Phase 2: MVC Architecture & Code Cleanup

### 2.1 Move DB Queries from Views to Controllers

**Problem:** Six Blade templates contained raw Eloquent queries in `@php` / `<?php` blocks, violating MVC separation and causing N+1 query problems.

#### admin/progress.blade.php → ProgressController

**Before:** Each row in the `@foreach` loop ran `Booking::where(...)` per student — classic N+1 problem. An additional unused `@forelse` block duplicated the same query.

**After:** `ProgressController::index()` performs a single bulk query for all bookings, then attaches pre-computed `$progress->completedSessions`, `$progress->totalSessions`, `$progress->currentBooking`, `$progress->nextBooking`, and `$progress->bookingsList` to each progress record. Both Blade query blocks removed.

#### admin/enrollment-requests/index.blade.php → EnrollmentRequestController

**Before:** The controller passed `$requests` with `['student', 'course']` eager loads. The Blade entirely ignored it and ran its own `EnrollmentRequest::with(['learner', 'course', 'approvedBy'])` query with 5 status-filtered sub-collections.

**After:** `EnrollmentRequestController::index()` now uses the correct eager loads (`learner`, `course`, `approvedBy`) and passes `$allRequests`, `$pendingRequests`, `$approvedRequests`, `$completedRequests`, `$cancelledRequests`, `$rejectedRequests` directly. Blade `@php` block removed.

#### guest/dashboard.blade.php → GuestController

**Before:** 3 separate enrollment queries in the Blade (`exists()`, `where('pending')->first()`, `where('approved')->first()`).

**After:** `GuestController::dashboard()` now computes `$hasEnrollment`, `$pendingRequest`, and `$approvedEnrollment` and passes them to the view.

#### guest/courses.blade.php → GuestController

**Before:** Enrollment status query + per-course `file_exists()` call in the Blade. Also contained a redundant `$courses->where('status', 'active')` filter despite the controller already filtering active courses.

**After:** `GuestController::courses()` now computes `$enrolledCourseIds`, `$enrollmentStatuses`, and pre-checks `$course->hasBannerImage` for each course. Redundant active filter removed from the Blade.

#### student/schedule.blade.php → StudentController

**Before:** 4 Eloquent queries (EnrollmentRequest, Booking, EnrollmentRequest again, TimeSlot) plus heavy collection processing — completely duplicating what `StudentController::schedule()` already computed and passed. The Blade ignored all controller data.

**After:** `StudentController::schedule()` now passes all required variables including previously missing `$enrolledCourseIds`, `$cancelledBookings`, `$groupedCancelledBookings`, and `$todayDate`. The entire 55-line `<?php` query block was removed from the Blade.

#### instructor/schedule.blade.php (resolved as dead code)

The route `schools.instructor.schedule` already points to `InstructorTimeSlotController::mySchedule()`, which renders `schedule-new.blade.php` with all data computed in the controller. The old `schedule.blade.php` (2,472 lines with 2 queries + 80 lines of collection processing) was dead code — deleted entirely.

### 2.2 Remove Fake Data (C7)

**Problem:** `ReportController` used `rand(40, 50) / 10` to generate fake 4.0–5.0 star ratings for instructor and course performance stats.

**Fix:** Replaced with `null` and a `TODO: Implement actual rating system` comment. The Blade templates already use `@if($item->average_rating)` guards, so null values simply hide the rating display.

### 2.3 Add DB Transaction (C8)

**Problem:** `BookingController::store()` created a booking and logged it in separate operations — if the log write failed, a partial state could result.

**Fix:** Wrapped `Booking::create()` and `SystemLog::logInfo()` inside `DB::transaction()`.

### 2.4 Delete Dead Code (G21, L11, L18)

| File | Lines | Reason |
|---|---|---|
| `resources/views/school/guest/dashboard-old.blade.php` | ~200 | Superseded by `dashboard.blade.php` |
| `resources/views/school/instructor/schedule.blade.php` | 2,472 | Superseded by `schedule-new.blade.php` |
| `resources/views/partials/sidebar.blade.php` | ~400 | Not included anywhere; divergent from `layouts/app.blade.php` |
| `resources/views/partials/topbar.blade.php` | ~350 | Not included anywhere; divergent from `layouts/app.blade.php` |
| `app/Http/Middleware/SystemAdminMiddleware.php` | ~30 | Unregistered duplicate of `EnsureSystemAdmin` |

**Total dead code removed: ~3,450 lines**

### 2.5 Remove Unused Imports (C16)

| File | Removed Imports |
|---|---|
| `ReportController.php` | `App\Models\Report` |
| `InstructorTimeSlotController.php` | `Monolog\Handler\ElasticaHandler`, `PhpParser\Node\Stmt\Else_` |

---

## Files Modified

### Controllers

| File | Changes |
|---|---|
| `BookingController.php` | School scoping (6 methods) + DB transaction on `store()` + added `DB` import |
| `PaymentController.php` | School scoping (4 methods) |
| `ProgressController.php` | School scoping (4 methods) + bulk booking pre-load in `index()` + added `Booking` import |
| `CourseModuleController.php` | Multi-guard auth helper, replaced `Auth::user()` in all 9 methods |
| `GuestController.php` | Enrollment data moved to `dashboard()` and `courses()` + `file_exists()` pre-check |
| `EnrollmentRequestController.php` | Updated eager loads and status grouping in `index()` |
| `AuthController.php` | Generic error messages, `min:8` password policy |
| `TheoreticalCompletionController.php` | Fixed `revoke()` type hint |
| `ReportController.php` | Removed `rand()` fake ratings + unused `Report` import |
| `StudentController.php` | Expanded `schedule()` to pass all view-required data |
| `InstructorTimeSlotController.php` | Removed unused `ElasticaHandler` and `Else_` imports |

### Models

| File | Changes |
|---|---|
| `Admin.php` | Added 3 security fields to `$fillable` + datetime casts |
| `Student.php` | Added 3 security fields to `$fillable` + datetime casts |
| `Instructor.php` | Added 3 security fields to `$fillable` + datetime casts |
| `EnrollmentRequest.php` | Fixed `sessionCompletions()` relationship |

### Routes & Middleware

| File | Changes |
|---|---|
| `routes/web.php` | Test route gating, rate limiting on login/password-reset |
| `EnsureSchoolContext.php` | School active-status check |

### Views

| File | Changes |
|---|---|
| `admin/progress.blade.php` | Removed 2 inline query blocks, uses controller-computed data |
| `admin/enrollment-requests/index.blade.php` | Removed inline query block |
| `guest/dashboard.blade.php` | Removed 3 inline enrollment queries |
| `guest/courses.blade.php` | Removed enrollment query + `file_exists()` + redundant active filter |
| `student/schedule.blade.php` | Removed 55-line query block |
| `admin/user-management.blade.php` | XSS fix: data-* attributes + event delegation |
| `system-admin/schools.blade.php` | XSS fix: data-* attributes + event delegation |
| `system-admin/admins.blade.php` | XSS fix: data-* attributes + event delegation |
| `system-admin/users.blade.php` | XSS fix: data-* attributes + event delegation |

---

## Files Deleted

| File | Lines | Category |
|---|---|---|
| `resources/views/school/guest/dashboard-old.blade.php` | ~200 | Dead view |
| `resources/views/school/instructor/schedule.blade.php` | 2,472 | Superseded view |
| `resources/views/partials/sidebar.blade.php` | ~400 | Unused partial |
| `resources/views/partials/topbar.blade.php` | ~350 | Unused partial |
| `app/Http/Middleware/SystemAdminMiddleware.php` | ~30 | Unregistered middleware |

---

## Summary Statistics

| Metric | Count |
|---|---|
| **Phase 1 items completed** | 12 |
| **Phase 2 items completed** | 11 |
| **Total items completed** | 23 |
| **Controllers modified** | 11 |
| **Models modified** | 4 |
| **Views modified** | 9 |
| **Views deleted** | 4 |
| **Middleware modified** | 1 |
| **Middleware deleted** | 1 |
| **Routes file modified** | 1 |
| **Dead code removed** | ~3,450 lines |
| **N+1 queries eliminated** | 4 views |
| **XSS vectors fixed** | 9 handlers across 4 files |
| **Security vulnerabilities fixed** | 6 (cross-tenant, email enum, XSS, rate limiting, test routes, school gating) |
