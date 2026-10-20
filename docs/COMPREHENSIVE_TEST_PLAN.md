# Comprehensive Test Coverage Plan

## Overview
Complete test coverage for **ALL pages, modals, features, buttons, and user flows** in the Driving School Management System. Every test captures screenshots organized in dedicated folders.

## Screenshot Organization
- **Format:** `tests/Browser/screenshots/Test {number} - {Test Name}/{step}-{action}.png`
- **Example:** `Test 001 - Admin Login/01-login-page-loaded.png`

---

## Test Suite Organization

### 1. Authentication & Authorization Tests (Tests 001-020)

#### AdminAuthTest.php
- **Test 001:** Admin Login Success
- **Test 002:** Admin Login with Invalid Credentials
- **Test 003:** Admin Logout
- **Test 004:** Admin Session Persistence
- **Test 005:** Admin Redirect After Login

#### InstructorAuthTest.php
- **Test 006:** Instructor Login Success
- **Test 007:** Instructor Login with Invalid Credentials
- **Test 008:** Instructor Logout
- **Test 009:** Instructor Session Persistence
- **Test 010:** Instructor Redirect After Login

#### StudentAuthTest.php
- **Test 011:** Student Login Success
- **Test 012:** Student Login with Invalid Credentials
- **Test 013:** Student Logout
- **Test 014:** Student Session Persistence
- **Test 015:** Student Redirect After Login

#### GuestRegistrationTest.php
- **Test 016:** Guest Registration Form Display
- **Test 017:** Guest Registration Success
- **Test 018:** Guest Registration with Invalid Data
- **Test 019:** Email Verification Display
- **Test 020:** Email Verification Success

#### PasswordResetTest.php
- **Test 021:** Forgot Password Page Display
- **Test 022:** Password Reset Link Sent
- **Test 023:** Password Reset Form Display
- **Test 024:** Password Reset Success
- **Test 025:** Password Reset with Invalid Token

---

### 2. System Admin Tests (Tests 026-050)

#### SystemAdminAuthTest.php
- **Test 026:** System Admin Login
- **Test 027:** System Admin Logout
- **Test 028:** System Admin Dashboard Access

#### SystemAdminSchoolsTest.php
- **Test 029:** Schools Page Display
- **Test 030:** Create School Modal Opens
- **Test 031:** Create School Form Has All Fields
- **Test 032:** Fill Create School Form
- **Test 033:** Submit Create School Form
- **Test 034:** Edit School Modal Opens
- **Test 035:** Update School Success
- **Test 036:** Toggle School Status
- **Test 037:** Delete School Confirmation Modal
- **Test 038:** Delete School Success

#### SystemAdminAdminsTest.php
- **Test 039:** School Admins Page Display
- **Test 040:** Create Admin Modal Opens
- **Test 041:** Fill Create Admin Form
- **Test 042:** Create Admin Success
- **Test 043:** Edit Admin Modal Opens
- **Test 044:** Update Admin Success
- **Test 045:** Toggle Admin Status
- **Test 046:** Delete Admin Confirmation

#### SystemAdminUsersTest.php
- **Test 047:** Users Management Page Display
- **Test 048:** Filter Users by Role
- **Test 049:** Toggle User Status
- **Test 050:** Delete User Success

#### SystemAdminLogsTest.php
- **Test 051:** System Logs Page Display
- **Test 052:** View Log Details Modal
- **Test 053:** Resolve Log Success
- **Test 054:** Cleanup Logs Confirmation
- **Test 055:** Cleanup Logs Success

---

### 3. Admin Pages Tests (Tests 056-150)

#### AdminDashboardTest.php
- **Test 056:** Admin Dashboard Display
- **Test 057:** Dashboard Statistics Cards
- **Test 058:** Recent Enrollments Widget
- **Test 059:** Upcoming Schedules Widget
- **Test 060:** Quick Actions Buttons

