# Testing Guide - New Features
**DriveED Hub Driving School Management System**  
*Last Updated: January 17, 2026*

---

## 📋 Table of Contents
1. [Setup Before Testing](#setup-before-testing)
2. [Feature 1: Password Reset System](#feature-1-password-reset-system)
3. [Feature 2: Email Notifications](#feature-2-email-notifications)
4. [Feature 3: Bulk Operations](#feature-3-bulk-operations)
5. [Feature 4: Export Functionality](#feature-4-export-functionality)
6. [Common Issues & Solutions](#common-issues--solutions)

---

## Setup Before Testing

### Prerequisites
1. **Start Database Server:**
   - Open XAMPP Control Panel
   - Start MySQL service
   - Ensure database `drivingapp` exists

2. **Start Laravel Server:**
   ```bash
   php artisan serve
   ```
   Server should be running at: `http://localhost:8000`

3. **Run Migration (if not done yet):**
   ```bash
   php artisan migrate
   ```
   This creates the `password_reset_tokens` table

4. **Access Points:**
   - System Admin: `http://localhost:8000/system-admin`
   - School Admin: `http://localhost:8000/{school-slug}/admin/dashboard`
   - Student/Guest: `http://localhost:8000/{school-slug}/login`

### Test School Slugs
Use these existing schools (check your database):
- `smart-driving-school`
- `alpha-driving-academy`
- Or any school slug in your `schools` table

---

## Feature 1: Password Reset System

### 🎯 What to Test
Password reset functionality for students, instructors, and admins.

### Test Scenario 1: Request Password Reset

**Steps:**
1. Go to login page: `http://localhost:8000/smart-driving-school/login`
2. Click **"Forgot Password?"** link (bottom of form)
3. You should see the "Forgot Password" page
4. Enter test data:
   - **Email:** Use an existing user email from database
     - Student: Check `students` table for email
     - Instructor: Check `instructors` table for email
     - Admin: Check `admins` table for email
   - **Account Type:** Select matching type (Student/Instructor/Admin)
5. Click **"Send Password Reset Link"**

**✅ Expected Results:**
- Success message appears: "Password reset link has been sent to your email!"
- **In DEV MODE:** You'll see the reset link directly in the success message
- Check `storage/logs/laravel.log` - you'll see the email content logged there

**Example Success Message (Dev Mode):**
```
Password reset link has been sent to your email! 
(Dev Mode - Link: http://localhost:8000/smart-driving-school/reset-password/abc123...)
```

### Test Scenario 2: Reset Password Using Link

**Steps:**
1. Copy the reset link from the success message or log file
2. Paste it in your browser (or click it)
3. You should see the "Reset Password" page with:
   - Email field (disabled/pre-filled)
   - New Password field
   - Confirm Password field
   - Password strength indicator
4. Enter new password (minimum 8 characters)
5. Re-enter password in confirmation field
6. Click **"Reset Password"**

**✅ Expected Results:**
- Success message: "Password has been reset successfully!"
- Redirected to login page
- Can now login with NEW password
- Password strength bar changes color as you type:
  - Red = Weak
  - Orange = Medium
  - Green = Strong

### Test Scenario 3: Test Validation

**Test these error cases:**

**A. Invalid Email:**
- Enter non-existent email → "We could not find an account with that email address."

**B. Expired Token:**
- Use a reset link older than 60 minutes → "Password reset token has expired."
- (Note: Hard to test without waiting, can manually change created_at in database)

**C. Password Mismatch:**
- Enter different passwords in "New Password" and "Confirm Password"
- Should show validation error

**D. Weak Password:**
- Try password less than 8 characters → Validation error

### Test Scenario 4: Check Database

**Verify token storage:**
1. After requesting reset, check `password_reset_tokens` table:
   ```sql
   SELECT * FROM password_reset_tokens ORDER BY created_at DESC LIMIT 1;
   ```
2. Should see:
   - Email of user
   - Hashed token
   - User type (student/admin/instructor)
   - School ID
   - Created timestamp

3. After successful reset, token should be deleted from table

---

## Feature 2: Email Notifications

### 🎯 What to Test
Automated email notifications sent when enrollment is approved.

### Test Scenario 1: Enrollment Approval Email

**Steps:**
1. Login as **School Admin**:
   - URL: `http://localhost:8000/smart-driving-school/admin/dashboard`
   - Credentials: Check `admins` table for email/password (or reset if needed)

2. Navigate to **Enrollment Requests**:
   - Click "Enrollments" in sidebar
   - You should see list of enrollment requests

3. Find a **Pending** enrollment request

4. Click **"Approve"** button

**✅ Expected Results:**
- Success message: "Student account activated! Notification email sent."
- Student's role changed from `guest` to `student`
- Check `storage/logs/laravel.log` for email content

**Email Content Preview:**
```
Subject: [School Name] - Enrollment Approved

Dear [Student Name],

Great news! Your enrollment request has been approved by [School Name].

Enrollment Details:
- Course: [Course Title]
- Type: [Practical/Theoretical]
- Duration: [X] hours
- Approved At: [Date & Time]

You can now start scheduling your sessions...

[Login to Your Account Button]
```

### Test Scenario 2: Check Log File

**Steps:**
1. Open: `storage/logs/laravel.log`
2. Scroll to bottom (latest entries)
3. Search for "Enrollment Approved"
4. You should see the full email text

**Example Log Entry:**
```
[2026-01-17 09:45:23] local.INFO: Message-ID: <xxx@localhost>
Hello John Doe,

You are receiving this email because your enrollment has been approved...
```

### Test Scenario 3: Multiple Approvals

**Steps:**
1. Approve 3-5 enrollment requests in a row
2. Check log file - should see 3-5 separate email entries
3. Each email should have correct student name and course details

**✅ Expected Results:**
- All emails logged successfully
- No errors in console/logs
- If email sending fails, should see warning in log but enrollment still approved

---

## Feature 3: Bulk Operations

### 🎯 What to Test
Approve or reject multiple enrollment requests at once.

### Test Scenario 1: Bulk Approve

**Preparation:**
1. Ensure you have at least 3-5 **pending** enrollment requests
2. If not, create guest accounts and submit enrollment requests

**Steps:**
1. Login as **School Admin**
2. Go to **Enrollments** page
3. You should see checkboxes next to each request
4. Select **3 or more pending** enrollment requests (check boxes)
5. Click **"Bulk Approve"** button (should be at top or bottom of table)
6. Confirm the action if prompted

**✅ Expected Results:**
- Success message: "Successfully approved X enrollment request(s)."
- All selected students:
  - Status changed to `approved`
  - Role changed from `guest` to `student`
  - `approved_at` timestamp set
  - `approved_by` set to current admin ID
- Email notification sent to each student (check logs)
- Page refreshes showing updated statuses

### Test Scenario 2: Bulk Reject

**Steps:**
1. Select **2 or more pending** enrollment requests
2. Click **"Bulk Reject"** button
3. Enter rejection reason in modal/form (e.g., "Incomplete requirements")
4. Confirm rejection

**✅ Expected Results:**
- Success message: "Successfully rejected X enrollment request(s)."
- All selected requests:
  - Status changed to `rejected`
  - `rejected_at` timestamp set
  - Remarks field filled with your reason
- Students remain as `guest` role
- No email sent (rejection notification not implemented yet)

### Test Scenario 3: Mixed Status Selection

**Test error handling:**

**A. Select Already Approved:**
- Select an already approved enrollment
- Try to bulk approve → Should be skipped

**B. Select Mix of Pending and Approved:**
- Select both pending and approved enrollments
- Bulk approve → Only pending ones processed
- Success message shows actual count approved

**C. Wrong School:**
- (Admin testing) Enrollments from other schools should be ignored
- System should only process enrollments belonging to logged-in admin's school

### Test Scenario 4: Database Verification

**After bulk operations, verify in database:**
```sql
-- Check recent approvals
SELECT 
    e.id,
    s.name as student_name,
    c.title as course,
    e.status,
    e.approved_at,
    e.approved_by
FROM enrollment_requests e
JOIN students s ON e.learner_id = s.id
JOIN courses c ON e.course_id = c.id
WHERE e.approved_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
ORDER BY e.approved_at DESC;
```

---

## Feature 4: Export Functionality

### 🎯 What to Test
Export student and enrollment data as PDF or Excel files.

### Test Scenario 1: Export Students List (PDF)

**Steps:**
1. Login as **School Admin**
2. Navigate to **Students** page or **User Management**
3. Look for **"Export"** button or dropdown
4. Click **"Export as PDF"** or similar option
5. Or directly visit: `http://localhost:8000/smart-driving-school/admin/exports/students/pdf`

**✅ Expected Results:**
- Browser downloads a PDF file
- Filename: `students-list-YYYY-MM-DD.pdf`
- PDF contains:
  - School name as header
  - "Students List Report" title
  - Generation date/time
  - Total student count
  - Table with columns:
    - # (number)
    - Name
    - Email
    - Contact
    - Status (with colored badge)
    - Active Enrollments count
    - Registration date
  - Footer with school name and copyright
- Data should match what's in database

### Test Scenario 2: Export Students List (Excel)

**Steps:**
1. From Students page, click **"Export as Excel"**
2. Or visit: `http://localhost:8000/smart-driving-school/admin/exports/students/excel`

**✅ Expected Results:**
- Downloads `.xlsx` file
- Filename: `students-YYYY-MM-DD.xlsx`
- Opens in Excel/Google Sheets/LibreOffice
- Contains same data as PDF:
  - Header row with bold formatting
  - All student records
  - Can sort, filter, and analyze data
- Check data accuracy against database

### Test Scenario 3: Export Enrollment Requests (PDF)

**Steps:**
1. Go to **Enrollments** page
2. (Optional) Filter by status: Pending/Approved/Rejected/All
3. Click **"Export as PDF"**
4. Or visit: `http://localhost:8000/smart-driving-school/admin/exports/enrollments/pdf?status=all`

**URL Parameters to test:**
- `?status=pending` - Only pending requests
- `?status=approved` - Only approved requests
- `?status=rejected` - Only rejected requests
- `?status=all` or no parameter - All requests

**✅ Expected Results:**
- Downloads PDF: `enrollment-requests-YYYY-MM-DD.pdf`
- PDF contains:
  - School name
  - "Enrollment Requests Report" title
  - Status filter shown in subtitle
  - Statistics: Total, Pending, Approved, Rejected counts
  - Table with:
    - #, Student Name, Course, Type, Status, Requested Date, Approved Date
  - Status badges colored appropriately:
    - Pending = Yellow
    - Approved = Green
    - Rejected = Red
    - Cancelled = Gray

### Test Scenario 4: Export Student Progress Report (PDF)

**Steps:**
1. Go to **Students** list or **User Management**
2. Click on a specific student's name or "View Details"
3. Look for **"Export Progress Report"** button
4. Or visit: `http://localhost:8000/smart-driving-school/admin/exports/student/{student-id}/progress/pdf`
   - Replace `{student-id}` with actual student ID from database

**✅ Expected Results:**
- Downloads PDF: `progress-[Student-Name]-YYYY-MM-DD.pdf`
- PDF contains:
  - School branding
  - Student name and info
  - List of enrollments with:
    - Course name
    - Course type
    - Status
    - Session completions
  - Total sessions completed
  - Total hours driven
  - Instructor names for each session
  - Professional formatting

### Test Scenario 5: Export Button UI Integration

**Check if export buttons are visible:**

**Option A: Buttons already added**
- Look for "Export" dropdown or buttons on these pages:
  - Students/User Management page
  - Enrollments page
  - Student detail page

**Option B: Buttons NOT visible yet**
- Use direct URLs to test functionality:
  ```
  http://localhost:8000/smart-driving-school/admin/exports/students/pdf
  http://localhost:8000/smart-driving-school/admin/exports/students/excel
  http://localhost:8000/smart-driving-school/admin/exports/enrollments/pdf
  http://localhost:8000/smart-driving-school/admin/exports/student/1/progress/pdf
  ```
- Replace school slug and student ID as needed

**Note to Developer:**
*If buttons are not visible, you'll need to add them to the blade templates later. The export functionality is working, just needs UI buttons.*

### Test Scenario 6: Test Different Data Scenarios

**A. Empty Data:**
- Test school with no students → PDF should show "0 students" gracefully

**B. Large Dataset:**
- Test with 50+ students → Check if PDF pagination works
- Excel should handle all rows

**C. Special Characters:**
- Test student names with accents (José, François, etc.)
- Should render correctly in PDF/Excel

**D. Missing Data:**
- Student with no enrollments → Should show 0 enrollments
- Should not crash or show errors

---

## Common Issues & Solutions

### Issue 1: "Class 'Mail' not found"
**Solution:**
```php
// Make sure controller has this import at the top:
use Illuminate\Support\Facades\Mail;
```

### Issue 2: "Class 'Pdf' not found"
**Solution:**
```php
// Make sure controller has this import:
use Barryvdh\DomPDF\Facade\Pdf;
```

### Issue 3: PDF Download Not Working
**Symptoms:** Clicking export opens blank page or shows HTML code

**Solutions:**
1. Check browser console for errors
2. Try different browser (Chrome recommended)
3. Check Laravel log: `storage/logs/laravel.log` for errors
4. Verify route is accessible:
   ```bash
   php artisan route:list | grep export
   ```

### Issue 4: Email Not in Log File
**Solutions:**
1. Verify `MAIL_MAILER=log` in `.env` file
2. Check `storage/logs/` folder exists and is writable
3. Run: `php artisan config:clear`
4. Try again and immediately check log file

### Issue 5: Password Reset Link Expired Immediately
**Solutions:**
1. Check server time is correct
2. Database `created_at` timezone issues
3. Clear cache: `php artisan cache:clear`

### Issue 6: Bulk Operations Not Working
**Symptoms:** Checkboxes not appearing or bulk buttons missing

**Solutions:**
1. Check if you're on the correct page version
2. Clear browser cache (Ctrl + Shift + R)
3. Inspect page source for `<input type="checkbox">` elements
4. Check browser console for JavaScript errors

### Issue 7: "SQLSTATE[42S02]: Base table or view not found: 'password_reset_tokens'"
**Solution:**
```bash
php artisan migrate
```

### Issue 8: Excel Export Shows Error
**Symptoms:** "Call to undefined method Excel::create"

**Solution:**
The maatwebsite/excel package syntax might need adjustment. For now, focus on testing PDF exports which use dompdf (more stable).

---

## 🧪 Testing Checklist

Use this checklist to track your testing progress:

### Password Reset System
- [ ] Request reset as Student
- [ ] Request reset as Instructor  
- [ ] Request reset as Admin
- [ ] Click reset link and change password
- [ ] Login with new password
- [ ] Test invalid email error
- [ ] Test password confirmation mismatch
- [ ] Verify token in database
- [ ] Verify token deleted after reset

### Email Notifications
- [ ] Approve enrollment request
- [ ] Check success message mentions email
- [ ] Find email in laravel.log file
- [ ] Verify email has correct student name
- [ ] Verify email has correct course details
- [ ] Approve multiple enrollments, check multiple emails

### Bulk Operations
- [ ] Bulk approve 3+ pending requests
- [ ] Verify all approved in database
- [ ] Check email logs for all approved students
- [ ] Bulk reject 2+ pending requests
- [ ] Verify rejection reason saved
- [ ] Test selecting already-approved request
- [ ] Test with mix of statuses

### Export Functionality
- [ ] Export students list as PDF
- [ ] Export students list as Excel
- [ ] Export enrollments as PDF (all)
- [ ] Export enrollments as PDF (pending only)
- [ ] Export enrollments as PDF (approved only)
- [ ] Export student progress report
- [ ] Test with empty data
- [ ] Test with large dataset
- [ ] Verify PDF formatting is professional
- [ ] Verify data accuracy

---

## 📝 Test Report Template

After testing, document your findings:

```
TESTER: [Your Name]
DATE: [Test Date]
SCHOOL USED: [School Slug]

FEATURE: [Password Reset / Email Notifications / Bulk Operations / Export]

TEST RESULTS:
✅ PASSED: [List what worked correctly]

❌ FAILED: [List what didn't work]
- Error message:
- Steps to reproduce:
- Expected behavior:
- Actual behavior:

🐛 BUGS FOUND:
1. [Description]
   - Severity: High/Medium/Low
   - Steps to reproduce:

💡 SUGGESTIONS:
- [Any improvements or features you think would be useful]

SCREENSHOTS:
[Attach relevant screenshots]
```

---

## 🎯 Priority Testing

If you have limited time, test in this order:

**Priority 1 (Critical):**
1. Password Reset - Test all 3 user types
2. Bulk Approve - Test with 3-5 enrollments
3. Export Students PDF - Verify basic functionality

**Priority 2 (Important):**
4. Email Notifications - Verify enrollment approval email
5. Bulk Reject - Test rejection flow
6. Export Enrollments PDF

**Priority 3 (Nice to Have):**
7. Export Student Progress Report
8. Export Students Excel
9. Edge cases and error handling

---

## 📞 Questions or Issues?

If you encounter any problems:

1. **Check Laravel Logs:**
   ```
   storage/logs/laravel.log
   ```

2. **Check Browser Console:**
   - Right-click → Inspect → Console tab
   - Look for JavaScript errors

3. **Check Database:**
   - Use phpMyAdmin or TablePlus
   - Verify data is being saved correctly

4. **Ask the Developer:**
   - Take screenshots of errors
   - Copy exact error messages
   - Note what you were doing when error occurred

---

## 🚀 Ready to Test!

Good luck with testing! Remember:
- Test systematically, one feature at a time
- Document everything you find
- Don't be afraid to break things - that's what testing is for!
- If something doesn't work, it's not your fault - report it!

**Happy Testing! 🎉**
