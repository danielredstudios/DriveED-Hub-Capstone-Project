# Screenshot Documentation for Dusk Tests

## Overview
All Dusk browser tests now automatically capture screenshots after **every action** to provide complete visual documentation of the test execution. This allows you to see exactly what happened at each step, whether the test passes or fails.

## Screenshot Locations
All screenshots are saved to: `tests/Browser/screenshots/`

## Naming Convention
Screenshots follow a clear naming pattern:
- **Format:** `[test-area]-[step-number]-[action-description].png`
- **Examples:**
  - `admin-login-01-page-loaded.png`
  - `admin-login-02-credentials-filled.png`
  - `admin-login-03-dashboard-loaded.png`
  - `student-modal-01-page-loaded.png`
  - `student-modal-02-modal-opened.png`

## Screenshot Coverage

### AdminModalTest.php - Modal Tests
Each test captures screenshots at key points:

#### Login Tests
- `admin-login-01-page-loaded` - Initial login page
- `admin-login-02-credentials-filled` - After entering credentials
- `admin-login-03-dashboard-loaded` - After successful login
- `instructor-login-01-page-loaded` - Instructor login page
- `instructor-login-02-credentials-filled` - After entering credentials
- `instructor-login-03-dashboard-loaded` - Instructor dashboard

#### User Management Tests
- `user-mgmt-01-page-loaded` - User management page
- `student-modal-01-page-loaded` - Before opening modal
- `student-modal-02-modal-opened` - Student creation modal opened
- `student-fields-01-page-loaded` - Page before checking fields
- `student-fields-02-modal-with-fields` - Modal showing all fields
- `student-fill-01-page-loaded` - Before filling form
- `student-fill-02-modal-opened` - Modal opened
- `student-fill-03-form-filled` - After filling student form
- `instructor-modal-01-page-loaded` - Before instructor modal
- `instructor-modal-02-tab-clicked` - After clicking instructor tab
- `instructor-modal-03-modal-opened` - Instructor modal opened

#### Course Management Tests
- `courses-01-page-loaded` - Courses page
- `course-modal-01-page-loaded` - Before opening course modal
- `course-modal-02-modal-opened` - Course modal opened

#### Schedule Management Tests
- `schedules-01-page-loaded` - Schedules page
- `schedule-modal-01-page-loaded` - Before opening schedule modal
- `schedule-modal-02-modal-opened` - Schedule modal opened

#### Enrollment Management Tests
- `enrollments-01-page-loaded` - Enrollment requests page
- `enrollment-reject-modal-01-page-with-modal` - Page with reject modal

#### Removal Requests Tests
- `removal-01-page-loaded` - Removal requests page
- `removal-approve-modal-01-page-with-modal` - Page with approve modal
- `removal-reject-modal-01-page-with-modal` - Page with reject modal

#### Settings Tests
- `settings-01-page-loaded` - Settings page

#### Instructor Pages Tests
- `instructor-dashboard-01-page-loaded` - Instructor dashboard
- `instructor-timeslots-01-page-loaded` - Instructor timeslots page

#### Navigation Tests
- `nav-01-dashboard` - Dashboard
- `nav-02-user-management` - User management page
- `nav-03-courses` - Courses page
- `nav-04-schedules` - Schedules page
- `nav-05-enrollments` - Enrollments page
- `nav-06-settings` - Settings page

### UIComponentsTest.php - UI Component Tests
Each UI test captures relevant screenshots:

#### Login Page Tests
- `ui-login-page-display` - Full login page
- `ui-login-fields` - Email and password fields
- `ui-login-register-link` - Register link visible
- `ui-login-forgot-password` - Forgot password link
- `ui-login-remember-me` - Remember me checkbox
- `ui-login-branding` - School branding
- `ui-login-button` - Login button

#### Registration Page Tests
- `ui-registration-page` - Registration form
- `ui-registration-fields` - All registration fields
- `ui-registration-login-link` - Back to login link

#### Forgot Password Tests
- `ui-forgot-password-page` - Forgot password page
- `ui-forgot-password-back-link` - Back to login link

#### Navigation Tests
- `ui-nav-01-login-page` - Login page before navigation
- `ui-nav-02-register-page` - Registration page after click
- `ui-nav-03-login-page-before` - Login page before forgot password
- `ui-nav-04-forgot-password-page` - Forgot password page

#### Form Input Tests
- `ui-input-01-before-email` - Before typing email
- `ui-input-02-after-email` - After typing email
- `ui-input-03-before-password` - Before typing password
- `ui-input-04-after-password` - After typing password
- `ui-input-05-before-checkbox` - Before checking remember me
- `ui-input-06-after-checkbox` - After checking remember me

## Helper Methods
The login helper methods also capture screenshots:
- Login page loaded
- Credentials entered
- User logged in (admin or instructor)

## Failure Screenshots
When a test fails, Dusk automatically captures:
- `failure-[TestClass]_[test_method_name]-0.png` - Screenshot at point of failure

## Benefits

### For Debugging
- See exactly what the page looked like when a test failed
- Identify visual issues or missing elements
- Verify that modals opened correctly
- Check form field visibility and states

### For Documentation
- Visual proof of test execution
- Easy to review what each test actually does
- Can be shared with team members or stakeholders
- Helps non-technical users understand test coverage

### For Regression Testing
- Compare screenshots over time to detect unintended visual changes
- Verify UI consistency across different test runs
- Spot layout or styling regressions

## Maintenance

### Clearing Old Screenshots
To remove all old screenshots before running new tests:
```powershell
Remove-Item "tests\Browser\screenshots\*.png" -Force
```

### Storage Considerations
- Screenshots are NOT committed to Git (in .gitignore)
- Average screenshot size: ~50-200 KB
- Full test suite (~50 tests): ~5-10 MB total
- Recommended: Clear screenshots periodically or after major test runs

## Running Tests with Screenshots

### Run All Tests
```powershell
php artisan dusk
```

### Run Specific Test File
```powershell
php artisan dusk tests/Browser/AdminModalTest.php
php artisan dusk tests/Browser/UIComponentsTest.php
```

### Run Single Test
```powershell
php artisan dusk --filter=test_admin_can_login
```

## Tips

1. **Review After Failures**: Always check the failure screenshots to understand what went wrong
2. **Before/After Comparison**: The numbered screenshots let you see the progression of actions
3. **Modal Verification**: Screenshots clearly show when modals open/close
4. **Form States**: Easy to verify that form fields are filled correctly
5. **Navigation Flow**: See the complete user journey through multiple pages

## Example Test Flow with Screenshots

For the `test_can_fill_create_student_form` test:

1. **student-fill-01-page-loaded.png**
   - Shows the user management page loaded
   - Verify page structure and layout

2. **student-fill-02-modal-opened.png**
   - Shows the student creation modal is open
   - Verify modal is visible and properly displayed

3. **student-fill-03-form-filled.png**
   - Shows all form fields filled with test data
   - Verify data entry was successful

This visual documentation makes it easy to:
- Verify the test is working correctly
- Debug issues quickly
- Share results with team members
- Document the application's behavior