#### AdminUserManagementTest.php
- **Test 061:** User Management Page Display
- **Test 062:** Students Tab Active by Default
- **Test 063:** Switch to Instructors Tab
- **Test 064:** Create Student Modal Opens
- **Test 065:** Create Student Modal Has All Fields
- **Test 066:** Fill Create Student Form
- **Test 067:** Submit Create Student Form
- **Test 068:** Edit Student Modal Opens
- **Test 069:** Update Student Form
- **Test 070:** Update Student Success
- **Test 071:** Toggle Student Status
- **Test 072:** View Student Details Modal
- **Test 073:** Create Instructor Modal Opens
- **Test 074:** Create Instructor Modal Has All Fields
- **Test 075:** Fill Create Instructor Form
- **Test 076:** Submit Create Instructor Form
- **Test 077:** Edit Instructor Modal Opens
- **Test 078:** Update Instructor Success
- **Test 079:** Toggle Instructor Status
- **Test 080:** Toggle Instructor Availability

#### AdminCoursesTest.php
- **Test 081:** Courses Page Display
- **Test 082:** Courses List Shows All Courses
- **Test 083:** Create Course Button Exists
- **Test 084:** Create Course Modal Opens
- **Test 085:** Create Course Modal Has All Fields
- **Test 086:** Fill Create Course Form
- **Test 087:** Submit Create Course Form
- **Test 088:** Edit Course Modal Opens
- **Test 089:** Update Course Form
- **Test 090:** Update Course Success
- **Test 091:** Delete Course Confirmation Modal
- **Test 092:** Delete Course Success
- **Test 093:** View Course Packages
- **Test 094:** Create Package Modal Opens
- **Test 095:** Fill Create Package Form
- **Test 096:** Create Package Success
- **Test 097:** Edit Package Modal Opens
- **Test 098:** Update Package Success
- **Test 099:** Delete Package Confirmation
- **Test 100:** Delete Package Success

#### AdminSchedulesTest.php
- **Test 101:** Schedules Page Display
- **Test 102:** Create Schedule Button Exists
- **Test 103:** Create Schedule Modal Opens
- **Test 104:** Create Schedule Modal Has All Fields
- **Test 105:** Fill Create Schedule Form
- **Test 106:** Submit Create Schedule Form
- **Test 107:** Edit Schedule Modal Opens
- **Test 108:** Update Schedule Form
- **Test 109:** Update Schedule Success
- **Test 110:** Delete Schedule Confirmation Modal
- **Test 111:** Delete Schedule Success
- **Test 112:** View Schedule Details
- **Test 113:** Filter Schedules by Date
- **Test 114:** Filter Schedules by Instructor

#### AdminEnrollmentsTest.php
- **Test 115:** Enrollments Page Display
- **Test 116:** Enrollment Requests Tab
- **Test 117:** Active Enrollments Tab
- **Test 118:** View Enrollment Request Details
- **Test 119:** Approve Enrollment Modal Opens
- **Test 120:** Approve Enrollment Success
- **Test 121:** Reject Enrollment Modal Opens
- **Test 122:** Fill Rejection Reason
- **Test 123:** Reject Enrollment Success
- **Test 124:** Bulk Approve Enrollments
- **Test 125:** Bulk Reject Enrollments
- **Test 126:** Mark Payment Status
- **Test 127:** Mark Theoretical Passed
- **Test 128:** Complete Enrollment
- **Test 129:** Cancel Enrollment

#### AdminRemovalRequestsTest.php
- **Test 130:** Removal Requests Page Display
- **Test 131:** View Removal Request Details
- **Test 132:** Approve Removal Modal Opens
- **Test 133:** Approve Removal Success
- **Test 134:** Reject Removal Modal Opens
- **Test 135:** Fill Rejection Reason
- **Test 136:** Reject Removal Success

#### AdminBookingsTest.php
- **Test 137:** Bookings Page Display
- **Test 138:** Filter Bookings by Status
- **Test 139:** View Booking Details Modal
- **Test 140:** Update Booking Status
- **Test 141:** Delete Booking Confirmation
- **Test 142:** Delete Booking Success

