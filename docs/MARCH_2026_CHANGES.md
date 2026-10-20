# March 2026 Changes

> This document covers all major changes made in March 2026.  
> For the complete system overview, see `DEVELOPER_HANDOFF.md`.

---

## 1. Unified Enrollment & Payment Architecture (March 26)

**Goal:** Make Enrollment the single authority for acceptance and role promotion. Payments is history/audit only.

### What Changed

| File | Change |
|------|--------|
| `EnrollmentRequestController.php` | Added `processApproval()` — single-source atomic approval (sets approved + promotes guest→student + locks course + sends notifications). Both `approve()` and `bulkApprove()` delegate to it. Promotion stripped from `verifyLicense()`, `verifyPayment()`, `updatePaymentStatus()`. Added `apiVerifyLicense()` for API route contract. Fixed `reject_url` in `apiShow()`. |
| `PaymentController.php` | `update()` method deleted. Dead `PaymentVerificationService` import removed. |
| `PaymentVerificationService.php` | `approve()`, `reject()`, `refund()` methods deleted. Only `logStatus()` retained for audit. |
| `PaymentGating.php` | Side-door auto-promotion fallback removed. |
| `web.php` | `update` excluded from payments resource. Enroll/checkout routes moved out of `guest.role` middleware to allow student re-enrollment. API verify-license route points to `apiVerifyLicense`. |
| `EnsureGuestRole.php` | Unchanged, but enrollment routes moved outside its scope. |
| `payments.blade.php` | Approve/Reject/Refund buttons, `verifyPayment()` JS, `markAsPaid()`, "Refunded" filter tab all removed. |
| `enrollment-requests/index.blade.php` | Approval confirmation updated to reflect immediate role promotion. `pending_verification` → `pending` in modal JS. |
| `GuestController.php` | `pending_verification` → `pending` (valid enum). |

### Architecture Invariant
```
processApproval() is the ONLY place guest→student happens.
No other controller, middleware, or service may call promoteToStudent().
```

---

## 2. GCash Checkout Flow (March 24)

### New Files
- `resources/views/school/guest/checkout.blade.php` — Premium GCash payment page
- `app/Http/Controllers/GuestController.php` → `showPayment()`, `submitPayment()`

### How It Works
1. Guest enrolls → redirected to GCash checkout
2. Checkout page shows school's QR code, amount, reference input, receipt upload
3. Guest submits → enrollment gets `payment_status = pending`
4. Admin verifies via Quick-Verify Modal → sets `payment_status = paid`

### Admin GCash Settings
- `/{school}/admin/settings` → GCash Configuration section
- Admins upload QR code image and set GCash number
- Stored in `school_settings` table (gcash_qr_path, gcash_number)

---

## 3. Email Migration to Resend (March 24)

### What Changed
- **Driver:** `MAIL_MAILER=resend` (was `log`)
- **From Address:** Dynamic, school-branded (e.g., `noreply@{school-slug}.driveedhub.com`)
- **Package:** `resend/resend-laravel`

### .env Config
```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxx
```

---

## 4. Admin Quick-Verify Modal (March 24)

### What It Does
- One-click modal to verify payment + license on enrollment requests
- Opens from the enrollment request list (eye icon)
- Shows student name, course, payment receipt image, license image
- "Verify Payment" and "Verify License" buttons update sub-statuses via AJAX

### API Endpoints
| Route | Method | Controller |
|-------|--------|------------|
| `enrollments/api/{id}` | GET | `apiShow()` |
| `enrollments/api/{id}/verify-payment` | POST | `verifyPayment()` |
| `enrollments/api/{id}/verify-license` | POST | `apiVerifyLicense()` |

---

## 5. Event Attendance Portal (March 25)

### New Capabilities
- Public attendance portal with signed GET links in emails
- Server-side timestamping for forensic data integrity
- Camera capture + drag-and-drop image upload for check-in selfies
- Multi-day event schedule support
- Cross-event status isolation (check-in status scoped per-event)

### Key Files
- `PublicAttendanceController.php`
- `resources/views/attendance/` — public attendance views

---

## 6. Sidebar $isAjax Fix (March 26)

### Problem
Sidebar toggle was unresponsive on dashboard pages loaded via AJAX navigation.

### Fix
All dashboard controller methods now pass `$isAjax = $request->ajax()` to their views. Views conditionally extend `layouts/app.blade.php` (full layout) or `layouts/ajax.blade.php` (content-only) based on this flag.

### Affected Controllers
Application-wide audit — every controller that returns a dashboard view.

---

## 7. Hostinger Production Alignment (March 26)

### Storage
- `FILESYSTEM_DISK=local` (not `public` or S3)
- All file access via `StorageController` stream methods (not direct URL)
- `php artisan storage:link` required for public symlink

### Path Resolution
- Case-sensitive paths on Linux (Hostinger)
- `storage_path()` instead of `public_path()` for file access
- Standardized on `local` disk driver throughout codebase

---

## 8. Attendance Hardening (March 25)

- Server-side validation prevents unauthorized clock-out
- Check-in status scoped per-event (no cross-event leakage)
- Multi-day event schedules display correctly in event list UI

---

## Summary of Files Touched in March

| Area | Key Files |
|------|-----------|
| Enrollment architecture | `EnrollmentRequestController.php`, `PaymentController.php`, `PaymentVerificationService.php`, `PaymentGating.php`, `GuestController.php`, `web.php` |
| GCash checkout | `GuestController.php`, `checkout.blade.php`, `settings.blade.php` |
| Email | `.env`, mail config, Resend package |
| Payments UI | `payments.blade.php` |
| Enrollment UI | `enrollment-requests/index.blade.php` |
| Attendance | `PublicAttendanceController.php`, attendance views |
| Sidebar | Multiple dashboard controllers |
| Hostinger | `StorageController.php`, `.env`, storage config |
| Storage Standard | `ReceiptStorageService.php`, `StorageController.php`, `GuestController.php` |

---

## 9. Receipt Access & Standardized Storage (March 27)

### Problem
- 403 Forbidden errors when viewing GCash receipts.
- Enrollment Verification Modal failing to load document previews.
- Inconsistent storage paths across Guest vs Admin modules.

### Fix
- **Standardized New Uploads:** Used `ReceiptStorageService` in `GuestController` to ensure all new screenshots go to `local/receipts/{school_id}/`.
- **Robust Retrieval:** Updated `StorageController` and `EnrollmentRequestController` to check both `local` and `public` disks and allow multiple legacy prefixes.
- **Architectural Directive:** Formalized the "Single-Root Storage" rule in `DEVELOPER_HANDOFF.md` for future AI-agent compliance.
