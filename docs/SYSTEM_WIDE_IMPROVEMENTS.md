# System-Wide Improvements Report

**Date:** February 2026  
**Scope:** All system pages EXCEPT Student & Instructor (documented separately in `STUDENT_INSTRUCTOR_IMPROVEMENTS.md`)  
**Methodology:** Code-level review of every Blade view, controller, model, layout, route, and middleware

---

## Table of Contents

1. [School Admin Pages](#1-school-admin-pages)
2. [System Admin Pages](#2-system-admin-pages)
3. [Guest & Authentication Pages](#3-guest--authentication-pages)
4. [Layouts, Routes & Middleware](#4-layouts-routes--middleware)
5. [Controllers & Models](#5-controllers--models)
6. [Priority Matrix](#6-priority-matrix)
7. [Cross-Cutting Issues](#7-cross-cutting-issues)

---

## 1. School Admin Pages

**Files Reviewed:** 20 files (10 main views, 4 subdirectories, partials, AdminController.php)

### 1.1 dashboard.blade.php (~280 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A1 | Add CDN fallback for Chart.js | Chart silently fails if CDN is unavailable |
| A2 | Add `session('error')` flash handling | Only success messages are displayed |
| A3 | Link stat cards to relevant pages (not all to user-management) | All 4 cards link to the same route — poor UX |
| A4 | Add `@include('school.admin.partials.admin-styles')` | Inconsistent with other admin views |
| A5 | Add null check on `$enrollmentData` before `@json` | Throws if null |

### 1.2 user-management.blade.php (1360 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A6 | **Fix duplicate `filterUsers()` function** | Defined twice — second shadows first with different logic |
| A7 | **Fix duplicate `filterByStatus()` function** | Same duplication issue |
| A8 | **Fix XSS in `onclick` handlers** — use `data-*` attributes + event listeners | Raw user data (`name`, `email`) injected into JS via `onclick` attributes |
| A9 | Add `@error` directives to create/edit form fields | Server validation errors not shown per-field |
| A10 | Add pagination to students and instructors tables | All records loaded at once |
| A11 | Replace hardcoded `#667eea` gradient in tab header with `$settings` | Inconsistent with school theming |
| A12 | Fix `window.onclick` global override for modal close | Conflicts with other scripts |

### 1.3 courses.blade.php (1790 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A13 | Move `file_exists(public_path(...))` to controller | Disk I/O in Blade = MVC violation (called 2x) |
| A14 | Add pagination | All courses loaded at once, `@json($courses)` dumps full payload to JS |
| A15 | Add `@error` directives to create/edit forms | No per-field validation |
| A16 | Replace `url($school->slug . '/admin/courses')` with named routes | Fragile URL construction |
| A17 | Move preview modal HTML from JS template literals to Blade partial | Large inline HTML in JS, potential XSS |

### 1.4 bookings.blade.php (~280 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A18 | **Move stats computation to controller** | `$bookings->where('status', ...)->count()` repeated 6+ times in view |
| A19 | Add pagination | All bookings loaded at once |
| A20 | Add null-safe operators: `$booking->student?->name`, `$booking->course?->title` | Throws if related record deleted |
| A21 | Use `loadContent()` instead of `window.location.href` in `createPayment()` | Breaks SPA pattern |
| A22 | Add loading indicator during AJAX status update | No feedback during status change |

### 1.5 payments.blade.php (~310 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A23 | **Move `$payments->where('status','completed')->sum('amount')` to controller** | MVC violation |
| A24 | Add pagination | All payments loaded |
| A25 | Add null-safe: `$payment->booking?->student?->name` | Triple-chained relationship without safety |
| A26 | Add search and date range filtering | Critical for financial data, currently filter-only |

### 1.6 schedules.blade.php (1926 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A27 | Remove ~500 lines of duplicate CSS (redefined `.btn`, `.alert`, `.badge`, grid system) | Conflicts with admin-styles partial |
| A28 | Move calendar date iteration and `$calendarStart`/`$calendarEnd` to controller | Heavy computation in Blade |
| A29 | Limit `allSchedulesData` JSON payload size | Dumps all schedule data to JS |
| A30 | Fix multiple `window.onclick` assignments | Overwrites global click handler |
| A31 | Optimize `getAdminAssignedCount()` / `getSelfSelectedCount()` per timeslot | Potential N+1 queries |
| A32 | Replace calendar `window.location.href` month navigation with AJAX | Breaks SPA pattern |

### 1.7 settings.blade.php (1703 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A33 | Add `@error` directives per field | Only `$errors->any()` at top |
| A34 | Extract Login/Signup customization section (~200 lines) to sub-partial | File is extremely long |
| A35 | Fix `resetToDefaults()` to also reset General tab values | Only resets Colors tab currently |
| A36 | Standardize color fallback pattern (use `?? '#667eea'` consistently) | Mixed patterns throughout |

### 1.8 progress.blade.php (567 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A37 | **Move raw `Booking::where(...)` queries to controller** | Two Eloquent queries per row = N+1, critical MVC violation |
| A38 | Add pagination | All progress records loaded at once |
| A39 | Add search and filter functionality | No way to find specific students |
| A40 | Add null-safe: `$booking->instructor?->name` | Uses null coalescing but not null-safe on chain |

### 1.9 profile.blade.php (~370 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A41 | Remove `* { margin: 0; padding: 0; box-sizing: border-box; }` global CSS reset | Resets ALL elements on page, breaks layout/sidebar |
| A42 | Replace hardcoded `#007bff` with school theming | Inconsistent color |
| A43 | Move `file_exists(storage_path(...))` to controller | Disk I/O in Blade |
| A44 | Add password change option to edit form | Controller supports it, view doesn't |
| A45 | Add `@error` directives to form fields | No per-field validation |
| A46 | Replace `alert()` with Toast system for avatar upload feedback | Inconsistent UX |

### 1.10 removal-requests.blade.php (668 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A47 | Add null-safe: `$request->instructor?->name`, `$request->timeSlot?->date` | Throws if related record deleted |
| A48 | Add null check on `$request->processed_at` before `.diffForHumans()` | Assumes always Carbon instance |
| A49 | Standardize modal visibility pattern (`.modal.active` vs `display: flex`) | Inconsistent with other views |

### 1.11 enrollment-requests/index.blade.php (822 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A50 | **Move `EnrollmentRequest::with([...])` query to controller** | Full Eloquent query in Blade + 5 collection filters |
| A51 | Add pagination | Entire dataset loaded |
| A52 | Add null-safe: `$request->course?->price`, `$request->learner?->name` | Throws if course/learner deleted |
| A53 | Replace `prompt()` for bulk reject reason with a proper modal | Terrible UX |

### 1.12 theoretical/ (3 files: index, passed, show)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A54 | Add null-safe: `$enrollment->student?->name`, `$session->instructor?->name` | Throws if deleted |
| A55 | Replace FontAwesome icons in `passed.blade.php` with Bootstrap Icons | Inconsistent icon system |
| A56 | Replace Bootstrap utility classes in `show.blade.php` with custom CSS | Inconsistent with other views |
| A57 | Replace `confirm()` in form onsubmit with `showConfirm()` system | Inconsistent UX pattern |

### 1.13 reports/index.blade.php (931 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A58 | Replace `<?php ?>` with `@php` | Inconsistent Blade syntax |
| A59 | Add `$isAjax` layout switching | Hardcoded `@extends('layouts.app')` |
| A60 | Add null-safe on `$booking->student?->name`, `$booking->instructor?->name` | In cancellation_details section |

### 1.14 reports/ stubs (3 files: students, instructors, logs)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A61 | Implement actual report functionality (currently "Coming Soon") | Placeholder pages with no data |
| A62 | Replace hardcoded `#764ba2` with `$secondaryColor` | Inconsistent theming |

### 1.15 partials/admin-styles.blade.php (1414 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A63 | Ensure ALL admin views include this partial (dashboard, profile, theoretical/passed, report stubs don't) | Inconsistent styling |
| A64 | Consider splitting CSS, JS, and HTML into separate partials | 1414-line mixed file is hard to maintain |

### 1.16 AdminController.php (1312 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| A65 | Replace 30 individual enrollment trend queries with single `GROUP BY` query | `dashboard()` runs 30 queries for 30-day chart |
| A66 | Add `$isAjax` to `dashboard()` and other methods | Only set in `userManagement()` |
| A67 | Add try/catch and DB transaction to `updateInstructor()` | Inconsistent with `updateStudent()` which has both |
| A68 | Add try/catch to `toggleStudentStatus()` and `toggleInstructorStatus()` | Raw error pages on failure |
| A69 | Relax email regex restriction (only @gmail.com and @yahoo.com allowed) | Many legitimate business emails rejected |

---

## 2. System Admin Pages

**Files Reviewed:** 12 Blade views + system-admin layout + SystemAdminController

### 2.1 login.blade.php (176 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S1 | Replace hardcoded brand name "DriveED Hub" with `config('app.name')` | Hardcoded in `<title>` |
| S2 | Add CAPTCHA or rate-limiting UI indicator | No brute-force protection indicator |

### 2.2 dashboard.blade.php (97 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S3 | **Fix hardcoded "Active" status badge** — use `$school->status` dynamically | Status always shows "Active" regardless of actual value |
| S4 | Limit `$recentActivities` to 10 in controller (currently loads 20, takes 10 in view) | Wasteful data transfer |

### 2.3 schools.blade.php (531 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S5 | **Fix hardcoded delete URL** in JS — use route helper or template variable | `'/system-admin/schools/' + schoolId` breaks with URL prefixes |
| S6 | **Fix XSS in `confirmDelete()`** — `'{{ $school->name }}'` not JS-safe | School name with quotes breaks execution |
| S7 | Add `@error` directives to Create School modal | No per-field validation feedback |

### 2.4 admins.blade.php (536 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S8 | **Fix XSS in `confirmDeleteAdmin()`** — same JS injection risk | User names passed unsafely to JS |
| S9 | Add `@error` directives to Create Admin modal | No per-field validation feedback |
| S10 | Extract ~230 lines of duplicate CSS to shared partial | Duplicated from schools.blade.php |

### 2.5 users.blade.php (697 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S11 | **Add pagination** | Controller uses `->get()`, loads ALL students/instructors |
| S12 | **Move stats computation from `@php` blocks to controller** | MVC violation — computed in both `@section('styles')` and `@section('content')` |
| S13 | **Fix XSS in `confirmDeleteUser()`** | Same JS injection pattern |
| S14 | Add null-safe: `$student->school?->name`, `$instructor->school?->name` | Throws if school deleted |
| S15 | Add ARIA attributes to tabs (`role="tablist"`, `aria-selected`) | No accessibility |

### 2.6 students.blade.php / instructors.blade.php (~55-56 lines each)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S16 | Add null-safe: `$student->school?->name`, `$instructor->school?->name` | Throws if school deleted |
| S17 | Add empty states for no-data scenarios | Tables render empty with no message |
| S18 | Evaluate removing these — redundant with Users page tabs | Duplicate views with less functionality |

### 2.7 courses.blade.php / bookings.blade.php / payments.blade.php (27-48 lines each)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S19 | Add null-safe: `$model->school?->name` across all three | Throws if school deleted |
| S20 | Add empty states for no-data scenarios | No message when tables are empty |
| S21 | Add filter UI (controllers already support filters) | Filter capability exists but is unexposed |
| S22 | Add `->appends(request()->query())` to pagination links | Filter state lost on page change |
| S23 | Make currency symbol (`₱`) configurable | Hardcoded Philippine Peso |

### 2.8 logs.blade.php (615 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S24 | Make category filter options dynamic (from DB or config) | Hardcoded dropdown values |
| S25 | Add log cleanup UI button | Controller has `cleanupLogs()` but no blade interface |

### 2.9 log-detail.blade.php (~330 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S26 | **Convert to use `@extends('layouts.system-admin')`** | Standalone HTML page — loses all navigation |
| S27 | Replace `#667eea`/`#764ba2` gradient with system admin theme (`#053d86`) | Completely different visual style |
| S28 | Add `@error` directive on `resolution_notes` field | No per-field validation |

### 2.10 Layout: system-admin.blade.php (~320 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| S29 | **Add sidebar links for Students, Instructors, Courses, Bookings, Payments** | Routes/views exist but are unreachable from navigation |
| S30 | **Add mobile sidebar toggle (hamburger menu)** | Fixed 250px sidebar unusable on mobile |
| S31 | Add `@stack('scripts')` section | Child views can't inject page-specific JS |

---

## 3. Guest & Authentication Pages

**Files Reviewed:** 10 Blade views + 4 controllers

### 3.1 login.blade.php (732 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G1 | Add `<label>` elements on email and password inputs | Accessibility failure — screen readers can't identify fields |
| G2 | Remove `user-scalable=no` from viewport meta | WCAG violation — blocks zoom |
| G3 | Add password visibility toggle | Users can't see what they're typing |
| G4 | Add loading/disabled state on submit button | Allows double-submits |
| G5 | Move ~70 lines of `@php` header logic to controller or View Composer | MVC violation — computed in Blade |

### 3.2 register.blade.php (920 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G6 | **Extract shared header to `school/partials/auth-header.blade.php`** | ~275 lines duplicated across login/register/verify-email (3 files) |
| G7 | **Fix CSS syntax error at lines ~502-510** | Orphaned CSS properties outside any selector in 480px media query |
| G8 | Make privacy policy and terms content configurable per school | Hard-coded in HTML |
| G9 | Add password visibility toggle and strength indicator | Registration has neither |
| G10 | Remove `user-scalable=no` from viewport meta | WCAG violation |
| G11 | Add ARIA attributes to privacy/terms modals (`role="dialog"`, focus trap) | No accessibility |

### 3.3 verify-email.blade.php (522 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G12 | Add `$schoolNameSize` computation to `@php` block (set from `$settings`) | Undefined variable — referenced but never assigned |
| G13 | Add loading indicator on auto-submit at 6 digits | Form submits without user confirmation |
| G14 | Add resend cooldown timer UI | No feedback that server may rate-limit |
| G15 | Extract test credentials modal to shared partial | Duplicated with guest/dashboard |

### 3.4 guest/dashboard.blade.php (~290 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G16 | **Move 3 database queries to GuestController::dashboard()** | `enrollmentRequests()->whereIn(...)` queries in `@php` block |
| G17 | Add null-safe: `$approvedEnrollment?->course?->title`, `$settings?->primary_color` | Throws if related records deleted |
| G18 | Add responsive breakpoints | Zero `@media` queries — broken on mobile |
| G19 | Make "About" section content dynamic (from school settings) | Hardcoded feature descriptions |
| G20 | Remove `@include('school.admin.partials.admin-styles')` | Admin-specific bloat in guest view |

### 3.5 guest/dashboard-old.blade.php (~200 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G21 | **Delete this file** | Dead code with active MVC violations; replaced by dashboard.blade.php |

### 3.6 guest/courses.blade.php (982 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G22 | **Move enrollment status queries from `<?php ?>` block to controller** | Database queries in Blade view |
| G23 | Move `file_exists(public_path(...))` to controller | Disk I/O in Blade |
| G24 | Replace per-course inline `<script>` functions with event delegation | Each course generates unique JS function, bloats page |
| G25 | Fix `@error` scope — validation errors appear on ALL course modals | Global error directives not scoped to specific course form |
| G26 | Make currency symbol (`₱`) configurable | Hardcoded |

### 3.7 guest/enrollment-requests.blade.php (~200 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G27 | Add null-safe: `$request->course?->title` | Throws if course deleted |
| G28 | Add responsive breakpoints | Zero `@media` queries |
| G29 | Add pagination | All requests loaded at once |

### 3.8 password/forgot.blade.php (~190 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G30 | **Auto-detect account type** instead of requiring user selection | Confusing UX — users don't know their "type" |
| G31 | Add loading state on submit button | No spinner/disabled state |
| G32 | Fix `@extends('layouts.app')` conflict with `body { min-height: 100vh }` CSS | Layout CSS fights parent layout |

### 3.9 password/reset.blade.php (~180 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G33 | Add password visibility toggle | Users can't see password while typing |
| G34 | Add "Back to Login" link | No navigation back (unlike forgot page) |
| G35 | Enforce `StrongPassword` rule (currently only `min:8`) | Inconsistent with registration policy |

### 3.10 welcome.blade.php (~210 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G36 | Replace hardcoded colors (`#053d86`, `#fbbf24`) with config | Hardcoded branding |
| G37 | Replace `© 2025` with `{{ date('Y') }}` | Stale year |
| G38 | Add empty state for when `$schools` is empty | Grid shows nothing with no message |
| G39 | Add image fallbacks for missing logo/hero images | No fallback if files don't exist |

### 3.11 Controllers (AuthController, GuestController, RegistrationController, PasswordResetController)

| # | Improvement | Rationale |
|---|-------------|-----------|
| G40 | **Fix email enumeration vulnerability** in AuthController — return identical errors | Different messages reveal whether email exists |
| G41 | **Resolve dual registration systems** (GuestController vs RegistrationController) | Two controllers with different field schemas |
| G42 | Replace `\Mail::raw()` with Mailable classes | Used in verification and password reset; raw mail has no template |
| G43 | Add `throttle` middleware to login/password reset routes | No Laravel rate-limiting on auth routes |
| G44 | Fix password policy inconsistency (registration=StrongPassword, login=min:6, reset=min:8) | Three different password policies |
| G45 | Fix silent email failure in PasswordResetController | Shows "link sent" even if mail fails |

---

## 4. Layouts, Routes & Middleware

### 4.1 layouts/app.blade.php (2,174 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| L1 | **Extract ~1,100 lines CSS to `resources/css/app.css` via Vite** | Inline CSS is un-cacheable, inflates every page load |
| L2 | **Extract ~700 lines JS to `resources/js/app.js` via Vite** | No browser caching, no minification, no source maps |
| L3 | Fix inline `<script>` execution on AJAX load (fragile, memory leaks, XSS vector) | Creates/removes scripts with 100ms timeout |
| L4 | Replace static notification bell with real notification data (or remove) | Hardcoded `🔔` with empty badge |
| L5 | Compute auth guard checks ONCE and share to views | Same `Auth::guard(...)` checks repeated 6+ times |
| L6 | Move `@stack('styles')` outside `<style>` tag | Currently inside `<style>`, forces pushed content to be raw CSS |
| L7 | Add favicon `<link rel="icon">` | Missing from `<head>` |
| L8 | Remove `console.log` statements from production JS | Debug output in production |
| L9 | Add null-safety on `sscanf` color parsing (lines 32-34) | Fails if color values are invalid/null |

### 4.2 layouts/system-admin.blade.php (~320 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| L10 | See S29-S31 above (missing sidebar links, mobile support, `@stack`) | Covered in System Admin section |

### 4.3 Dead Partials

| # | Improvement | Rationale |
|---|-------------|-----------|
| L11 | **Delete `partials/sidebar.blade.php` and `partials/topbar.blade.php`** | ~750 lines of unused, divergent code (different class names, colors, nav links) |

### 4.4 routes/web.php (~340 lines)

| # | Improvement | Rationale |
|---|-------------|-----------|
| L12 | **Guard ALL test routes with environment check** | `/test/course-form` accessible in production |
| L13 | **Add `throttle` middleware to login POST routes** | No rate limiting on `schools.login.submit` or `system-admin.login.submit` |
| L14 | Remove duplicate `/system-admin/dashboard` route | Both `/` and `/dashboard` map to same method |
| L15 | Consolidate logout routes (4 school + 1 system admin) | Multiple ambiguous logout endpoints |
| L16 | Use route model binding consistently (replace raw `{id}` with typed bindings) | Mix of `{school:slug}` and raw `{id}` parameters |
| L17 | Add role middleware for instructor routes | Instructor group only checks `auth:instructor`, no explicit role check |

### 4.5 Middleware

| # | Improvement | Rationale |
|---|-------------|-----------|
| L18 | **Delete `SystemAdminMiddleware.php`** | Unregistered duplicate of `EnsureSystemAdmin` |
| L19 | **Add school active-status check in `EnsureSchoolContext`** | Deactivated school's portal still accessible |
| L20 | Fix `redirect()->back()` in `EnsureSystemAdmin` for undefined referrer | Regular admin navigating to system-admin gets unpredictable redirect |

---

## 5. Controllers & Models

### 5.1 Controller Issues

| # | Improvement | Rationale |
|---|-------------|-----------|
| C1 | **Add school scoping to BookingController** (`update`, `destroy`, `updateStatus`, `confirmBooking`, `removeFromQueue`) | Cross-tenant manipulation possible — never verifies `$booking->school_id` |
| C2 | **Add school scoping to PaymentController** (`show`, `edit`, `update`, `destroy`) | Same cross-tenant risk |
| C3 | **Add school scoping to ProgressController** (`show`, `edit`, `update`, `destroy`) | Same cross-tenant risk |
| C4 | **Fix `CourseModuleController` — uses `Auth::user()` (default guard)** | Returns `User` model, not admin. `$user->role` doesn't exist → runtime error |
| C5 | **Fix `GuestController::enroll()` — change `'student_id'` to `'learner_id'`** | Doesn't match `EnrollmentRequest->$fillable`, value silently dropped |
| C6 | **Fix `TheoreticalCompletionController::revoke()` type hint** | Expects `Enrollment` but should be `EnrollmentRequest` → fails at runtime |
| C7 | **Remove fake `rand()` rating data** from `ReportController` | `'average_rating' => rand(40, 50) / 10` in production code |
| C8 | Wrap `BookingController::store()` in DB transaction | Multi-step logic (conflict check, load-balancing, create) without atomicity |
| C9 | Replace MySQL-specific raw SQL in `ReportController` and `InstructorController` | `DATE_FORMAT()`, double-quoted `CASE WHEN` — not portable |
| C10 | Fix `InstructorController::myStudents()` — loads ALL students then filters in PHP | Memory/performance issue |
| C11 | Fix `InstructorController::reports()` hardcoded `count * 2` hours | Assumes all sessions are 2 hours |
| C12 | Add pagination to `BookingController::index()`, `PaymentController::index()`, `ProgressController::index()` | All use `->get()` without limits |
| C13 | Extract repetitive auth boilerplate in ExportController (10 methods repeat same check) | Should be middleware or before-filter |
| C14 | Create Form Request classes (only SessionCompletionController uses one) | 17 of 18 controllers use inline validation |
| C15 | Move profile methods out of `InstructorTimeSlotController` into `InstructorController` | Unrelated responsibilities |
| C16 | Remove unused imports (`Report` in ReportController, `ElasticaHandler`/`Else_` in InstructorTimeSlotController) | Dead code |
| C17 | Introduce Laravel Policies for Booking, Payment, Progress, Course | Ad-hoc authorization across all controllers |

### 5.2 Model Issues

| # | Improvement | Rationale |
|---|-------------|-----------|
| M1 | **Add `failed_login_attempts`, `locked_until`, `last_login_at` to `$fillable`** in Admin, Instructor, Student | AuthController updates these but mass assignment protection silently ignores them → account locking never works |
| M2 | **Fix `EnrollmentRequest::sessionCompletions()` relationship** | Returns `Progress::class` records, not `SessionCompletion` instances — breaks progress tracking system-wide |
| M3 | **Fix `SessionCompletion::enrollment()` relationship** | Points to `Enrollment::class` but populated with `enrollment_requests.id` values |
| M4 | Fix `ModuleLesson::course()` — not a proper Eloquent relationship | Returns `$this->module->course()` which can't be eager-loaded and breaks on null |
| M5 | Resolve dual enrollment system (Enrollment vs EnrollmentRequest) | Used interchangeably across controllers; `Student` has both `enrollmentRecords()` and `enrollments()` with confusing naming |
| M6 | Remove or repurpose `Report` model | Imported by ReportController but never used |
| M7 | Remove or repurpose `User` model | Default Laravel model, not used in multi-guard architecture |

---

## 6. Priority Matrix

### 🔴 CRITICAL — Fix Immediately (Security / Data Integrity)

| ID | Item | Area |
|----|------|------|
| C1-C3 | Missing school scoping on BookingController, PaymentController, ProgressController | Controllers |
| M1 | `$fillable` missing security fields — account locking silently fails | Models |
| M2-M3 | Broken enrollment/session relationships | Models |
| C4 | CourseModuleController uses wrong auth guard | Controllers |
| C5 | GuestController::enroll() sets wrong FK name | Controllers |
| A37 | N+1 queries in admin progress.blade.php (raw Eloquent in view) | Admin Views |
| A50 | Full Eloquent query in enrollment-requests/index.blade.php | Admin Views |
| G16 | Database queries in guest/dashboard.blade.php | Guest Views |
| G22 | Database queries in guest/courses.blade.php | Guest Views |
| L12 | Test routes accessible in production | Routes |
| S3 | Hardcoded "Active" status ignoring actual school status | System Admin |
| S26 | log-detail.blade.php is standalone HTML, breaks navigation | System Admin |

### 🟠 HIGH — Fix This Sprint

| ID | Item | Area |
|----|------|------|
| A6-A7 | Duplicate `filterUsers()`/`filterByStatus()` in user-management | Admin Views |
| A8 | XSS in user-management `onclick` handlers | Admin Views |
| S5-S6, S8, S13 | XSS in system admin JS onclick handlers | System Admin |
| G40 | Email enumeration vulnerability in AuthController | Auth |
| G41 | Dual registration systems (GuestController vs RegistrationController) | Auth |
| L1-L2 | Extract CSS/JS from app.blade.php to Vite | Layouts |
| L11 | Delete unused sidebar/topbar partials | Layouts |
| L13 | Add rate limiting to login routes | Routes |
| L18 | Delete unregistered `SystemAdminMiddleware.php` | Middleware |
| L19 | Add school active-status check in `EnsureSchoolContext` | Middleware |
| S29-S30 | Missing sidebar links + mobile support in system admin layout | System Admin |
| G1-G2 | Accessibility: labels on login inputs, remove zoom-block | Guest Views |
| G6 | Extract shared auth header (~550 lines of duplication) | Guest Views |
| G7 | Fix CSS syntax error in register.blade.php | Guest Views |
| C6 | Fix TheoreticalCompletionController::revoke() type hint | Controllers |
| C7 | Remove fake rand() rating data | Controllers |
| C8 | Add DB transaction to BookingController::store() | Controllers |
| M5 | Resolve dual enrollment system ambiguity | Models |

### 🟡 MEDIUM — Next Sprint

| ID | Item | Area |
|----|------|------|
| A9-A10 | Add @error directives and pagination to user-management | Admin Views |
| A14,A19,A24,A38,A51 | Add pagination across admin data views | Admin Views |
| A18,A23 | Move stats computation from views to controllers | Admin Views |
| A27 | Remove 500 lines duplicate CSS in schedules.blade.php | Admin Views |
| A65 | Replace 30 individual queries with GROUP BY in dashboard | Admin Controller |
| S10-S12 | Extract duplicate CSS, add pagination in system admin | System Admin |
| S21-S22 | Expose filter UI, fix pagination appends | System Admin |
| G18,G28 | Add responsive breakpoints to guest views | Guest Views |
| G30 | Auto-detect account type in forgot-password | Guest Views |
| G42-G44 | Mailable classes, throttle middleware, password policy | Auth |
| L5 | Compute guard checks once in middleware | Layouts |
| C9-C11 | Fix MySQL-specific SQL, memory issues, hardcoded hours | Controllers |
| C12-C14 | Pagination, auth middleware extraction, Form Requests | Controllers |

### 🟢 LOW — Backlog

| ID | Item | Area |
|----|------|------|
| A1-A5 | Dashboard CDN fallback, error flash, stat card links | Admin Views |
| A33-A36 | Settings improvements | Admin Views |
| A61-A62 | Implement report stubs | Admin Views |
| S1-S2 | System admin login branding/CAPTCHA | System Admin |
| S23-S25 | Configurable currency, dynamic filter options, log cleanup UI | System Admin |
| G3-G4,G9 | Password visibility toggles, loading states | Guest Views |
| G36-G39 | Welcome page improvements | Guest Views |
| L4,L7-L8 | Notification system, favicon, console.log removal | Layouts |
| L14-L17 | Route cleanup: duplicates, binding consistency | Routes |
| C15-C17 | Controller cleanup: responsibilities, imports, Policies | Controllers |
| M4,M6-M7 | Model cleanup: relationships, unused models | Models |

---

## 7. Cross-Cutting Issues

### 7.1 Null-Safety on Relationship Chains
**Scope:** 30+ files  
Nearly every view chains relationships without null-safe operators (`?->`). Pattern: `$booking->student->name`, `$payment->booking->student->name`, etc. If any related record is soft-deleted or missing, the page crashes.

### 7.2 MVC Violations — Database Queries in Blade
**Scope:** 8 view files  
| File | Violation |
|------|-----------|
| admin/progress.blade.php | Raw `Booking::where()` x2 per row (N+1) |
| admin/enrollment-requests/index.blade.php | Full `EnrollmentRequest::with()` + 5 filters |
| admin/bookings.blade.php | `$bookings->where()->count()` x6 |
| admin/payments.blade.php | `$payments->where()->sum()` |
| admin/courses.blade.php | `file_exists()` x2 |
| guest/dashboard.blade.php | 3 enrollment queries |
| guest/dashboard-old.blade.php | 2 enrollment queries |
| guest/courses.blade.php | Enrollment status query + filtering |

### 7.3 Missing Pagination
**Scope:** 15+ views  
Only 3 admin views (theoretical/index, theoretical/passed) and system admin views have pagination. All other data-heavy views load entire datasets.

### 7.4 Missing `@error` Directives
**Scope:** All forms  
No admin or system admin form uses per-field `@error()` directives. Validation errors appear only as flash messages at page top. Only `system-admin/login.blade.php` properly uses `@error`.

### 7.5 XSS in JavaScript onclick Handlers
**Scope:** 5 files (user-management, schools, admins, users, guests)  
User-supplied data (names, emails) passed directly into JS `onclick` attributes: `onclick="editStudent({{ $id }}, '{{ $name }}')"`. Names containing `'` or `</script>` can inject code. Should use `data-*` attributes with event listeners.

### 7.6 Code Duplication
| Duplicated Code | Files | ~Lines |
|----------------|-------|--------|
| Auth header (PHP + CSS + HTML) | login, register, verify-email | ~275 x 3 |
| Modal CSS/JS | 6+ admin views, 3 system admin views | ~100-200 per file |
| Admin styles partial not included | 5 admin views use own inline CSS | Varies |
| Flash message HTML | Every view | ~10-20 per file |
| Guard check logic | app.blade.php (6+ locations) | ~30 lines x 6 |

### 7.7 Monolithic Layout
**app.blade.php** is 2,174 lines with ALL CSS and JS inline. Vite build system exists but is not connected. No browser caching, no minification, no source maps.

### 7.8 Dual Enrollment System
Both `Enrollment` and `EnrollmentRequest` models are used interchangeably across controllers. `SessionCompletion::enrollment()` points to `Enrollment::class` but stores `enrollment_requests.id`. `EnrollmentRequest::sessionCompletions()` returns `Progress` records, not `SessionCompletion` instances.

### 7.9 Accessibility
No ARIA attributes on any interactive elements across the entire system. No `role="dialog"` on modals, no `aria-labels` on forms, no keyboard navigation, no focus management. `user-scalable=no` blocks zoom on auth pages.

---

**Total Improvements Identified:** 129 items  
- School Admin: 69 items (A1–A69)  
- System Admin: 31 items (S1–S31)  
- Guest & Auth: 45 items (G1–G45)  
- Layouts/Routes/Middleware: 20 items (L1–L20)  
- Controllers: 17 items (C1–C17)  
- Models: 7 items (M1–M7)