#### AdminPaymentsTest.php
- **Test 143:** Payments Page Display
- **Test 144:** Payment Statistics Widget
- **Test 145:** View Payment Details Modal
- **Test 146:** Filter Payments by Status
- **Test 147:** Export Payments PDF
- **Test 148:** Export Payments Excel

#### AdminSettingsTest.php
- **Test 149:** Settings Page Display
- **Test 150:** Update School Settings Form
- **Test 151:** Update Settings Success
- **Test 152:** Upload School Logo
- **Test 153:** Update Business Hours

#### AdminReportsTest.php
- **Test 154:** Reports Page Display
- **Test 155:** Student Reports Tab
- **Test 156:** Instructor Reports Tab
- **Test 157:** Booking Reports Tab
- **Test 158:** Payment Reports Tab
- **Test 159:** Course Reports Tab
- **Test 160:** Export Students PDF
- **Test 161:** Export Students Excel
- **Test 162:** Export Instructors PDF
- **Test 163:** Export Schedules PDF
- **Test 164:** Export Payments PDF

#### AdminProfileTest.php
- **Test 165:** Admin Profile Page Display
- **Test 166:** Update Profile Form
- **Test 167:** Update Profile Success
- **Test 168:** Upload Profile Picture
- **Test 169:** Change Password Form

#### AdminCourseModulesTest.php
- **Test 170:** Course Modules Page Display
- **Test 171:** Create Module Button
- **Test 172:** Create Module Form Display
- **Test 173:** Fill Create Module Form
- **Test 174:** Submit Create Module
- **Test 175:** Edit Module Page Display
- **Test 176:** Update Module Success
- **Test 177:** Delete Module Confirmation
- **Test 178:** Reorder Modules
- **Test 179:** Duplicate Module

#### AdminModuleLessonsTest.php
- **Test 180:** Module Lessons Page Display
- **Test 181:** Create Lesson Form Display
- **Test 182:** Fill Create Lesson Form
- **Test 183:** Submit Create Lesson
- **Test 184:** Edit Lesson Page Display
- **Test 185:** Update Lesson Success
- **Test 186:** Delete Lesson Confirmation
- **Test 187:** Reorder Lessons

#### AdminTheoreticalTest.php
- **Test 188:** Theoretical Completion Page Display
- **Test 189:** Mark Student as Passed Form
- **Test 190:** Mark Theoretical Passed Success
- **Test 191:** View Passed Students List
- **Test 192:** Revoke Theoretical Pass
- **Test 193:** View Theoretical Stats

#### AdminSessionsTest.php
- **Test 194:** Sessions Page Display
- **Test 195:** View Session Details
- **Test 196:** Delete Session Confirmation
- **Test 197:** View Enrollment Session Stats

---

### 4. Instructor Pages Tests (Tests 198-280)

#### InstructorDashboardTest.php
- **Test 198:** Instructor Dashboard Display
- **Test 199:** Dashboard Statistics
- **Test 200:** Upcoming Lessons Widget
- **Test 201:** Recent Students Widget

#### InstructorScheduleTest.php
- **Test 202:** My Schedule Page Display
- **Test 203:** View Timeslots Calendar
- **Test 204:** Toggle Timeslot Selection
- **Test 205:** Request Timeslot Removal Modal
- **Test 206:** Fill Removal Request Reason
- **Test 207:** Submit Removal Request
- **Test 208:** View Assigned Timeslots
- **Test 209:** Filter Schedule by Date

#### InstructorStudentsTest.php
- **Test 210:** My Students Page Display
- **Test 211:** Students List View
- **Test 212:** View Student Details
- **Test 213:** View Student Progress
- **Test 214:** View Student Schedule

