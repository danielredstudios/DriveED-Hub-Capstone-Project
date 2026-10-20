# Strict Code Findings (Codebase Review)

Date: April 1, 2026
Scope: Source-code review of current codebase behavior. This document lists findings only (no proposed fixes).

## F-001 - Cross-school course ID can be accepted when creating schedules

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/AdminController.php

Problem:
- In createSchedule, course_id is validated with exists:courses,id and not constrained by school_id.
- A crafted request can pass a course ID that belongs to another school while creating a schedule inside the current school context.

Role used to find problem:
- School Admin

What happens if this error occurs:
- A schedule in one school can point to a course owned by another school.
- Admins and students can see broken or mismatched schedule/course data.
- Tenant isolation can be violated, especially in reporting and exports.

How to encounter manually:
1. Create two schools (School A and School B) and create at least one course in each school.
2. Login as School A admin and open the schedule creation page.
3. Use browser DevTools request replay (Network tab, Edit and Resend) to change the course ID to School B's course ID.
4. Submit and observe that the request can pass validation and proceed with a cross-school course linkage.

## F-002 - Cross-school branch assignment risk in account creation and updates

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/AdminController.php

Problem:
- branch_id in several admin user flows is checked as exists:branches,id without school_id scoping.
- This allows foreign branch IDs to pass request validation when IDs are known.

Role used to find problem:
- School Admin

What happens if this error occurs:
- Users can be assigned to branches that do not belong to their school.
- Branch-scoped permissions and reports become unreliable.
- Downstream pages can show missing/incorrect branch data or access anomalies.

How to encounter manually:
1. Create branches in two schools.
2. Login to School A admin and open user invite or user update forms.
3. Change the submitted branch_id to a School B branch ID using browser request editing.
4. Submit and observe the field-level branch existence check can still pass.

## F-003 - Payment linkage to booking/enrollment is not school-scoped in submission flow

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/PaymentController.php

Problem:
- Payment store validates booking_id/enrollment_request_id using generic exists checks.
- It then resolves the model by raw ID and derives branch_id, without first enforcing same-school ownership for that linked record.

Role used to find problem:
- Student

What happens if this error occurs:
- A payment from School A can become linked to records from School B.
- Payment ledgers, branch totals, and audit trails can be corrupted.
- Users may see payment records under the wrong operational context.

How to encounter manually:
1. Login as a School A student and open a payment submission form.
2. In another session, note a booking or enrollment ID from School B.
3. Use browser DevTools request editing to replace the linked ID before submitting.
4. Observe that the request can proceed using a foreign-school linkage.

## F-004 - Payment status vocabulary is inconsistent across modules

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/EnrollmentRequestController.php
- app/Http/Controllers/PaymentController.php
- app/Services/FinancialService.php
- app/Http/Controllers/SystemAdminController.php

Problem:
- Unified approval marks related payments as completed.
- Payment dashboards and financial summaries aggregate approved.
- System admin payment summary uses paid.
- Same payment lifecycle is interpreted with three different status labels.

Role used to find problem:
- Guest/Student (payment submission)
- School Admin (enrollment and report verification)
- System Admin (global payment totals comparison)

What happens if this error occurs:
- Financial totals differ between pages for the same date range.
- Revenue and pending metrics are undercounted or overcounted depending on screen.
- Management decisions can be made using inconsistent numbers.

How to encounter manually:
1. Submit a payment as guest/student and complete admin approval using the unified approval path.
2. Open school payment statistics and record values.
3. Open report analytics financial values for the same period.
4. Open system-admin payment totals for the same period.
5. Compare results and observe mismatches caused by status label differences.

## F-005 - Booking status naming is inconsistent (no-show vs no_show)

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/BookingController.php
- app/Http/Controllers/ReportController.php
- app/Models/Booking.php

Problem:
- Different parts of the system refer to no-show with different tokens: no-show and no_show.
- This causes fragmented filtering and aggregation behavior.

Role used to find problem:
- Instructor or School Admin (booking status operations)
- School Admin (analytics/report verification)

What happens if this error occurs:
- No-show counts can differ between dashboard cards and detailed reports.
- Historical data from older flows can disappear from some widgets.
- Attendance and cancellation KPIs become inconsistent.

