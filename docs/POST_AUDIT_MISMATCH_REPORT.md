# Post-Audit Mismatch Report — System-Wide Review

**Date:** March 7, 2026  
**Scope:** All 5 roles — Guest, Student, Instructor, Admin/Branch Secretary, School Admin, System Admin  
**Purpose:** Identify mismatches between controllers, database, frontend, and forms introduced by the Full System Audit (March 3, 2026)  
**Total Issues Found: 22**

---

## EXECUTIVE SUMMARY

| Severity     | Count | Breakdown |
|--------------|-------|-----------|
| **Critical** | 10    | Functionality fully broken or data corruption |
| **High**     | 6     | Security gaps or crash potential |
| **Medium**   | 6     | Consistency gaps or minor functional issues |

### Issues by Role

| Role | Critical | High | Medium |
|------|----------|------|--------|
| Guest | 2 | 2 | 2 |
| Student | 0 | 1 | 0 |
| Instructor | 2 | 1 | 2 |
| Admin / Branch Secretary | 2 | 1 | 0 |
| System Admin | 2 | 0 | 1 |
| Cross-cutting | 2 | 1 | 1 |

---

## CRITICAL ISSUES (10)

### C-1. StoreSessionCompletionRequest — Authorization Always Denies

| Detail | Value |
|--------|-------|
| **File** | `app/Http/Requests/StoreSessionCompletionRequest.php` (line 17) |
| **Problem** | `authorize()` checks `$user->role === 'instructor'`, but the Instructor model has **no `role` field** |
| **Impact** | Session logging is completely broken for instructors — every request returns 403 Forbidden |
| **Affected Role** | Instructor |
| **Root Cause** | Audit fixed the old broken check but used a non-existent attribute instead of an Auth guard check |
| **Fix** | Change `authorize()` to `return Auth::guard('instructor')->check();` |

---

### C-2. SessionCompletion::loggedBy() — Wrong Model Class + FK Mismatch

