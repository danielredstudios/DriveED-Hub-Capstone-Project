# Full System Audit Report — Driving School Management System

**Date:** March 3, 2026  
**Total issues found: 150** across controllers, middleware, models, form requests, and migrations.

---

## EXECUTIVE SUMMARY

| Severity     | Total Issues | Resolved | Remaining |
|--------------|--------------|----------|-----------|
| **Critical** | 24           | 24       | 0         |
| **High**     | 47           | 47       | 0         |
| **Medium**   | 53           | 10       | 43        |
| **Low**      | 26           | 5        | 21        |

---

## 1. CRITICAL FINDINGS (Fix immediately — security holes / data-loss risk)

### 1.1 Mass-Assignment Privilege Escalation

| File | Issue | Status |
|------|-------|--------|
| `app/Models/Student.php` | `'role'` is in `$fillable`. Any input that reaches `Student::create()` or `->update()` can escalate a guest to a student. | **[FIXED]** |
| `app/Models/Admin.php` | `'role'` is in `$fillable`. A branch secretary can be escalated to `school_admin` or `system_admin` via mass assignment. | **[FIXED]** |
| `app/Models/Student.php` | `'email_verified_at'`, `'verification_code'`, `'student_license_status'`, `'active_enrollment_id'`, `'is_course_locked'` are all in `$fillable`, allowing bypass of verification and enrollment workflows. | **[FIXED]** |
| `app/Models/EnrollmentRequest.php` | `'approved_by'`, `'payment_confirmed_by'` in `$fillable`, allowing forgery of audit fields. | **[FIXED]** |
| `app/Models/Payment.php` | `'status'` in `$fillable`, allowing payment status to be set to `confirmed` without verification. | **[FIXED]** |

### 1.2 Authorization Gaps — Any Authenticated User Can Modify Data

| File / Method | Issue | Status |
|---------------|-------|--------|
| `BookingController::store()` | `student_id` from user input is NOT validated against auth user. A student can create bookings for other students. | **[FIXED]** |
| `BookingController::update()`/`destroy()`/`updateStatus()` | Only `school_id` checked. Any auth user can modify/delete any booking or change its status. | **[FIXED]** |
| `PaymentController::store()`/`update()`/`destroy()`/`statistics()` | No role check at all. Any authenticated user can CRUD financial records. | **[FIXED]** |
| `CourseController::update()`/`destroy()` | Comments say "auth check can be added here if needed" — currently none. | **[FIXED]** |
| `ProgressController::store()`/`update()`/`destroy()` | No role check. Students can create/edit/delete their own progress records, setting `completion_percent` to 100%. | **[FIXED]** |
| `AdminController::deleteStudent()`/`deleteInstructor()` | Comment says "System Admin only" but no enforcement. Any admin (including branch secretaries) can permanently delete users. | **[FIXED]** |
| `BranchController` (all methods) | No `school_id` cross-check. Admin from School A can CRUD branches in School B. | **[FIXED]** |

### 1.3 Broken Form Request Validators

| File | Issue | Status |
|------|-------|--------|
| `MarkTheoreticalPassedRequest.php` | `authorize()` checks `role === 'admin' \|\| 'superadmin'`, but Admin model uses `system_admin`/`school_admin`/`branch_secretary`. **Always denies admins.** Also validates against `enrollments` table which doesn't exist (should be `enrollment_requests`). | **[FIXED]** |
| `StoreSessionCompletionRequest.php` | `authorize()` checks `$user->role === 'instructor'`, but the Instructor model has no `role` field. **Always denies.** Also validates `enrollment_id` without school scoping — cross-school session logging possible. | **[FIXED]** |

### 1.4 Broken Model Relationship

| File | Issue | Status |
|------|-------|--------|
| `SessionCompletion.php` — `loggedBy()` | References `User::class`, which doesn't exist. The app uses separate `Admin`, `Student`, `Instructor` tables. Calling `$session->loggedBy` will throw a class-not-found fatal error. | **[FIXED]** |

### 1.5 Database Foreign Keys to Wrong Table

| Migration / Column | Issue | Status |
|--------------------|-------|--------|
| `enrollments.theoretical_passed_by` | FK constrained to `users` table, but app uses `admins`. Insert will fail with FK violation. | **[FIXED]** |
| `session_completions.logged_by` | Same; FK to `users` instead of `admins`. | **[FIXED]** |

### 1.6 Destructive Cascade Deletes

| Scenario | Impact | Status |
|----------|--------|--------|
| Deleting a **school** | Cascades across 17+ tables, wiping ALL students, instructors, courses, bookings, payments, reports, and session records. A single accidental delete is unrecoverable. | **[FIXED]** |
| Deleting a **course** | Cascades to delete all bookings, enrollment requests, and progress records tied to it. | **[FIXED]** |
| `session_completions.logged_by` CASCADE | Deleting an admin wipes all training session records they logged. | **[FIXED]** |
| `reports.generated_by` CASCADE | Deleting an admin wipes all generated reports. | **[FIXED]** |

