# DriveED Hub

A school portal for managing students, instructors, courses, schedules, payments, and training records.

[Latest fixes](#latest-fixes-october-31-2026) · [October 30](#fixes-october-30-2026) · [October 29](#fixes-october-29-2026) · [October 27](#fixes-october-27-2026) · [Earlier fixes](#earlier-fixes-october-20-to-26-2026)

## Latest Fixes October 31 2026

- Students who pass TDC can apply for PDC without cancelling their finished TDC course.
- Previous training records and Combo courses are kept unchanged.
- Finishing an older course does not interrupt a student's newer course.
- Passing notes are saved and kept when the passing action is repeated.
- Extra sessions cannot be added once the required training hours are complete.
- Grades and feedback can still be corrected without adding duplicate sessions.
- Saving two sessions at the same time cannot exceed the student's remaining training hours.
- Admins, instructors, and students can have their own header colors.
- Review windows close properly, including when using the Escape key.

## School Admin and Home Page Fixes

- Course and package hours save correctly, including fractional totals. Required course hours can be edited in the course form.
- Login adapts to phone, tablet, desktop, and landscape screens with readable mobile inputs.
- New schools can open User Management before creating courses. Empty-course links use the correct school route.
- Payments retains the admin layout on direct loads and shows one empty state per screen size.
- Archived and deactivated users appear in the inactive list, with statistics matching the list. Each school sees only its own users.
- The home page title and copyright use DriveED Hub.

| Fix | Screenshot evidence |
| --- | --- |
| Saved course hours | [Course form](docs/screenshots/hours-login-fix/course-hours.png) |
| Saved package hours | [Package form](docs/screenshots/hours-login-fix/package-created.png) |
| Mobile login | [390px phone](docs/screenshots/hours-login-fix/login-390.png) |
| New-school User Management | [Empty-school page](docs/screenshots/new-school-user-management/user-management-loaded.png) |
| Payments empty state | [Payments page](docs/screenshots/new-school-audit/payments.index.png) |
| Five inactive users in one school | [Matching card and roster](docs/screenshots/inactive-roster-fix/inactive-card-five-users.png) |
| Users in separate schools | [Four archived users](docs/screenshots/inactive-roster-fix/qa-inactive-roster-school-scoped.png), [one deactivated instructor](docs/screenshots/inactive-roster-fix/qa-inactive-roster-other-school-scoped.png) |
| Home branding | [DriveED Hub home](docs/screenshots/home-branding/driveed-hub-home.png) |

### Verification

The local regression run passed: 22 admin GET routes loaded directly and through AJAX, six creation dialogs opened, 16 export/statistics endpoints succeeded, and 65 additional backend assertions passed. Tests also covered hour persistence, five login viewport sizes, new/existing schools, inactive-user filters, and school isolation. Temporary test data was removed.

[Full verification report and 49 screenshots](docs/screenshots/full-regression/README.md).

These results describe local testing; they do not certify the deployed site or every possible workflow.

### Database Update and Regression Command

Apply the migration that changes package total hours from an integer to a decimal before using fractional hours:

```sh
php artisan migrate --force
```

For the bundled local regression scripts, start the Docker app at http://localhost:8004 with the seeded demo accounts, install puppeteer-core in the local Node environment, and make Google Chrome available at the executable path configured in the scripts. The scripts use the driveed_hub_app container and create isolated test schools.

```sh
node tests/run-chat-regression.mjs
```

## TDC to PDC Enrollment

**Before:** students who already passed TDC were still blocked from applying for PDC.

**After:** passing TDC finishes the old course and lets the student submit a separate PDC application. Payment and school approval are still required. Previous training records and Combo courses are kept.

| Before | After |
| --- | --- |
| ![PDC application blocked](docs/screenshots/tdc-pdc-reproduction/before-blocked.png) | ![PDC application accepted](docs/screenshots/tdc-pdc-reproduction/after-submitted.png) |

## Other Fixes

Expand a finding to view its before-and-after pictures. Before pictures come from the findings report; after pictures show the updated app.

<details>
<summary>1. Pending Emails is easier to read</summary>

Names, email addresses, and descriptions have more space. The section can be opened when needed and closed afterward.

| Before | After |
| --- | --- |
| ![Pending emails before](docs/screenshots/oct31-findings/review/before/image2.png) | ![Pending emails after](docs/screenshots/oct31-findings/review/emails-expanded.png) |

</details>

<details>
<summary>2. Schedule sections can be opened and closed</summary>

Today's Lessons, Upcoming This Week, and Cancelled Schedules no longer need to stay open all the time.

| Before | After |
| --- | --- |
| ![Schedule sections before](docs/screenshots/oct31-findings/review/before/image3.png) | ![Schedule sections after](docs/screenshots/oct31-findings/review/panels-collapsed.png) |

</details>

<details>
<summary>3. Instructors can close the calendar window</summary>

The Close button now dismisses the window without showing an error.

| Before | After |
| --- | --- |
| ![Instructor calendar close error](docs/screenshots/oct31-findings/review/before/image4.png) | ![Instructor calendar after closing](docs/screenshots/oct31-findings/review/instructor-dialog-closed.png) |

</details>

<details>
<summary>4. Students can close the calendar window</summary>

Students can also close the window without an error message.

| Before | After |
| --- | --- |
| ![Student calendar close error](docs/screenshots/oct31-findings/review/before/image5.png) | ![Student calendar after closing](docs/screenshots/oct31-findings/review/student-dialog-closed.png) |

</details>

<details>
<summary>5. The calendar fits the screen</summary>

The calendar fits computer and phone screens without stretching beyond the page.

| Before | After |
| --- | --- |
| ![Oversized calendar before](docs/screenshots/oct31-findings/review/before/image6.png) | ![Calendar fitting the page after](docs/screenshots/oct31-findings/student-calendar-desktop.png) |

[View the calendar on a phone](docs/screenshots/oct31-findings/student-calendar-mobile.png).

</details>

<details>
<summary>6. Calendar details are easier to read</summary>

The instructor's calendar window has clearer headings, spacing, and a border. Cancelled lessons no longer offer a Log Session button here.

| Before | After |
| --- | --- |
| ![Calendar details before](docs/screenshots/oct31-findings/review/before/image7.png) | ![Calendar details after](docs/screenshots/oct31-findings/review/instructor-dialog-open.png) |

</details>

<details>
<summary>7. Download options stay out of the way</summary>

Schedule download options stay closed until needed and can be opened with one click.

| Before | After |
| --- | --- |
| ![Download options before](docs/screenshots/oct31-findings/review/before/image8.png) | ![Download options closed after](docs/screenshots/oct31-findings/review/exports-collapsed.png) |

[View the opened download options](docs/screenshots/oct31-findings/review/exports-expanded.png).

</details>

<details>
<summary>8. Each user group can have its own header colors</summary>

Settings now has separate header background and text colors for admins, instructors, and students. Colors save correctly even when the login page uses a picture.

The report did not include a before picture for this finding.

![New header color settings](docs/screenshots/oct31-findings/role-header-settings.png)

</details>

<details>
<summary>9. Past schedules are easier to recognize</summary>

Past schedules have a clear label and a different heading color so they are easier to distinguish from upcoming lessons.

| Before | After |
| --- | --- |
| ![Past schedules before](docs/screenshots/oct31-findings/review/before/image9.png) | ![Past schedules after](docs/screenshots/oct31-findings/review/past-schedules.png) |

</details>

<details>
<summary>10. Student training records can be downloaded</summary>

An Export PDF button lets users download the training list or an individual student's training record.

| Before | After |
| --- | --- |
| ![Training page before](docs/screenshots/oct31-findings/review/before/image10.png) | ![Training page with download button](docs/screenshots/oct31-findings/review/training-pdf-button.png) |

</details>

<details>
<summary>11. Enrollment review is properly arranged</summary>

Student details, licenses, and receipts appear in the correct places. The window fits computer and phone screens. Missing documents show a clear explanation.

| Before | After |
| --- | --- |
| ![Broken enrollment review before](docs/screenshots/oct31-findings/review/before/image11.png) | ![Enrollment review after](docs/screenshots/oct31-findings/enrollment-verification-desktop.png) |

[View enrollment review on a phone](docs/screenshots/oct31-findings/enrollment-verification-mobile.png).

</details>

<details>
<summary>12. Enrollments can be searched</summary>

Admins can find enrollments by student name, email, course, or enrollment number.

| Before | After |
| --- | --- |
| ![Enrollment list before](docs/screenshots/oct31-findings/review/before/image12.png) | ![Enrollment search after](docs/screenshots/oct31-findings/enrollment-search.png) |

</details>

<details>
<summary>13. Dashboard totals open the right list</summary>

Clicking a dashboard total opens its matching list, such as students, instructors, new students, active users, or active enrollments.

| Before | After clicking Students |
| --- | --- |
| ![Dashboard totals before](docs/screenshots/oct31-findings/review/before/image13.png) | ![Matching student list after](docs/screenshots/oct31-findings/review/dashboard-filtered-students.png) |

</details>

<details>
<summary>14. Finished training hours are clearly explained</summary>

When a student has completed the required hours, the form explains why another session cannot be added. TDC and PDC hours are counted separately for Combo students.

| Before | After |
| --- | --- |
| ![Extra session could be added before](docs/screenshots/oct31-findings/review/before/image15.png) | ![Completed training hours explanation after](docs/screenshots/oct31-findings/training-hours-completed.png) |

</details>

## Fixes October 30 2026

These earlier pictures show the fixes when they were added. Expand a fix to see its screenshot.

<details>
<summary>1. Pending account emails are visible</summary>

Admins can see who still needs to finish setting up an account, when the invitation expires, and whether to resend or remove it.

![Pending account emails](docs/screenshots/bug-report-2026-10-30/admin-user-management.png)

</details>

<details>
<summary>2. The Add Student window has a clear title</summary>

The window now says Add New Student, matching the other account forms.

![Add New Student window](docs/screenshots/bug-report-2026-10-30/admin-add-student.png)

</details>

<details>
<summary>3. Report downloads are in one menu</summary>

Users can download all records or just the selected records, as PDF or Excel files.

![Report download menu](docs/screenshots/bug-report-2026-10-30/admin-verify-session-completion.png)

</details>

<details>
<summary>4. Student training details open correctly</summary>

The View button opens the student's training record. If no training information is available, a clear message is shown.

![Student training details](docs/screenshots/bug-report-2026-10-30/admin-student-training.png)

</details>

<details>
<summary>5. Instructors can open calendar day details</summary>

Selecting a day shows its lessons and the available session actions.

![Instructor calendar day details](docs/screenshots/bug-report-2026-10-30/instructor-calendar-day.png)

</details>

<details>
<summary>6. Attendance, grades, and notes can be recorded together</summary>

Instructors can record lesson details directly from their schedule. The student list shows students assigned to them or students they have previously taught.

![Record a lesson from the schedule](docs/screenshots/bug-report-2026-10-30/instructor-session-log.png)

</details>

<details>
<summary>7. Recorded lessons appear for school approval</summary>

Admins can review the same lesson with its attendance, grade, training hours, and instructor notes.

![Recorded lesson ready for school approval](docs/screenshots/bug-report-2026-10-30/admin-logged-session-visible.png)

</details>

<details>
<summary>8. Instructors can view past schedules</summary>

Show Past Schedules reveals earlier lessons and their available actions.

![Past instructor schedules](docs/screenshots/bug-report-2026-10-30/instructor-past-schedules.png)

</details>

<details>
<summary>9. The session form includes attendance and grades</summary>

Lessons entered through the session form stay connected to the same training record used on the schedule.

![Session form with attendance and grades](docs/screenshots/bug-report-2026-10-30/instructor-manual-session.png)

</details>

<details>
<summary>10. Students can open calendar day details</summary>

Students can select a day to see their lessons, instructors, and lesson status.

![Student calendar day details](docs/screenshots/bug-report-2026-10-30/student-calendar-day.png)

</details>

<details>
<summary>11. The student calendar works on phones</summary>

The calendar and lesson window fit smaller screens.

![Student calendar on a phone](docs/screenshots/bug-report-2026-10-30/student-calendar-mobile.png)

</details>

<details>
<summary>12. School name and logo choices are respected</summary>

The header follows the school's chosen name, colors, and logo. Hidden names and removed logos stay hidden.

![School name and logo hidden](docs/screenshots/bug-report-2026-10-30/branding-name-and-logo-hidden.png)

</details>

## Fixes October 29 2026

<details>
<summary>1. Enrollment, payment, and receipt numbers are easier to follow</summary>

Each enrollment, payment, and receipt has a clear tracking number so staff can find the right record.

![Enrollment tracking numbers](docs/screenshots/capstone_01_enrollment_uids.png)

![Payment and receipt tracking numbers](docs/screenshots/capstone_02_payments_uids.png)

</details>

<details>
<summary>2. User groups have different colors</summary>

Admins, branch secretaries, instructors, students, and guests are easier to recognize in account lists.

![Different colors for user groups](docs/screenshots/capstone_03_role_badges_unique_colors.png)

</details>

<details>
<summary>3. Training logs can be archived and restored</summary>

Staff can move a log out of the active list without permanently deleting it, then restore it when needed.

![Active and archived training logs](docs/screenshots/capstone_04_session_logs_archive.png)

</details>

<details>
<summary>4. Inactive accounts can be archived and restored</summary>

System admins can archive unused accounts while keeping their history. Archived students and instructors can be restored to active status. Staff can also choose how long an account must be inactive before archiving it.

![Account archiving options](docs/screenshots/capstone_05_system_admin_user_archive.png)

![Archived accounts with restore buttons](docs/screenshots/capstone_05_system_admin_archived_tab.png)

</details>

<details>
<summary>5. Instructors and students have calendar views</summary>

Users can browse lessons by month and see their dates, course types, and booking status.

![Instructor calendar view](docs/screenshots/capstone_06_instructor_calendar_view.png)

![Student calendar view](docs/screenshots/capstone_07_student_calendar_view.png)

</details>

## Fixes October 27 2026

<details>
<summary>1. Payment codes and document pictures are easier to view</summary>

Payment codes open in a larger window for scanning. Users can enlarge or download the code and copy the payment number. Receipts and license pictures can also be enlarged, moved, rotated, or downloaded.

![Student payment page](docs/screenshots/12_actual_payment_checkout_page.png)

![Larger payment code window](docs/screenshots/12_qr_scan_modal_popup_opened.png)

![Copying the payment number](docs/screenshots/13_qr_scan_modal_copied_number.png)

![Payment code opened above the receipt form](docs/screenshots/17_qr_scan_modal_stacked_on_enrollment_modal.png)

</details>

<details>
<summary>2. Table action buttons stay within reach</summary>

On smaller screens, users can scroll across wide tables while keeping the action buttons visible.

![Table actions on a smaller screen](docs/screenshots/07_admin_table_narrow_screen_sticky_actions.png)

![Edit window opened from the table](docs/screenshots/08_admin_edit_modal_interacted.png)

</details>

<details>
<summary>3. User search and filters work together</summary>

Admins can search by name, email, or phone number and narrow the list by user group, account status, branch, or license expiry. Clear removes the selections when users want to start again.

![User search and filters](docs/screenshots/22_admin_user_management_search_and_filters.png)

![Users with expiring licenses](docs/screenshots/23_admin_user_management_license_filter_results.png)

![Empty search results with a Clear option](docs/screenshots/24_admin_user_management_empty_state_clear_filters.png)

</details>

<details>
<summary>4. Schedule downloads can be narrowed down</summary>

Users can choose dates and lesson status before downloading. School staff can also choose a student or instructor. Students receive their own records, and instructors receive records for their assigned lessons.

![Schedule download choices](docs/screenshots/03_admin_schedules_session_exports.png)

![Student schedule downloads](docs/screenshots/06_student_schedule_export.png)

</details>

## Earlier Fixes October 20 to 26 2026

<details>
<summary>1. Payment receipts open correctly</summary>

View Receipt opens the uploaded receipt instead of showing a missing-page error.

![Payment records with View Receipt](docs/screenshots/01_admin_payments_table.png)

![Opened payment receipt](docs/screenshots/02_payment_receipt_image_stream.png)

</details>

<details>
<summary>2. Instructors can download training logs</summary>

Instructors can download their full session history or one selected lesson as a PDF or Excel file.

![Training log download buttons](docs/screenshots/08_instructor_sessions_list_exports.png)

![Single lesson download buttons](docs/screenshots/09_instructor_session_detail_export.png)

</details>

<details>
<summary>3. License expiry reminders are easier to find</summary>

Students and instructors can see when their licenses are close to expiring, along with renewal guidance. Admins can find affected users and send a reminder.

![Student license reminder and renewal guide](docs/screenshots/12_student_license_expiration_alert.png)

![School license expiry overview](docs/screenshots/13_admin_license_expiration_monitoring_widget.png)

![License reminder actions in the user list](docs/screenshots/14_admin_user_management_license_filter.png)

![Instructor license reminder](docs/screenshots/15_instructor_license_expiration_alert.png)

</details>

<details>
<summary>4. Course transitions and certificate notices are easier to manage</summary>

Combo students can request their move from TDC to PDC from their dashboard for school review. Staff can also tell students when their certificate or claim stub is ready for pickup.

![Student training and pickup notice options](docs/screenshots/04_admin_student_training_claim_notice.png)

</details>

<details>
<summary>5. Schedule records can be downloaded</summary>

School staff and students can download schedule and attendance records. Schedule pages are also easier to read on computers and phones.

![School schedule download options](docs/screenshots/03_admin_schedules_session_exports.png)

![Student schedule download options](docs/screenshots/06_student_schedule_export.png)

</details>

<details>
<summary>6. Licenses and receipts can be uploaded without leaving the page</summary>

Users can replace a rejected license or submit a receipt in a pop-up window. The payment window also shows the school payment details and a scannable payment code.

![Receipt upload window](docs/screenshots/10_reupload_receipt_popup_modal.png)

![License upload window](docs/screenshots/11_reupload_license_popup_modal.png)

</details>

<details>
<summary>7. School admins can sign in and reach the dashboard</summary>

Sign-in problems that repeatedly sent school admins away from the dashboard were fixed. Missing information in a dashboard section no longer stops the rest of the page from opening.

![School admin dashboard](docs/screenshots/16_school_admin_dashboard_login_verified.png)

</details>

<details>
<summary>8. Progress pages handle missing lesson dates</summary>

Student progress and instructor reports can still open when a lesson does not yet have a scheduled date.

![Student progress page](docs/screenshots/17_instructor_student_progress_verified.png)

</details>

<details>
<summary>9. Students can enroll again after finishing a course</summary>

Completed courses stay in the student's history. Students can apply for another course or choose Enroll Again on a course they previously completed.

![Completed course with Enroll Again](docs/screenshots/18_course_completed_reenroll.png)

</details>

<details>
<summary>10. School pictures and branding can be changed</summary>

Users can update their profile pictures. School staff can change course pictures, the school logo, and header appearance. Removed or replaced pictures follow the user's choices.

![School appearance settings](docs/screenshots/20_admin_settings_smart_logo.png)

![Updated school logo in the header](docs/screenshots/21_admin_header_smart_logo.png)

</details>

<details>
<summary>11. Training downloads are available with session review</summary>

School staff can download training records from Verify Session Completion and narrow them by student, instructor, or dates. Reports include the selected names and dates.

![Training download choices beside session review](docs/screenshots/19_verify_session_completion_export_section.png)

</details>

<details>
<summary>12. School walkthrough guides are available</summary>

The [presentation guide](docs/CAPSTONE_DEFENSE_MASTER_GUIDE.md) and [school process walkthroughs](docs/SYSTEM_PROCESS_FLOWS_PRESENTATION.md) explain enrollment, payments, training, and common problems step by step.

</details>
