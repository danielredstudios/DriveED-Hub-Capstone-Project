# TDC to PDC enrollment block reproduction

This test reproduces the legacy data state on the current application code. It does not restore an old release or modify an existing student.

## Before

A temporary, email-verified student had already passed TDC and had a verified license. Their standalone TDC enrollment was still approved, their course lock was true, and their active enrollment reference pointed at TDC. Three completed theoretical sessions totaled 15 hours.

The actual student enrollment form submitted a PDC application. The server rejected it with:

> You already have an active course in this school. Complete or cancel it before enrolling in another course.

The database confirmed that no PDC enrollment had been created.

![Blocked PDC submission](before-blocked.png)

## After

The test invoked the corrected shared `EnrollmentRequest::markTheoreticalPassed()` lifecycle for the temporary TDC enrollment. It completed the standalone TDC enrollment and called the student's course-unlock method. The TDC passing flag and student role were retained.

Submitting the same PDC form then created a separate pending PDC enrollment. The browser showed the success notification and Payment Needed state. No receipt was uploaded, no payment was made, and no admin approval was performed.

![Accepted PDC submission](after-submitted.png)

## Verification and cleanup

- The test student's three TDC sessions and their 15 hours were unchanged during the correction, verified by a SHA-256 comparison.
- Outgoing mail used Laravel's array transport in a temporary localhost-only Docker web instance.
- Cookie sessions avoided creating persistent session records for the test login.
- Temporary student, enrollments, training sessions, notifications, and authentication log were removed after capture.
- Original table counts and hashes matched the baseline after cleanup: 29 students, 32 enrollment requests, 17 session logs, 62 bookings, 4 progress records, 2 phase transitions, 441 notifications, and 1055 system logs.
- All 9 Combo enrollments were unchanged. Original student row hashes also confirm that their lock fields were unchanged.
- Screenshot enrollment IDs refer to the removed temporary records.

The temporary web instance was stopped after verification. The main local app and its existing enrollments remain available.

[Browser assertions and state snapshots](browser-results.json) · [Cleanup verification](cleanup.json) · [Side-by-side comparison](before-after-comparison.png)

The reusable test scripts are `tests/tdc-pdc-reproduction-fixture.php` and `tests/capture-tdc-pdc-reproduction.mjs`. The fixture refuses to run unless mail uses the array transport and sessions use cookies. Prepare it in the mail-disabled reproduction container before running the browser script; the browser script cleans up its fixture in a `finally` block and requires successful cleanup verification.