| Detail | Value |
|--------|-------|
| **Files** | `app/Models/SessionCompletion.php` (line 77), `app/Http/Controllers/SessionCompletionController.php` (line 203), `database/migrations/2026_03_03_145040_fix_foreign_key_constraints_point_to_admins_and_cascades.php` (line 25) |
| **Problem** | Model relationship points to `Admin::class`, controller stores `$instructor->id` in `logged_by`, FK constraint points to `admins` table — but the actual values are **instructor IDs** |
| **Impact** | FK violation on insert (instructor ID doesn't exist in admins table); relationship returns wrong/null data |
| **Affected Role** | Instructor, Admin |
| **Root Cause** | Audit changed `User::class` to `Admin::class` but sessions are logged by **instructors**, not admins |
| **Fix** | Change relationship to `Instructor::class`; change FK constraint to reference `instructors` table; use `nullOnDelete()` instead of `cascadeOnDelete()` |

---

### C-3. Admin Model — `role` and `must_reset_password` Missing from $fillable

| Detail | Value |
|--------|-------|
| **File** | `app/Models/Admin.php` (lines 21–34) |
| **Problem** | `role` and `must_reset_password` are NOT in `$fillable`, but `AdminManagementController` (lines 92–93) and `SystemAdminController` (line 246) mass-assign them |
| **Impact** | Admin/secretary creation silently fails — role is never saved, account is created without a role and is unusable |
| **Affected Role** | School Admin, System Admin |
| **Root Cause** | Audit removed `role` from `$fillable` to prevent privilege escalation, but this also blocks legitimate admin creation |
| **Fix** | Add `'role'` and `'must_reset_password'` back to `$fillable` in Admin model; protect against escalation via controller-level checks instead |

---

### C-4. Experience Level Enum Mismatch — Form vs Database [RESOLVED]

| Detail | Value |
|--------|-------|
| **Files** | `app/Http/Requests/StoreEnrollmentRequestRequest.php` (line 31), `resources/views/school/guest/courses.blade.php` (lines 905, 910), `database/migrations/2025_01_01_000006_create_enrollment_tables.php` (line 17), `database/migrations/2025_01_01_000002_create_auth_tables.php` (line 50) |
| **Problem** | Form and validation use `'experienced_driver'` but DB enum only allows `['new_driver', 'experienced']` |
| **Impact** | Selecting "Experienced Driver" passes validation but causes a database constraint violation on insert |
| **Affected Role** | Guest |
| **Root Cause** | Form/validation was never aligned with the database enum values |
| **Fix** | Standardized all references to use `'experienced'` to match the database enum and form values. [FIXED March 18, 2026] |

---

### C-5. School Model — Missing `status` Column

| Detail | Value |
|--------|-------|
| **Files** | `app/Http/Controllers/SystemAdminController.php` (lines 235, 289–290), `database/migrations/2025_01_01_000001_create_schools_table.php`, `app/Models/School.php` (lines 12–19) |
| **Problem** | `SystemAdminController` sets `'status' => 'active'` when creating schools and reads/updates `$school->status` in `toggleSchoolStatus()` — but no `status` column exists in the schools table migration, and `status` is not in School's `$fillable` |
| **Impact** | School creation ignores status; toggle-status feature is non-functional |
| **Affected Role** | System Admin |
| **Root Cause** | Controller references a column that was never added to the database |
| **Fix** | Add `status` column to schools table migration + add to School `$fillable`, OR use the existing `settings` JSON column |

---

### C-6. `enrollments.theoretical_passed_by` — FK References Non-Existent `users` Table

| Detail | Value |
|--------|-------|
| **Files** | `database/migrations/2025_01_01_000006_create_enrollment_tables.php` (line 53), `database/migrations/2026_03_03_145040_fix_foreign_key_constraints_point_to_admins_and_cascades.php` (lines 17–20) |
| **Problem** | Original migration constrains `theoretical_passed_by` to `users` table (doesn't exist). Fix migration changes it to `admins`, but on fresh DB, the original `->constrained('users')` will fail first |
| **Impact** | Fresh `php artisan migrate` will fail at the FK creation step |
| **Affected Role** | All (migration integrity) |
| **Root Cause** | Audit created a fix migration but didn't update the original migration |
| **Fix** | Update the original migration to reference `admins` instead of `users`, or ensure the fix migration handles the case where the original FK doesn't exist |

---

### C-7. `session_completions.logged_by` — FK References `users` Table (Same Issue)

| Detail | Value |
|--------|-------|
| **Files** | `database/migrations/2025_01_01_000006_create_enrollment_tables.php` (line 75), `database/migrations/2026_03_03_145040_fix_foreign_key_constraints_point_to_admins_and_cascades.php` (lines 24–27) |
| **Problem** | Same issue as C-6 — original FK references `users`, fix migration changes to `admins`. Fresh migrations break |
| **Impact** | Fresh `php artisan migrate` fails |
| **Affected Role** | All (migration integrity) |
| **Fix** | Same as C-6 |

---

### C-8. `session_completions.enrollment_id` — FK to Wrong Table

| Detail | Value |
|--------|-------|
| **Files** | `database/migrations/2025_01_01_000006_create_enrollment_tables.php` (line 67), `app/Models/SessionCompletion.php` (line 55) |
| **Problem** | Migration constrains `enrollment_id` to `enrollments` table, but model references `EnrollmentRequest::class` (table `enrollment_requests`). Fix migration changes FK to `enrollment_requests`, but fresh migration creates original FK first |
| **Impact** | Data integrity mismatch between what the model expects and what the DB enforces on fresh install |
| **Affected Role** | Instructor, Admin |
| **Fix** | Update original migration to reference `enrollment_requests` |

---

### C-9. EnrollmentRequest `rejected_at` — In Model `$fillable` But No DB Column

| Detail | Value |
|--------|-------|
| **Files** | `app/Models/EnrollmentRequest.php` (line 36) |
| **Problem** | `'rejected_at'` is listed in `$fillable` but no migration creates this column in the `enrollment_requests` table |
| **Impact** | Any code setting `rejected_at` silently fails — the timestamp is never persisted |
| **Affected Role** | Admin |
| **Fix** | Either add a migration to create the column, or remove it from `$fillable` |

---

### C-10. Enrollment View — `$request->notes` References Non-Existent Field

| Detail | Value |
|--------|-------|
| **Files** | `resources/views/school/guest/enrollment-requests.blade.php` (lines 575, 577–578), `app/Http/Controllers/GuestController.php` (line 238) |
| **Problem** | View checks `@if($request->notes)` and displays `$request->notes`, but the EnrollmentRequest model only has `remarks` (not `notes`). Controller stores form's `notes` input as `remarks` |
| **Impact** | "Your Notes" section never displays for guests viewing their enrollment requests |
| **Affected Role** | Guest |
| **Fix** | Change view from `$request->notes` to `$request->remarks` |

---

## HIGH SEVERITY ISSUES (6)

### H-1. Student Model — `role` Still in `$fillable` (Audit Said [FIXED])

| Detail | Value |
|--------|-------|
| **File** | `app/Models/Student.php` (line 42) |
| **Problem** | `'role'` is still in `$fillable` despite the audit report marking this as **[FIXED]** |
| **Impact** | Privilege escalation vulnerability — any mass-assignment path can change a guest to student role |
| **Affected Role** | Guest, Student |
| **Note** | `'role'` may need to remain in `$fillable` because `EnrollmentRequest::approve()` calls `$this->learner->update(['role' => 'student'])`. If so, protect at the controller/form-request level instead |

---

### H-2. Information Leaks in EnrollmentRequestController

| Detail | Value |
|--------|-------|
| **File** | `app/Http/Controllers/EnrollmentRequestController.php` (lines 206, 607, 684) |
| **Problem** | Exception messages exposed to users: `$e->getMessage()` appended to user-facing error messages |
| **Impact** | Internal error details (DB queries, stack info) visible to admins |
| **Affected Role** | Admin |
| **Fix** | Replace with generic error messages; log the exception details server-side |

---

### H-3. TimeSlot `$casts` — `datetime:H:i` on TIME Columns

| Detail | Value |
|--------|-------|
| **Files** | `app/Models/TimeSlot.php` (lines 28–29), scheduling migration |
| **Problem** | Casts `start_time` and `end_time` as `'datetime:H:i'` but DB columns are `time()` type. Carbon sets date to "today" for time-only values |
| **Impact** | Cross-day comparisons may break; schedule data may serialize incorrectly |
| **Affected Role** | All roles viewing schedules |
| **Fix** | Remove the datetime cast or use a custom accessor that handles time-only values |

---

### H-4. Theoretical Completion — Dual Field Inconsistency

| Detail | Value |
|--------|-------|
| **Files** | `app/Http/Controllers/TheoreticalCompletionController.php`, `app/Support/EnrollmentValidator.php`, `app/Models/EnrollmentRequest.php` |
| **Problem** | Two different tables track "theoretical passed": `Student.has_passed_theoretical` and `EnrollmentRequest.theoretical_passed`. TheoreticalCompletionController updates the Student field, but EnrollmentValidator checks the EnrollmentRequest field |
| **Impact** | Validation may allow re-passing or block already-passed students depending on which field is checked |
| **Affected Role** | Instructor, Admin |
| **Fix** | Standardize on one source of truth and update both the controller and validator to use it |

---

### H-5. EnsureGuestRole Middleware — Missing School Ownership Check

| Detail | Value |
|--------|-------|
| **File** | `app/Http/Middleware/EnsureGuestRole.php` |
| **Problem** | Only checks `$student->role !== 'guest'` — doesn't verify `$student->school_id` matches the route's school |
| **Impact** | A guest from School A can access School B's guest dashboard (data is scoped in controllers, but authorization gap exists at middleware level) |
| **Affected Role** | Guest |
| **Fix** | Add `$student->school_id !== $request->route('school')->id` check |

---

### H-6. Student Profile View — Unsafe Null Relationship Access

| Detail | Value |
|--------|-------|
| **File** | `resources/views/school/student/profile.blade.php` (line 685) |
| **Code** | `{{ $student->branchRelation->name ?? 'Not Assigned' }}` |
| **Problem** | PHP's `??` does NOT protect against null intermediate objects. If `branchRelation` is null, `->name` throws a fatal error before `??` is evaluated |
| **Impact** | 500 error on student profile page when no branch is assigned |
| **Affected Role** | Student |
| **Fix** | Use nullsafe operator: `{{ $student->branchRelation?->name ?? 'Not Assigned' }}` |

---

## MEDIUM SEVERITY ISSUES (6)

### M-1. Route `toggleUserStatus` — Type Parameter Mismatch

| Detail | Value |
|--------|-------|
| **Files** | `routes/web.php` (line 80), `app/Http/Controllers/SystemAdminController.php` |
| **Problem** | Route accepts `['student', 'instructor', 'admin']` via `whereIn`, but controller only handles `student` and `instructor` |
| **Impact** | Passing `admin` type matches the route but controller returns an error response |
| **Affected Role** | System Admin |
| **Fix** | Remove `'admin'` from the route's `whereIn`, or add controller handling for it |

---

### M-2. InstructorController::myStudents() — Undefined Variable

| Detail | Value |
|--------|-------|
| **File** | `app/Http/Controllers/InstructorController.php` (lines 104–106) |
| **Problem** | Closure uses `$assignedStudentIds` which is never defined in the method |
| **Impact** | PHP undefined variable warning; `is_assigned` property is unreliable |
| **Affected Role** | Instructor |
| **Fix** | Define the variable before use or remove the reference |

---

### M-3. Session Edit Form — PUT vs PATCH Mismatch

| Detail | Value |
|--------|-------|
| **Files** | Route definition uses `Route::put()`, view form uses `@method('PATCH')` |
| **Problem** | HTTP method mismatch between route and form |
| **Impact** | Works in Laravel (PUT/PATCH are handled identically) but violates REST conventions |
| **Affected Role** | Instructor |
| **Fix** | Use `PATCH` consistently for partial updates |

---

### M-4. Guest Registration — `location` Field Validated But Not in Form

| Detail | Value |
|--------|-------|
| **File** | `app/Http/Controllers/GuestController.php` (line 46) |
| **Problem** | Validates `'location'` as nullable but registration form has no location input |
| **Impact** | Location is always null during registration (can be set during enrollment later) |
| **Affected Role** | Guest |
| **Fix** | Either add the field to the registration form or remove validation |

---

### M-5. Form Field Naming — `notes` Input Stored as `remarks`

| Detail | Value |
|--------|-------|
| **Files** | `resources/views/school/guest/courses.blade.php` (line 950), `app/Http/Controllers/GuestController.php` (line 238) |
| **Problem** | Form field is named `notes` but stored as `remarks` in the database |
| **Impact** | Functional but creates naming confusion across the codebase |
| **Affected Role** | Guest |
| **Fix** | Align naming — either rename the form field to `remarks` or the DB column to `notes` |

---

### M-6. SessionCompletion `session_time` Cast — Same datetime-on-TIME Issue

| Detail | Value |
|--------|-------|
| **File** | `app/Models/SessionCompletion.php` (line 33) |
| **Problem** | `'session_time' => 'datetime:H:i'` applied to a `time()` column — same issue as H-3 |
| **Impact** | Cast inconsistency for time-only data |
| **Affected Role** | Instructor, Admin |
| **Fix** | Same approach as H-3 |

---

## RECOMMENDED FIX ORDER

| Priority | Issue | Impact if Unfixed |
|----------|-------|-------------------|
| 1 | **C-1** StoreSessionCompletionRequest `authorize()` | Session logging completely broken |
| 2 | **C-3** Admin `$fillable` missing `role` / `must_reset_password` | Admin creation completely broken |
| 3 | **C-2** SessionCompletion `loggedBy()` wrong class + FK | FK violation on every session save |
| 4 | **C-4** Experience level enum mismatch | Guest enrollment broken for experienced drivers |
| 5 | **C-5** School `status` column missing | School toggle feature non-functional |
| 6 | **H-1** Student `role` still in `$fillable` | Privilege escalation possible |
| 7 | **C-6/C-7/C-8** Migration FK references to `users` table | Fresh migrations fail |
| 8 | **C-9** `rejected_at` missing column | Silent data loss |
| 9 | **C-10** View references `notes` instead of `remarks` | Guest enrollment notes never display |
| 10 | **H-6** Student profile nullsafe operator | 500 error for branchless students |
| 11 | **H-3/M-6** TimeSlot/SessionCompletion datetime casts | Schedule data integrity |
| 12 | **H-4** Theoretical dual field inconsistency | Validation logic mismatch |
| 13 | **H-2** Information leaks in error messages | Security exposure |
| 14 | **H-5** Guest middleware school check | Cross-school access |
| 15 | **M-1 through M-5** Medium issues | UX/consistency |