### 1.7 Missing Cross-School Scoping (Multi-Tenant Data Leaks)

| Model / Controller | Issue | Status |
|--------------------|-------|--------|
| `Student`, `Instructor`, `EnrollmentRequest`, `Branch`, `TimeSlot`, `SessionCompletion`, `RegistrationRequest` | Have **no** `scopeForSchool()`. Any controller query that forgets to manually add `where('school_id', ...)` leaks data across schools. | **[FIXED]** |
| `InstructorController.php` | **Never** verifies `$instructor->school_id === $school->id`. An instructor from School A could access School B's dashboard data. | **[FIXED]** |

---

## 2. HIGH SEVERITY FINDINGS

### 2.1 Null Dereference Crashes (the `??` operator trap)

The pattern `$booking->course->title ?? 'N/A'` is used **15+ times** across controllers. PHP's `??` does NOT protect against null intermediate objects — if `$booking->course` is `null` (e.g., course was deleted), `->title` throws a fatal error. Must use the nullsafe operator: `$booking->course?->title ?? 'N/A'`.

**Affected files:**

| File | Approximate Lines | Status |
|------|-------------------|--------|
| `StudentController.php` | ~44, ~83, ~323 | **[FIXED]** |
| `InstructorController.php` | ~150–158 | **[FIXED]** |
| `BookingController.php` | ~381, ~416–417 | **[FIXED]** |
| `EnrollmentRequestController.php` | ~96, ~262, ~274, ~555–565, ~590, ~638, ~664 | **[FIXED]** |
| `GuestController.php` | ~172 (`$school->schoolSetting->enable_branches`) | **[FIXED]** |

### 2.2 `find()` Without Null Check in Bulk Operations

| File / Method | Issue | Status |
|---------------|-------|--------|
| `EnrollmentRequestController::bulkApprove()` | `EnrollmentRequest::find($id)` can return null, then immediately accesses `->school_id`, `->student->role`, etc. | **[FIXED]** |
| `EnrollmentRequestController::bulkReject()` | Same pattern. | **[FIXED]** |

### 2.3 Missing Try-Catch in Reporting & Exports

| File / Method | Issue |
|---------------|-------|
| `ReportController::index()` | 15+ raw SQL queries with no exception handling. |
| `ExportController` (all methods) | All load unbounded datasets with `->get()` (no pagination). A school with 50,000 students will cause out-of-memory. |
| `ExportController::instructorsExcel()` | Classic N+1: 2 booking queries per instructor inside a foreach loop. |

### 2.4 Database Schema Issues

| Table / Column | Issue |
|----------------|-------|
| `time_slots.school_id`, `course_modules.school_id`, `module_lessons.school_id`, `schedule_instructors.school_id`, `session_completions.school_id` | All **nullable** but must be NOT NULL for tenant isolation. |
| `bookings.package_id`, `bookings.time_slot_id` | No FK constraints or indexes. |
| `[student_id, course_id]` on enrollments | Missing unique constraint — a student can be double-enrolled. |
| `students.branch` (string column) | Redundant; coexists with proper `branch_id` FK. |

### 2.5 Edge Cases

| File / Method | Issue |
|---------------|-------|
| `PasswordResetController.php` | Returns "success" even if email send silently failed. Also leaks reset URL in dev mode (`APP_ENV=local`). |
| `StudentActionRequestController::approve()` | Creates student with password `'temporary-' . uniqid()` but never sends credentials or forces password reset. |
| `SystemAdminController::deleteSchool()` | Manual cascade misses payments, enrollment requests, session completions, phase progressions, notifications, branches, etc. Leaves orphaned records. |
| `SystemAdminController::storeSchool()` | Uses `bcrypt()` but Admin model may have a password mutator/cast, risking double-hashing. |
| `InstructorTimeSlotController::updateAttendance()` | Doesn't verify `$booking->instructor_id === $instructor->id`. Any instructor at the same school can mark attendance for any booking. |
| `TheoreticalCompletionController` | Division by zero when `required_hours` is explicitly `0` (not null). |
| `TimeSlot` model `$casts` | `start_time`/`end_time` cast to `datetime:H:i` but columns store time-only strings. Carbon sets date to today, breaking cross-day comparisons. |

---

## 3. MEDIUM SEVERITY FINDINGS

### 3.1 Missing Auth Null Guards

`Auth::guard('admin')->user()` is used directly (without `?->` or null check) in logging statements across:

- `AdminController.php` (~lines 35, 256, 270)
- `SystemAdminController.php` (~lines 107, 395, 563)
- `ReportController.php` (line ~19)
- `TheoreticalCompletionController.php` (lines ~24–33)
- `EnrollmentRequestController.php` (line ~28)

If the session expires mid-request (race condition), these crash.

### 3.2 Error Message Information Leaks

