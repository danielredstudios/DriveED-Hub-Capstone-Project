# System-Wide Regression Test Coverage

This document summarizes what the new regression suite verifies.

## Test file
- `tests/Feature/SystemWideRegressionTest.php`

## What each test verifies

1. **Public school routes are accessible and unknown routes return 404**
   - Confirms school login, registration, and forgot-password pages return HTTP 200.
   - Confirms invalid school sub-route returns HTTP 404.

2. **Student and instructor core pages are accessible to correct roles**
   - Student can access student progress page (200).
   - Instructor can access instructor sessions index (200).

3. **Admin and branch secretary can access admin dashboard**
   - School admin dashboard access works (200).
   - Branch secretary dashboard access works (200).

4. **Guest role can access guest dashboard while student role is redirected from guest dashboard**
   - Guest-role student can access guest dashboard (200).
   - Full student is redirected away from guest-only page (302).

5. **Student is blocked from admin pages by auth middleware**
   - Ensures student cannot access admin route and is redirected (302).

6. **Branch secretary is forbidden from school-admin-only management page**
   - Ensures `school.admin.only` enforcement on admin-management endpoint (403 JSON).

7. **School admin can create, update, and delete a module safely**
   - Exercises LMS module create/update/delete flow.
   - Verifies DB state changes within test transaction scope only.

8. **School admin can create, update, and delete a lesson safely**
   - Exercises LMS lesson create/update/delete flow.
   - Verifies DB state changes within test transaction scope only.

9. **Module route returns 404 for module-course mismatch**
   - Confirms scoped binding/mismatch handling returns 404.

10. **Lesson route returns 404 for lesson-module mismatch**
   - Confirms nested LMS route mismatch handling returns 404.

## Safety guarantees
- Uses Laravel testing DB isolation via `RefreshDatabase`.
- No migrations or seeders are modified.
- Writes/deletes happen only in isolated test transactions and are rolled back/reset after test execution.

## Screenshot routing setup
- Screenshot base path is centralized in `tests/DuskTestCase.php` to `tests/screenshots`.
- Role folders are enforced/created:
  - `tests/screenshots/admin/`
  - `tests/screenshots/instructor/`
  - `tests/screenshots/student/`
  - `tests/screenshots/guest/`
  - `tests/screenshots/branch/`
- Browser tests now use centralized role-based screenshot naming through `captureRoleScreenshot(...)`.