#### InstructorProgressTest.php
- **Test 215:** Progress Page Display
- **Test 216:** Create Progress Button
- **Test 217:** Create Progress Form Display
- **Test 218:** Fill Create Progress Form
- **Test 219:** Submit Create Progress
- **Test 220:** View Progress Details
- **Test 221:** Edit Progress Form
- **Test 222:** Update Progress Success
- **Test 223:** Delete Progress Confirmation

#### InstructorBookingsTest.php
- **Test 224:** View Lesson Details Modal
- **Test 225:** Update Attendance Form
- **Test 226:** Submit Attendance
- **Test 227:** Update Feedback Form
- **Test 228:** Submit Feedback
- **Test 229:** Update Lesson Details

#### InstructorSessionsTest.php
- **Test 230:** Sessions Page Display
- **Test 231:** Create Session Button
- **Test 232:** Create Session Form Display
- **Test 233:** Fill Create Session Form
- **Test 234:** Submit Create Session
- **Test 235:** View Session Details
- **Test 236:** Edit Session Form
- **Test 237:** Update Session Success
- **Test 238:** Delete Session Confirmation
- **Test 239:** View Enrollment Stats

#### InstructorTheoreticalTest.php
- **Test 240:** Theoretical Page Display
- **Test 241:** Mark Student as Passed Form
- **Test 242:** Mark Theoretical Passed Success
- **Test 243:** View Passed Students List

#### InstructorCourseModulesTest.php
- **Test 244:** Course Modules Page Display
- **Test 245:** View Module Details
- **Test 246:** View Module Lessons List
- **Test 247:** View Lesson Details

#### InstructorReportsTest.php
- **Test 248:** Reports Page Display
- **Test 249:** Performance Statistics
- **Test 250:** Student Progress Reports

#### InstructorGradesTest.php
- **Test 251:** Grades Page Display
- **Test 252:** View Student Grades
- **Test 253:** Update Grade Form
- **Test 254:** Submit Grade Update

#### InstructorProfileTest.php
- **Test 255:** Instructor Profile Page Display
- **Test 256:** Update Profile Form
- **Test 257:** Update Profile Success
- **Test 258:** Upload Profile Picture
- **Test 259:** Update Availability Settings

---

### 5. Student Pages Tests (Tests 260-340)

#### StudentDashboardTest.php
- **Test 260:** Student Dashboard Display
- **Test 261:** Dashboard Statistics
- **Test 262:** Course Progress Widget
- **Test 263:** Upcoming Lessons Widget
- **Test 264:** Recent Payments Widget

#### StudentCoursesTest.php
- **Test 265:** Courses Page Display
- **Test 266:** View Available Courses
- **Test 267:** View Course Details
- **Test 268:** View Course Packages
- **Test 269:** View Course Modules

#### StudentMyCourseTest.php
- **Test 270:** My Course Page Display
- **Test 271:** View Current Enrollment
- **Test 272:** View Course Progress
- **Test 273:** View Completed Modules
- **Test 274:** View Pending Modules

#### StudentCourseModulesTest.php
- **Test 275:** Course Modules Page Display
- **Test 276:** View Module Details
- **Test 277:** View Module Lessons List
- **Test 278:** View Lesson Content
- **Test 279:** Navigate Between Lessons
- **Test 280:** Mark Lesson as Complete

#### StudentScheduleTest.php
- **Test 281:** Schedule Page Display
- **Test 282:** View Available Timeslots
- **Test 283:** Book Timeslot Modal Opens
- **Test 284:** Fill Booking Form
- **Test 285:** Submit Booking
- **Test 286:** View My Bookings
- **Test 287:** Confirm Booking
- **Test 288:** Remove from Queue
- **Test 289:** Filter Schedule by Instructor
- **Test 290:** Filter Schedule by Date

#### StudentProgressTest.php
- **Test 291:** Progress Page Display
- **Test 292:** View Progress Timeline
- **Test 293:** View Completed Sessions
- **Test 294:** View Theoretical Status
- **Test 295:** View Overall Progress Percentage