How to encounter manually:
1. Mark bookings as no-show through normal admin/instructor workflow.
2. Use an environment that contains legacy or migrated records where no_show exists.
3. Open booking statistics and report analytics pages.
4. Compare no-show-related counts and observe inconsistent totals.

## F-006 - Password rule mismatch can create valid updates that cannot log in

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/AuthController.php
- app/Http/Controllers/AdminController.php

Problem:
- Login requires password length minimum 8.
- Some admin update flows allow password minimum 6 for student/instructor updates.
- A password accepted by update flow can still be rejected by login validation.

Role used to find problem:
- School Admin (password update)
- Student/Instructor (login verification)

What happens if this error occurs:
- Admin sees successful save, but target user cannot log in.
- Users can be unintentionally locked out after profile maintenance.
- Support overhead increases due to password reset requests.

How to encounter manually:
1. As admin, edit a student or instructor and set a 6-7 character password in the UI.
2. Save changes and sign out.
3. Attempt login as that user with the same password.
4. Observe login is rejected before credentials are fully evaluated.

## F-007 - Booking creation IDs are validated for existence but not tenant ownership

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/BookingController.php

Problem:
- Booking store uses generic exists checks for student_id, course_id, instructor_id, time_slot_id.
- The request can include IDs from outside the current school unless separately blocked later.

Role used to find problem:
- Student

What happens if this error occurs:
- Bookings can reference foreign-school entities.
- Schedule boards and student/instructor assignment screens can show invalid joins.
- Integrity issues can cascade into payments and reports.

How to encounter manually:
1. Login as a School A student and open the schedule booking flow.
2. Intercept the booking submit request in browser DevTools.
3. Replace one or more IDs (course, timeslot, instructor) with IDs from School B.
4. Submit and observe that basic existence validation can pass even with cross-school IDs.

## F-008 - Payment delete redirect uses route name that is not in school-scoped route set

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/PaymentController.php

Problem:
- In non-AJAX delete flow, redirect calls payments.index instead of school-prefixed route names.
- Current route definitions are namespaced under schools.admin.*, schools.student.*, or schools.guest.*.

Role used to find problem:
- School Admin or Branch Secretary

What happens if this error occurs:
- Payment may be deleted but user lands on an error page instead of list view.
- Users interpret this as a failed operation even when data changed.
- Admin confidence and workflow continuity are affected.

How to encounter manually:
1. Open payment list in a full-page flow (not modal/AJAX-only behavior).
2. Delete a payment and let the request complete normally.
3. Observe post-delete navigation can fail due to unresolved route name.

## F-009 - Enrollment eligibility messaging conflicts with actual enrollment enforcement

Status: RESOLVED (V10.2 Hardening)
- app/Support/EnrollmentValidator.php
- app/Http/Controllers/GuestController.php
- app/Http/Controllers/StudentController.php

Problem:
- EnrollmentValidator can return permissive practical-course messaging for experienced users in some checks.
- Actual enrollment controllers hard-block practical enrollment without verified license.
- UI guidance and backend enforcement can contradict each other.

Role used to find problem:
- Guest or Student

What happens if this error occurs:
- Users receive mixed guidance and failed submissions.
- Enrollment conversion drops due to confusion.
- Admin/support teams receive preventable clarification requests.

How to encounter manually:
1. Use an account that passed theoretical requirements but has unverified license.
2. Open a practical course details page and review displayed eligibility guidance.
3. Attempt enrollment using the normal enroll button.
4. Observe submission is blocked despite permissive guidance seen earlier.

## F-010 - Financial period calculations use mixed timestamp axes

Status: RESOLVED (V10.2 Hardening)
- app/Http/Controllers/ReportController.php
- app/Services/FinancialService.php

Problem:
- Some financial calculations filter by received_at while other paths use paid_on or creation-based periods.
- Period totals can differ for the same dataset because not all modules use the same event timestamp.

Role used to find problem:
- School Admin
- System Admin (cross-page total validation)

What happens if this error occurs:
- Month/week/day totals can shift between pages for identical data.
- Revenue trend charts can show false peaks or dips.
- Reconciliation against accounting records becomes difficult.

How to encounter manually:
1. Use real workflows where payment is submitted on one date and verified/received on another date.
2. Open monthly (or weekly) financial reports in school admin pages.
3. Compare those values against other financial widgets and payment list views for the same range.
4. Observe totals differ because different timestamp columns are used in different modules.
