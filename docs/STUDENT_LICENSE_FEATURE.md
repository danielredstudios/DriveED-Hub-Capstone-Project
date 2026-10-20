# Student Driver's License Verification Feature

## Overview
Students must upload and have their student driver's license verified by an admin before they can enroll in **practical (behind-the-wheel)** courses. Theoretical courses remain open to all.

## Feature Flow
1. **Student uploads** license (PDF/JPG/PNG, max 5MB) via guest dashboard
2. License status changes from `none` → `pending`
3. **Admin sees** license status badge in Enrollment Requests page
4. Admin can **View** the document, **Verify** it, or **Reject** it with a reason
5. If verified → student can enroll in practical courses
6. If rejected → student sees rejection reason and can re-upload

## License Statuses
| Status | Description |
|--------|-------------|
| `none` | No license uploaded (default) |
| `pending` | Uploaded, awaiting admin review |
| `verified` | Admin approved the license |
| `rejected` | Admin rejected (reason provided) |

---

## Files Changed

### Migration
- `database/migrations/2025_01_01_000010_add_student_license_fields.php`
  - Adds to `students` table: `student_license_path`, `student_license_status` (enum), `student_license_verified_at`, `student_license_verified_by` (FK → admins), `student_license_rejection_reason`

### Models
- `app/Models/Student.php`
  - Added 5 license fields to `$fillable`
  - Added `student_license_verified_at` to `$casts`
  - Updated `canEnrollPractical()` — now requires `hasPassedTheoretical() && hasVerifiedLicense()`
  - New methods: `hasVerifiedLicense()`, `isLicensePending()`, `isLicenseRejected()`, `hasNoLicense()`, `licenseVerifiedBy()`

### Controllers
- `app/Http/Controllers/GuestController.php`
  - New method: `uploadLicense()` — validates file, stores in `student-licenses/` on public disk, sets status to `pending`

- `app/Http/Controllers/EnrollmentRequestController.php`
  - New method: `verifyLicense()` — sets status to `verified`, records admin ID and timestamp
  - New method: `rejectLicense()` — sets status to `rejected` with reason

### Validator
- `app/Support/EnrollmentValidator.php`
  - `canEnrollInCourse()` now blocks practical enrollment if license is not verified

### Routes (`routes/web.php`)
- `POST /{school}/guest/upload-license` → `GuestController@uploadLicense` (name: `schools.guest.uploadLicense`)
- `POST /{school}/admin/enrollments/student/{student}/verify-license` → `EnrollmentRequestController@verifyLicense` (name: `schools.admin.enrollments.verifyLicense`)
- `POST /{school}/admin/enrollments/student/{student}/reject-license` → `EnrollmentRequestController@rejectLicense` (name: `schools.admin.enrollments.rejectLicense`)

### Views
- `resources/views/school/guest/dashboard.blade.php`
  - New "Student Driver's License" section between enrollment status and courses
  - Shows upload form (none/rejected), pending message, or verified badge

- `resources/views/school/guest/courses.blade.php`
  - Practical courses show disabled "License Required" button if license isn't verified

- `resources/views/school/admin/enrollment-requests/index.blade.php`
  - License status badge below learner name/email
  - View/Verify/Reject buttons for pending licenses
  - License rejection modal with reason textarea

---

## Testing Steps

### Prerequisites
- Run migration: `php artisan migrate`
- Ensure storage link exists: `php artisan storage:link`
- Login as a guest account (e.g., register a new guest at any school)

### Test 1: License Upload (Guest)
1. Log in as a guest (e.g., register at `/{school}/register`)
2. Go to guest dashboard
3. Verify the "Student Driver's License" section appears with "No License Uploaded" status
4. Upload a valid file (JPG/PNG/PDF under 5MB)
5. Verify status changes to "Pending Verification"
6. Verify file is saved in `storage/app/public/student-licenses/`

### Test 2: Invalid Upload
1. Try uploading a file > 5MB → should show validation error
2. Try uploading a .txt or .docx file → should show format error
3. Try submitting without selecting a file → should show required error

### Test 3: Admin Verify License
1. Log in as admin (e.g., `admin@gmail.com` / `password`)
2. Go to Enrollment Requests page (`/{school}/admin/enrollments`)
3. Find the guest who uploaded a license
4. Verify the "Pending" license badge appears under their name
5. Click "View" to see the uploaded document in a new tab
6. Click "✓ Verify" and confirm
7. Verify badge changes to "Verified"

### Test 4: Admin Reject License
1. Find a guest with a pending license
2. Click "✗ Reject" button
3. Enter a rejection reason in the modal
4. Submit the rejection
5. Verify badge changes to "Rejected"
6. Log in as the guest → verify dashboard shows rejection reason and re-upload form

### Test 5: Practical Course Enrollment Blocked
1. Log in as a guest with NO verified license
2. Go to Courses page
3. For any **practical** course, verify the button shows "License Required" (disabled)
4. For any **theoretical** course, verify the "Enroll" button is still active
5. Now verify the license (via admin) and reload courses page
6. Practical courses should now show the active "Enroll" button

### Test 6: Re-upload After Rejection
1. As a guest with a rejected license, go to dashboard
2. Verify rejection reason is displayed
3. Upload a new valid license file
4. Verify old file is deleted and new file is saved
5. Verify status resets to "Pending Verification"
6. Verify rejection reason is cleared

### Test 7: Enrollment Validator Backend Check
1. Try to directly POST to the enroll route for a practical course without a verified license
2. Verify the enrollment is blocked with the appropriate message
3. This protects against anyone bypassing the frontend disabled button

---

## Database Schema (students table additions)
```sql
student_license_path           VARCHAR(255) NULL
student_license_status         ENUM('none','pending','verified','rejected') DEFAULT 'none'
student_license_verified_at    TIMESTAMP NULL
student_license_verified_by    BIGINT UNSIGNED NULL (FK → admins.id ON DELETE SET NULL)
student_license_rejection_reason TEXT NULL
```

## File Storage
- Disk: `public`
- Directory: `student-licenses/`
- Access URL: `/storage/student-licenses/{filename}`
