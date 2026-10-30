# Bug report fixes and verification

The issues in `Bugs and Reports.docx` were addressed in the local DriveED Hub application. Verification covers the affected admin, instructor, and student workflows. Screenshots use explicitly named QA fixtures; those records were removed and the school's original branding settings restored after capture.

| Reported issue | Result |
| --- | --- |
| Pending emails are only in Settings | User Management now lists pending student/instructor setup emails, recipient, role, expiry, and resend/remove actions. Settings links to User Management. Branch-secretary results remain scoped to their branch. |
| Header color does not update across roles | Login and authenticated layouts use the same configured header background and text settings. Admin, instructor, and student browser checks verify the configured color. |
| Removed logo reappears or breaks | Removed the hardcoded logo fallback and hardcoded logo descriptions. An empty logo setting renders no school image. |
| Hidden school name reappears or is duplicated | Both header layouts honor the boolean setting, including the logo-only layout. The configured name appears once when enabled. |
| Header uses the wrong school name | Headers and the affected admin page subtitles prefer the configured display name and otherwise use the school's stored name. |
| Duplicate export controls | Session-report controls use one Export Report menu with Export All Records and Export Filtered Records, retaining PDF and Excel formats. |
| Inconsistent action buttons | Shared management/session action styles use common spacing, sizing, colors, and icon placement. Session tables now label View, Edit, Archive, Restore, and export actions. |
| Student Training List View opens User Management | Passed-student View opens the same training review as Review. Students without an enrollment receive `This student has no data/information.` An invalid route argument in the review page was also corrected. |
| Manual logging exposes unrelated students | The enrollment query and server authorization require a current or historical instructor/student connection. |
| Manual logs are missing from the admin view | Manual logs link to bookings in the admin queue. Seventeen existing logs were linked successfully. Admin cards display attendance, grade, actual logged hours, and instructor notes. |
| Invite should say Add | Student creation now says Add New Student. Instructor and admin creation labels already use Add. |
| Calendar days are not interactive | Student and instructor days open native dialogs with the day's schedules. Instructor entries open session logging. Dialogs support keyboard interaction and Escape. The mobile grid fits all seven columns. |
| Past schedules are inaccessible | Restored Show Past Schedules and fixed the hidden CSS class that prevented the toggle from revealing records. |
| Schedule attendance should support full logging | The schedule form saves attendance, grade, and notes through the existing lesson endpoint. Manual logging and schedule logging share a unique booking-to-session link; verification, edits, and cancellation synchronize that record. Student calendars retain instructor-logged lessons awaiting verification. |

## Verification

| Check | Result |
| --- | --- |
| Bug-report regressions | 21 passed; transactional test data rolled back. Includes zero grades, assignment authorization, unique logging, manual edits, cancellation, preservation of existing grades during legacy linking, branding variants, and fake-mail resend/removal. |
| Existing session/export/claim regressions | 30 passed. |
| PDF/Excel report regressions | 22 passed. Includes real binary signatures, role filters, empty filters, and the unified export menu. |
| Browser workflow | 23 passed across admin, instructor, and student accounts; no JavaScript page errors recorded. Includes an actual schedule-form save followed by admin and student checks. |
| PHP and templates | Changed PHP files passed syntax checks; all Blade templates compiled successfully. |
| Patch whitespace | `git diff --check` passed. |

No real invitation emails were sent. Resend uses a fake mail transport in the backend test. Browser fixtures use an `example.invalid` recipient and are removed afterward.

## Screenshots

- [Pending emails in User Management](screenshots/bug-report-2026-10-30/admin-user-management.png)
- [Add Student label](screenshots/bug-report-2026-10-30/admin-add-student.png)
- [Unified report export menu](screenshots/bug-report-2026-10-30/admin-verify-session-completion.png)
- [Student training review](screenshots/bug-report-2026-10-30/admin-student-training.png)
- [Instructor calendar day](screenshots/bug-report-2026-10-30/instructor-calendar-day.png)
- [Schedule session-log form](screenshots/bug-report-2026-10-30/instructor-session-log.png)
- [Saved log visible to admin](screenshots/bug-report-2026-10-30/admin-logged-session-visible.png)
- [Past instructor schedules](screenshots/bug-report-2026-10-30/instructor-past-schedules.png)
- [Manual session form](screenshots/bug-report-2026-10-30/instructor-manual-session.png)
- [Student calendar day](screenshots/bug-report-2026-10-30/student-calendar-day.png)
- [Mobile calendar dialog](screenshots/bug-report-2026-10-30/student-calendar-mobile.png)
- [School name and logo hidden](screenshots/bug-report-2026-10-30/branding-name-and-logo-hidden.png)
- [Machine-readable browser results](screenshots/bug-report-2026-10-30/results.json)

## Running the checks

Run in the application runtime:

```text
php tests/verify-bug-report.php
php tests/verify-session-features.php
php tests/verify-session-report-export.php
php artisan view:cache
```

The browser capture uses the existing local Docker app, seeded demo accounts, Chrome, and `puppeteer-core`:

```text
node tests/capture-bug-report.mjs
```

For another installation, apply the additive migration before using the updated logging forms. Link its existing manual logs afterward:

```text
php artisan migrate --force
php artisan sessions:link-bookings --school=1
```

The link command is idempotent for linked records and reports any legacy conflicts requiring review. It does not remove historical logs.

## Design verification for the changed workflows

Reading: an existing driving-school operations interface for admins, instructors, and students. Preserve the current visual language; energy 1, rhythm 1, motion 1.

- Hard gate PASS for the changed workflows: routes and forms exercised, keyboard dialogs checked, no fabricated content, and the 390px calendar checked for horizontal overflow.
- Purpose gate PASS: dialogs provide focused day details/session entry; existing colors and typography preserve the school settings; icon labels identify actions.
- Liveliness PASS: existing hierarchy and school accent retained; no decorative motion added to routine operations.
- Craftsmanship PASS for this scope: repeat saves preserve one log; empty/error states are present; mobile dialog and calendar checks pass; screenshots were inspected.

The mechanical style scan also reported 27 warnings in existing page styles, chiefly accent borders and layout-property transitions. This work preserves the existing design and does not represent a full accessibility or visual audit of every application page.