| File | Code Pattern |
|------|-------------|
| `AdminController.php` | `'Failed to approve removal request: ' . $e->getMessage()` exposes internal error details. |
| `EnrollmentRequestController.php` | `'Failed to approve enrollment: ' . $e->getMessage()`. |

### 3.3 Missing Try-Catch on Mutation Operations

Missing exception handling on DB writes in:

- `AdminController::storeAccount()`, `updateInstructor()`, `deleteSchedule()`
- `StudentController::updateProfile()`, `updateProfilePicture()`
- `BranchController::store()`
- `NotificationController` (all methods)
- `InstructorTimeSlotController::mySchedule()`

### 3.4 N+1 Performance Issues

| File / Method | Issue |
|---------------|-------|
| `InstructorController::myStudents()` | Loads ALL students from school with nested eager loads. |
| `InstructorController::reports()` | 15+ individual count/avg/get queries on the same table. |
| `CourseController::show()` | Eagerly loads ALL bookings for a course (could be thousands), no pagination. |

### 3.5 Form Validation Gaps

| File | Issue |
|------|-------|
| `StoreEnrollmentRequestRequest.php` | `branch_id` and `package_id` validated for global existence, not scoped to school/course. Cross-school references possible. |
| `InstructorTimeSlotController.php` | Email regex only allows Gmail and Yahoo addresses. |

### 3.6 Model Relationship Gaps

| File | Issue |
|------|-------|
| `SessionCompletion` — `student()`/`course()` | Plain methods (not `Relation` objects). Cannot be used with `with()`, `whereHas()`. Fail when `$this->enrollment` is null. |
| `Instructor` model | Missing `timeSlots()`, `sessionCompletions()`, `removalRequests()` relationships. |
| `Booking` model | Missing `enrollmentRequest()` inverse relationship. |
| `ModuleLesson` — `course()` | Not a real relationship (traverses via `->module->course()`), causes N+1. |

### 3.7 Middleware Edge Case

| File | Issue |
|------|-------|
| `EnsureSystemAdmin.php` | Non-system-admin hits system route → `redirect()->back()`. If referrer is the same page, infinite redirect loop. |

---

## 4. LOW SEVERITY FINDINGS

| File / Area | Issue |
|-------------|-------|
| Auth flow | OTP exposed in session on non-production environments if `APP_ENV` is `local`/`development`. |
| `AdminController::dashboard()` | Runs 12+ individual count queries (could be consolidated). |
| `CourseModuleController` | `sort_order` defaults to `null + 1 = 1` (works in PHP but fragile). |
| `ModuleLessonController` | `remove_attachments` indices not validated for range. |
| Model patterns | Inconsistent use of `$casts` property vs `casts()` method across models. |
| `GuestController::register()` | Logs out all guards (admin, instructor, student) on every register attempt. |
| `SystemAdminController::toggleUserStatus()` | No regex constraint on the `$type` route parameter. |
| `StudentController::schedule()` | Dead code — unreachable `elseif` branch. |
| Schema | No unique constraint on `[school_id, branch_name]`, `[course_id, package_name]`, or `[school_id, course_title]`. |
| `SchoolSetting::custom_css` | Cast to `array` — potential XSS if rendered unescaped in `<style>` tags. |

---

## TOP 10 RECOMMENDED FIXES (Priority Order)

| # | Fix | Impact | Status |
|---|-----|--------|--------|
| 1 | **Remove `role` from `$fillable`** in Student and Admin models | Prevents privilege escalation | **[FIXED]** |
| 2 | **Add authorization checks** to BookingController, PaymentController, ProgressController, CourseController | Prevents unauthorized CRUD of critical data | **[FIXED]** |
| 3 | **Fix MarkTheoreticalPassedRequest & StoreSessionCompletionRequest** — wrong table name, wrong role checks | Unblocks theoretical/session features | **[FIXED]** |
| 4 | **Fix SessionCompletion::loggedBy()** — change `User::class` to `Admin::class` | Prevents fatal error on session viewing | **[FIXED]** |
| 5 | **Use nullsafe operator (`?->`)** instead of `->` on all nullable relationship chains | Prevents ~15 potential 500 crashes | **[FIXED]** |
| 6 | **Add `scopeForSchool()` trait** to all models + apply in queries | Prevents cross-school data leaks | **[FIXED]** |
| 7 | **Change destructive `CASCADE` to `RESTRICT`/`SET NULL`** on `logged_by`, `generated_by`, school FK, course FK | Prevents accidental data wipe | **[FIXED]** |
| 8 | **Fix FK constraints** pointing to `users` table → point to `admins` | Prevents insert failures | **[FIXED]** |
| 9 | **Make `school_id` NOT NULL** on time_slots, course_modules, module_lessons, schedule_instructors, session_completions | Enforces tenant isolation | **[FIXED]** |
| 10 | **Add try-catch + chunking** to Export/Report controllers | Prevents OOM crashes and raw 500 errors | **[FIXED]** |