#### StudentPaymentsTest.php
- **Test 296:** Payments Page Display
- **Test 297:** View Payment History
- **Test 298:** View Payment Details
- **Test 299:** View Payment Status
- **Test 300:** Download Payment Receipt

#### StudentProfileTest.php
- **Test 301:** Student Profile Page Display
- **Test 302:** Update Profile Form
- **Test 303:** Update Profile Success
- **Test 304:** Upload Profile Picture
- **Test 305:** Change Password Form
- **Test 306:** Change Password Success

---

### 6. Guest User Tests (Tests 307-330)

#### GuestDashboardTest.php
- **Test 307:** Guest Dashboard Display
- **Test 308:** View Welcome Message
- **Test 309:** View Available Courses

#### GuestCoursesTest.php
- **Test 310:** Guest Courses Page Display
- **Test 311:** View Course List
- **Test 312:** View Course Details
- **Test 313:** Enroll Button Exists

#### GuestEnrollmentTest.php
- **Test 314:** Enroll in Course Modal Opens
- **Test 315:** Select Course Package
- **Test 316:** Submit Enrollment Request
- **Test 317:** Enrollment Request Success Message

#### GuestEnrollmentRequestsTest.php
- **Test 318:** Enrollment Requests Page Display
- **Test 319:** View Pending Requests
- **Test 320:** View Request Status
- **Test 321:** View Approval Status
- **Test 322:** View Rejection Reason

---

### 7. Complete User Journeys (Tests 323-370)

#### CompleteStudentJourneyTest.php
- **Test 323:** Complete Registration Flow
- **Test 324:** Email Verification Flow
- **Test 325:** First Login as Student
- **Test 326:** View Available Courses
- **Test 327:** Enroll in Course
- **Test 328:** Wait for Admin Approval
- **Test 329:** Book First Lesson
- **Test 330:** Attend Lesson
- **Test 331:** View Progress After Lesson
- **Test 332:** Complete All Lessons
- **Test 333:** Pass Theoretical
- **Test 334:** Complete Course

#### CompleteInstructorJourneyTest.php
- **Test 335:** Admin Creates Instructor
- **Test 336:** Instructor First Login
- **Test 337:** View Assigned Timeslots
- **Test 338:** Select Available Timeslots
- **Test 339:** View Assigned Students
- **Test 340:** Conduct Lesson
- **Test 341:** Mark Attendance
- **Test 342:** Submit Feedback
- **Test 343:** Log Session Completion
- **Test 344:** View Performance Reports

#### CompleteAdminJourneyTest.php
- **Test 345:** Admin Login
- **Test 346:** Create New Course
- **Test 347:** Add Course Packages
- **Test 348:** Create Course Modules
- **Test 349:** Add Module Lessons
- **Test 350:** Create Instructor
- **Test 351:** Create Schedule Timeslots
- **Test 352:** Approve Enrollment Request
- **Test 353:** Monitor Student Progress
- **Test 354:** View Reports
- **Test 355:** Export Data

#### BookingCompleteFlowTest.php
- **Test 356:** Student Views Schedule
- **Test 357:** Student Books Timeslot
- **Test 358:** Admin Views Booking
- **Test 359:** Instructor Views Upcoming Lesson
- **Test 360:** Instructor Conducts Lesson
- **Test 361:** Instructor Marks Attendance
- **Test 362:** Student Views Updated Progress
- **Test 363:** Admin Views Booking Report

#### PaymentCompleteFlowTest.php
- **Test 364:** Student Enrolls in Course
- **Test 365:** Admin Views Payment Pending
- **Test 366:** Admin Marks Payment as Paid
- **Test 367:** Student Views Payment Receipt
- **Test 368:** Admin Views Payment Statistics
- **Test 369:** Admin Exports Payment Report

---

### 8. UI Components & Interactions (Tests 371-400)

