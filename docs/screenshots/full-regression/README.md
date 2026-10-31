# Regression verification and screenshots

Verified locally on 2026-10-31 at http://localhost:8004. This does not verify or deploy driveedhub.com.

All 14 run steps passed, including fixture preparation and cleanup.

## Coverage

- Home: DriveED Hub title and copyright.
- Course/package hours: saved values after reload, fractional totals, create/update, zero hours, negative-hour validation.
- Login: 320px, 390px, 768px, 1440px, and 667px landscape; correct viewport, no horizontal overflow, readable mobile input text.
- Empty new school: 22 GET routes via direct and AJAX requests, six creation dialogs, 16 export/statistics endpoints.
- Payments: admin navigation retained; one visible empty state on desktop and mobile.
- User Management: new and existing schools, five inactive users in one school, archived/deactivated role filters and search, separate-school card/list isolation.
- Existing backend regressions: 65 assertions (21 bug-report + 27 edge-case + 17 October 31 findings).
- Browser exception checks and HTTP 5xx checks passed where instrumented; all page/API/export response assertions passed.
- Temporary schools/users removed; backend regression data rolled back; temporary course removed.

No errors were detected in these tested flows. This is not a guarantee that untested workflows, data combinations, devices, or the deployed site are free of bugs.

## Run results

| Step | Result | Log |
|---|---|---|
| home-branding | PASS | [Log](home-branding.log) |
| hours-and-responsive-login | PASS | [Log](hours-and-responsive-login.log) |
| prepare-new-school | PASS | [Log](prepare-new-school.log) |
| new-school-pages-dialogs-exports | PASS | [Log](new-school-pages-dialogs-exports.log) |
| new-and-existing-user-management | PASS | [Log](new-and-existing-user-management.log) |
| prepare-inactive-users | PASS | [Log](prepare-inactive-users.log) |
| five-inactive-users | PASS | [Log](five-inactive-users.log) |
| split-school-counts | PASS | [Log](split-school-counts.log) |
| school-isolation | PASS | [Log](school-isolation.log) |
| existing-bug-report | PASS | [Log](existing-bug-report.log) |
| existing-edge-cases | PASS | [Log](existing-edge-cases.log) |
| existing-oct31-findings | PASS | [Log](existing-oct31-findings.log) |
| cleanup-new-school | PASS | [Log](cleanup-new-school.log) |
| cleanup-inactive-users | PASS | [Log](cleanup-inactive-users.log) |

## Screenshots

### home-branding

- [driveed-hub-home.png](../home-branding/driveed-hub-home.png)

### hours-login-fix

- [course-hours.png](../hours-login-fix/course-hours.png)
- [login-1440.png](../hours-login-fix/login-1440.png)
- [login-320.png](../hours-login-fix/login-320.png)
- [login-390.png](../hours-login-fix/login-390.png)
- [login-667.png](../hours-login-fix/login-667.png)
- [login-768.png](../hours-login-fix/login-768.png)
- [package-created.png](../hours-login-fix/package-created.png)
- [package-updated.png](../hours-login-fix/package-updated.png)

### new-school-audit

- [admin-management.index-create-dialog.png](../new-school-audit/admin-management.index-create-dialog.png)
- [admin-management.index.png](../new-school-audit/admin-management.index.png)
- [branches.index-create-dialog.png](../new-school-audit/branches.index-create-dialog.png)
- [branches.index.png](../new-school-audit/branches.index.png)
- [courses-create-dialog.png](../new-school-audit/courses-create-dialog.png)
- [courses.png](../new-school-audit/courses.png)
- [dashboard.png](../new-school-audit/dashboard.png)
- [enrollments.index.png](../new-school-audit/enrollments.index.png)
- [login.png](../new-school-audit/login.png)
- [materials.index.png](../new-school-audit/materials.index.png)
- [payments-mobile.png](../new-school-audit/payments-mobile.png)
- [payments.index.png](../new-school-audit/payments.index.png)
- [payments.statistics.png](../new-school-audit/payments.statistics.png)
- [profile.png](../new-school-audit/profile.png)
- [removalRequests.png](../new-school-audit/removalRequests.png)
- [reports.index.png](../new-school-audit/reports.index.png)
- [reports.instructors.png](../new-school-audit/reports.instructors.png)
- [reports.logs.png](../new-school-audit/reports.logs.png)
- [reports.students.png](../new-school-audit/reports.students.png)
- [schedules-create-dialog.png](../new-school-audit/schedules-create-dialog.png)
- [schedules.png](../new-school-audit/schedules.png)
- [sessions.index.png](../new-school-audit/sessions.index.png)
- [settings.png](../new-school-audit/settings.png)
- [theoretical.index.png](../new-school-audit/theoretical.index.png)
- [userManagement-create-dialog.png](../new-school-audit/userManagement-create-dialog.png)
- [userManagement.png](../new-school-audit/userManagement.png)
- [vehicles.index-create-dialog.png](../new-school-audit/vehicles.index-create-dialog.png)
- [vehicles.index.png](../new-school-audit/vehicles.index.png)
- [verify-session-completion.index.png](../new-school-audit/verify-session-completion.index.png)

### new-school-user-management

- [create-course-link-works.png](../new-school-user-management/create-course-link-works.png)
- [no-courses-student-modal.png](../new-school-user-management/no-courses-student-modal.png)
- [user-management-loaded.png](../new-school-user-management/user-management-loaded.png)

### inactive-roster-fix

- [active-users.png](../inactive-roster-fix/active-users.png)
- [all-users.png](../inactive-roster-fix/all-users.png)
- [deactivated-instructor.png](../inactive-roster-fix/deactivated-instructor.png)
- [inactive-card-five-users.png](../inactive-roster-fix/inactive-card-five-users.png)
- [instructors.png](../inactive-roster-fix/instructors.png)
- [qa-inactive-roster-other-school-scoped.png](../inactive-roster-fix/qa-inactive-roster-other-school-scoped.png)
- [qa-inactive-roster-school-scoped.png](../inactive-roster-fix/qa-inactive-roster-school-scoped.png)
- [students.png](../inactive-roster-fix/students.png)

49 screenshots available.
