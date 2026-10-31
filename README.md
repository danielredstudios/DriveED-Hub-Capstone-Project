# DriveED Hub

A school portal for managing students, instructors, courses, schedules, payments, and training records.

## Latest Fixes

- Students who pass TDC can apply for PDC without cancelling their finished TDC course.
- Previous training records and Combo courses are kept unchanged.
- Finishing an older course does not interrupt a student's newer course.
- Passing notes are saved and kept when the passing action is repeated.
- Extra sessions cannot be added once the required training hours are complete.
- Grades and feedback can still be corrected without adding duplicate sessions.
- Saving two sessions at the same time cannot exceed the student's remaining training hours.
- Admins, instructors, and students can have their own header colors.
- Review windows close properly, including when using the Escape key.

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