#### NavigationTest.php
- **Test 371:** Admin Navigation Menu Display
- **Test 372:** Navigate All Admin Pages
- **Test 373:** Instructor Navigation Menu Display
- **Test 374:** Navigate All Instructor Pages
- **Test 375:** Student Navigation Menu Display
- **Test 376:** Navigate All Student Pages

#### ModalsTest.php
- **Test 377:** All Modals Open Correctly
- **Test 378:** All Modals Close with X Button
- **Test 379:** All Modals Close with Cancel Button
- **Test 380:** Modal Backdrop Click Closes Modal

#### FormsTest.php
- **Test 381:** All Form Fields Accept Input
- **Test 382:** Required Field Validation
- **Test 383:** Email Field Validation
- **Test 384:** Phone Number Validation
- **Test 385:** Date Picker Functionality
- **Test 386:** File Upload Functionality

#### ButtonsTest.php
- **Test 387:** All Primary Buttons Clickable
- **Test 388:** All Secondary Buttons Clickable
- **Test 389:** All Delete Buttons Show Confirmation
- **Test 390:** All Submit Buttons Trigger Action

#### TablesTest.php
- **Test 391:** All Tables Display Data
- **Test 392:** Table Sorting Functionality
- **Test 393:** Table Pagination Works
- **Test 394:** Table Search Filters Work

#### FiltersTest.php
- **Test 395:** Date Range Filter Works
- **Test 396:** Status Filter Works
- **Test 397:** Role Filter Works
- **Test 398:** Search Filter Works

#### ResponsivenessTest.php
- **Test 399:** Sidebar Collapses on Mobile
- **Test 400:** Mobile Menu Works

---

## Coverage Summary

- **Total Tests:** 400+
- **Authentication Tests:** 25
- **System Admin Tests:** 30
- **Admin Tests:** 142
- **Instructor Tests:** 62
- **Student Tests:** 81
- **Guest Tests:** 16
- **Complete Journeys:** 48
- **UI Components:** 30

## Screenshot Organization Examples

### Test 001 - Admin Login
```
Test 001 - Admin Login/
├── 01-login-page-loaded.png
├── 02-email-field-filled.png
├── 03-password-field-filled.png
├── 04-login-button-clicked.png
└── 05-dashboard-loaded.png
```

### Test 065 - Fill Create Student Form
```
Test 065 - Fill Create Student Form/
├── 01-user-management-page.png
├── 02-create-student-modal-opened.png
├── 03-first-name-filled.png
├── 04-last-name-filled.png
├── 05-email-filled.png
├── 06-phone-filled.png
├── 07-birthdate-selected.png
├── 08-address-filled.png
├── 09-form-complete.png
└── 10-submit-clicked.png
```

### Test 323 - Complete Registration Flow
```
Test 323 - Complete Registration Flow/
├── 01-welcome-page.png
├── 02-school-selected.png
├── 03-register-button-clicked.png
├── 04-registration-form-loaded.png
├── 05-personal-info-filled.png
├── 06-contact-info-filled.png
├── 07-password-entered.png
├── 08-form-submitted.png
├── 09-verification-page.png
├── 10-verification-code-entered.png
├── 11-email-verified.png
└── 12-guest-dashboard-loaded.png
```

## Benefits

1. **Complete Coverage:** Every page, button, modal, and feature is tested
2. **Visual Documentation:** Screenshots show exactly what happened at each step
3. **Error Detection:** Easy to identify where, what, and when errors occur
4. **Debugging:** Visual proof of test execution makes debugging faster
5. **Regression Testing:** Detect UI changes and regressions immediately
6. **Documentation:** Screenshots serve as visual documentation for stakeholders
7. **Organized Structure:** Test folders make it easy to find specific test results

## Running Tests

```powershell
# Run all tests
php artisan dusk

# Run specific test file
php artisan dusk tests/Browser/AdminAuthTest.php

# Run specific test
php artisan dusk --filter=test_admin_can_login

# Run tests by group
php artisan dusk --group=admin
php artisan dusk --group=student
php artisan dusk --group=instructor
```
