# Audit Findings — March 9, 2026 (Strict Verified Findings V2)

## Purpose
This is a findings-only report. It is not a task ticket pack or implementation handoff format.

It answers one question: what is actually true in the current codebase, with evidence.

## Final Verdict Snapshot
1. Confirmed: Email behavior is inconsistent across important enrollment/lifecycle transitions.
2. Confirmed: OTP/reset flows still use `Mail::raw(...)` in controllers.
3. Confirmed: Session reminders send email; other command flows are not equivalent by default.
4. Overstated: "Queue architecture is broken."
5. Dropped: "failed_jobs is a defect" as a standalone finding.

## Confirmed Findings

### CF-01: Raw inline email is used in OTP and reset flows
- Severity: Medium
- Confidence: High
- Classification: Confirmed

Evidence:
- `app/Http/Controllers/AuthController.php:285` uses `Mail::raw(...)` for verification code flow.
- `app/Http/Controllers/GuestController.php:80` uses `Mail::raw(...)` for verification flow.
- `app/Http/Controllers/GuestController.php:516` uses `Mail::raw(...)` for resend verification flow.
- `app/Http/Controllers/PasswordResetController.php:76` uses `Mail::raw(...)` for password reset flow.

Why it is a finding:
- Message construction logic is embedded in controllers, which increases duplication and inconsistency risk.
- Harder to standardize rendering, subjects, localization, and future template changes.

---

### CF-02: Enrollment email behavior is not uniform across lifecycle actions
- Severity: Medium
- Confidence: High
- Classification: Confirmed

Evidence (email send present):
- `app/Http/Controllers/EnrollmentRequestController.php:180` approve sends `EnrollmentApproved`.
- `app/Http/Controllers/EnrollmentRequestController.php:262` reject sends `EnrollmentRejected`.
- `app/Http/Controllers/EnrollmentRequestController.php:588` bulk approve sends email.
- `app/Http/Controllers/EnrollmentRequestController.php:666` bulk reject sends email.

Evidence (non-uniformity in other transitions):
- Verified payment/cancel/complete/theoretical/license related paths do not show equivalent email sending in the checked controller paths, while in-app notifications are used.

Why it is a finding:
- Users get email for some major status changes but not others.
- Creates inconsistent user communication and support ambiguity.

---

### CF-03: Command-level notification behavior differs by command
- Severity: Low
- Confidence: High
- Classification: Confirmed behavior mapping

Evidence:
- `app/Console/Commands/SendSessionReminders.php:65` sends `SessionReminder` mail.
- `app/Console/Commands/ConfirmQueuedBookings.php` (verified path) performs booking status updates without equivalent direct email send.

Why it is a finding:
- Important operational behavior difference that should be intentional and documented.

## Overstated Claims (Downgraded)

### OC-01: "Queue architecture is broken"
- Previous claim: Too strong
- New classification: Scalability/reliability improvement opportunity
- Confidence: High

Evidence:
- Mailables in `app/Mail/*.php` are standard `Mailable` classes; no `ShouldQueue` implementation found.
- Verified code paths send synchronously with `->send(...)`.

Interpretation:
- Current behavior is valid for synchronous delivery.
- Queue-backed email is recommended for scale/retries, but this is not automatically a current defect.

## Dropped Claims

### DC-01: "failed_jobs table indicates a defect"
- Status: Dropped as standalone finding
- Confidence: High

Evidence:
- Queue config includes failed jobs mapping: `config/queue.php:109`.
- Migration creates failed jobs table: `database/migrations/0001_01_01_000002_create_jobs_table.php:34`.

Interpretation:
- `failed_jobs` existence is expected in Laravel queue architecture.
- Worker/scheduler uptime is a deployment-runtime check, not a repository defect by itself.

## Clean Priority List (Findings-Only)
1. P1: Define and enforce a consistent lifecycle notification policy (CF-02).
2. P2: Replace OTP/reset `Mail::raw(...)` with structured mail templates/classes (CF-01).
3. P3: Decide and document command-level notification intent across scheduler commands (CF-03).
4. Optional: Move to queue-backed email if production volume/reliability requirements justify it (OC-01).

## Certainty Notes
- All "Confirmed" items above are tied to concrete, cited repository references.
- Any runtime infrastructure statements (workers/scheduler actually running in production) require environment verification and are intentionally excluded as code defects.
