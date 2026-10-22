# Session reports, module feedback, and claim notices

- Admins use School Settings. Students and Instructors use the Settings link for
  their account details. Settings and Schedule actions use the shared success
  and error toasts, labeled with the module name. Validation failures no longer
  produce duplicate profile/settings toasts or a false success message.
- Each role's Schedule page includes a Session report toolbar with optional
  start/end dates and PDF/Excel downloads. Reports include date/time, student,
  course, instructor, hours, status, attendance, grade, notes, both feedback
  fields, skills, and source record. Training logs and bookings are labeled
  separately; logged training hours are not added to booking hours.
- Students export only their own records. Instructors export only records
  assigned to them. Branch secretaries export only their branch. School Admins
  can export school records. Downloads reject reports larger than 5,000 records
  rather than silently omitting rows.
- Excel reports are native XLSX files with wrapped notes, frozen headings,
  filters, alternating rows, and numeric hours. User text is stored as text to
  prevent formula execution. PDF reports use landscape pages, readable details,
  repeated column headings, and page numbers.
- In Admin > Student Training List, use **Send claim notice** beside a student.
  Choose Certificate or Claim stub, customize collection instructions, and send.
  The student receives a persistent bell notification and their latest claim
  notice appears on the dashboard, including after the notification is read.
  Readiness is announced by the Admin; this does not generate a certificate or
  record that the student has collected it.
- Receipt generation is unchanged pending the client's format.

Verification:

```sh
node tests/verify-module-toasts.cjs
php tests/verify-session-features.php
php tests/verify-bundle-transitions.php
```

The session-feature checks use existing local demo accounts and roll back their
database changes. Sample report files are written under ignored `tmp/report-verification/`.
