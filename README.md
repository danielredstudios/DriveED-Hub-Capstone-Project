# 🚗 DriveED Hub — Driving School Management System
**Capstone Project | LPU Cavite**

DriveED Hub is a multi-tenant web application designed for driving schools, providing comprehensive management for student enrollments, course progressions, instructor assignments, driving sessions, payments, and automated regulatory reminders.

---

## 📋 Summary of Fixes & Enhancements (October 24, 2026)

This release addresses all consultation recommendations from defense advisors and fixes identified during regression testing.

### 1. 💳 Payment & Transactions Receipt 404 Bug Fix
- **Problem:** Clicking "View Receipt" in Admin Payments & Transactions returned HTTP 404.
- **Root Cause:**
  - `ReceiptStorageService@getUrl` defaulted to route parameter `'default'` when school context was unbound, causing `{school:slug}` route binding failure.
  - Strict directory prefix checks blocked legacy storage paths (`guest-data/`).
  - Missing `proof_url` accessor on `Payment` model.
  - Windows backslash directory separator mismatches (`\` vs `/`).
- **Resolution:**
  - Added robust school slug resolution fallback in [`app/Services/ReceiptStorageService.php`](app/Services/ReceiptStorageService.php).
  - Added `getProofUrlAttribute()` accessor in [`app/Models/Payment.php`](app/Models/Payment.php) with fallback to `enrollmentRequest->payment_proof_path`.
  - Upgraded [`app/Http/Controllers/StorageController.php`](app/Http/Controllers/StorageController.php) to normalize directory separators, whitelist `guest-data/`, `receipts/`, `screenshots/`, and `proof_of_payment/`, and check across `local` disk, `public` disk, and `public_path()`.

---

### 2. 📊 Instructor Session Log Exports (Whole & Per-Session)
- **Problem:** Instructors needed the ability to export session completion records either in bulk (whole history) or individually (per single session) in clean PDF and native Excel (.xlsx) formats.
- **Resolution:**
  - Enhanced [`app/Http/Controllers/SessionReportController.php`](app/Http/Controllers/SessionReportController.php) to accept `session_id` (single completion log) or `booking_id` (single booked lesson), generating dynamic titles and filenames (`session-log-{id}-{school}-{date}.[pdf|xlsx]`).
  - Updated [`app/Http/Controllers/ExportController.php`](app/Http/Controllers/ExportController.php) for backward compatibility with `session_id` query parameters.
  - **Whole Session Export:** Added header dropdown in [`resources/views/school/instructor/sessions/index.blade.php`](resources/views/school/instructor/sessions/index.blade.php) for 1-click bulk PDF & Excel downloads.
  - **Per-Session Row Export:** Added direct 1-click PDF (red icon) and Excel (green icon) download actions on each session row and mobile card in the sessions table.
  - **Single Session Detail Export:** Added dedicated "Export Session (PDF)" and "Export Session (Excel)" action buttons in the header of [`resources/views/school/instructor/sessions/show.blade.php`](resources/views/school/instructor/sessions/show.blade.php).

---

### 3. ⏰ Driver's License Expiration Reminder System
- **Requirement:** Automatically remind students and instructors when their driver's licenses/permits approach expiration (within 30 days) and emphasize that actual renewal is handled directly by the LTO.
- **Resolution:**
  - Added database migration [`database/migrations/2026_10_24_000001_add_license_expiration_to_students_and_instructors.php`](database/migrations/2026_10_24_000001_add_license_expiration_to_students_and_instructors.php) adding `license_expiration_date` and `license_number` to `students` and `instructors` tables.
  - Implemented [`app/Services/LicenseExpirationReminderService.php`](app/Services/LicenseExpirationReminderService.php) with 30-day proactive reminder window and 7-day notification cooldown deduplication.
  - Created Artisan command `license:check-expirations` ([`app/Console/Commands/CheckLicenseExpirations.php`](app/Console/Commands/CheckLicenseExpirations.php)) registered in [`routes/console.php`](routes/console.php).
  - Created student dashboard alert banner [`resources/views/school/student/partials/license-expiration-alert.blade.php`](resources/views/school/student/partials/license-expiration-alert.blade.php) showing days remaining, direct "Update License Details" action, and an official link to the LTO LTMS Portal.
  - Hardened `school_route()` in [`app/Support/helpers.php`](app/Support/helpers.php) to defend against argument order mismatches.

---

### 4. 🎨 UI/UX Consistency (No Gradients Standard)
- **Requirement:** Maintain solid, clean colors throughout the entire application. Avoid linear/radial gradients.
- **Resolution:**
  - Refactored all views across Admin, Instructor, Student, Guest, and Email templates to use solid brand tokens (`#2563eb`, `#1d4ed8`, `#10b981`, `#059669`, `#1e293b`).
  - Standardized contrast ratios and tap target dimensions for accessibility and defense presentation compliance.

---

### 5. 🔄 Phase Progression Redundancy Removal & Certificate Claim Stub Notices
- **Problem:** Standalone Phase Progression page was redundant. Students needed clear notices when certificates are ready for pickup.
- **Resolution:**
  - Removed standalone page; streamlined bundle transitions (TDC to PDC) through interactive dashboard pop-up modals and verified inside Admin Enrollment Requests.
  - Added "Send pickup notice" button in Admin Student Training List allowing administrators to dispatch customized claim notices.
  - Integrated certificate claim cards directly onto the student dashboard with pickup instructions.

---

### 6. 📖 Defense Presentation & Process Documentation
- **Added Assets:**
  - [`docs/CAPSTONE_DEFENSE_MASTER_GUIDE.md`](docs/CAPSTONE_DEFENSE_MASTER_GUIDE.md): 2-Laptop defense script and live role-switching guide between Laptop 1 (Guest/Student) and Laptop 2 (Admin/Instructor).
  - [`docs/SYSTEM_PROCESS_FLOWS_PRESENTATION.md`](docs/SYSTEM_PROCESS_FLOWS_PRESENTATION.md): Detailed scenario walkthroughs (Smooth Enrollment, Special Exceptions, and Expired License Flow).
  - [`docs/SYSTEM_DOCUMENTATION.md`](docs/SYSTEM_DOCUMENTATION.md): Updated with regulatory boundaries clarifying that driver's license issuance/renewal is exclusively managed by the Land Transportation Office (LTO).

---

## 🧪 Automated Test Suite Verification

All **6 regression test suites (105 automated checks)** are passing with a **100% pass rate**:

| Suite | Scope | Checks | Status |
|---|---|:---:|:---:|
| `tests/verify-session-features.php` | Session report exports (PDF & Excel), whole & per-session exports, and claim notices | 30 | ✅ PASS |
| `tests/verify-bundle-transitions.php` | Bundle transitions, modal license uploads, and request workflows | 18 | ✅ PASS |
| `tests/verify-receipt-serving.php` | Receipt streaming security, multi-disk fallback, path traversal defense | 14 | ✅ PASS |
| `tests/verify-license-expiration-reminders.php` | Expiration detection, notification dispatch, deduplication cooldown | 6 | ✅ PASS |
| `tests/verify-module-toasts.cjs` | Settings & Schedule toast deduplication across Admin, Instructor, and Student | 30 | ✅ PASS |
| `tests/verify-schedule-submission.cjs` | Schedule slot booking, capacity checks, and feedback validation | 7 | ✅ PASS |
| **Total** | | **105** | **100% PASS** |

---

## 🚀 Running the Project Locally (Docker)

```bash
# 1. Start containers
docker compose up -d

# 2. Run migrations
docker compose exec app php artisan migrate

# 3. Seed demo data (optional)
docker compose exec app php artisan db:seed --class=DriveEdHubSeeder

# 4. Run automated test suites
docker compose exec app php tests/verify-session-features.php
docker compose exec app php tests/verify-receipt-serving.php
docker compose exec app php tests/verify-license-expiration-reminders.php
```

- **Application URL:** `http://localhost:8004/drived-hub`
- **Default School Slug:** `drived-hub`
- **Admin Login:** `admin1@driveedhub.test` / `DriveDemo123`
- **Instructor Login:** `instructor1@driveedhub.test` / `DriveDemo123`
- **Student Login:** `student1@driveedhub.test` / `DriveDemo123`
