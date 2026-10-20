# Security & Conflict Prevention Features

## Overview
This document describes the security enhancements and scheduling conflict prevention features implemented in the Driving School Management System.

## 1. Security Enhancements

### 1.1 Strong Password Requirements
All passwords must now meet the following criteria:
- Minimum 8 characters
- At least one uppercase letter (A-Z)
- At least one number (0-9)
- At least one special character (!@#$%^&*(),.?":{}|<>)

**Implementation:**
- Custom validation rule: `App\Rules\StrongPassword`
- Applied to:
  - Guest registration (`GuestController`)
  - Student password changes (`StudentController`)
  - Admin creation (`SystemAdminController`)
  - School admin creation

**Files Modified:**
- `app/Rules/StrongPassword.php` (NEW)
- `app/Http/Controllers/GuestController.php`
- `app/Http/Controllers/StudentController.php`
- `app/Http/Controllers/SystemAdminController.php`

### 1.2 Account Lockout After Failed Login Attempts
Accounts are automatically locked after 5 failed login attempts for 30 minutes.

**Features:**
- Tracks failed login attempts for admins, instructors, and students
- Locks account for 30 minutes after 5 failed attempts
- Shows remaining attempts before lockout
- Displays lockout message with remaining time
- Automatically resets failed attempts counter on successful login
- Logs all failed login attempts and lockouts via SystemLog

**Database Changes:**
- Added to `admins`, `instructors`, `students` tables:
  - `failed_login_attempts` (integer, default 0)
  - `locked_until` (timestamp, nullable)
  - `last_login_at` (timestamp, nullable)

**Implementation:**
- Migration: `2026_01_25_095343_add_security_columns_to_auth_tables.php`
- Updated: `app/Http/Controllers/AuthController.php`

**Files Modified:**
- `database/migrations/2026_01_25_095343_add_security_columns_to_auth_tables.php` (NEW)
- `app/Http/Controllers/AuthController.php`

### 1.3 Session Timeout Configuration
While the actual session timeout is configured in `config/session.php`, the system is now ready for additional session management features:

**Current Configuration:**
- Session lifetime: 120 minutes (2 hours) - configurable in `.env` as `SESSION_LIFETIME`
- Session driver: Database (sessions stored in database for security)

**Recommendations for Production:**
- Enable `expire_on_close` in session config for sensitive systems
- Consider implementing session activity tracking
- Add inactivity warning modal (5 minutes before expiration)

## 2. Scheduling Conflict Prevention

### 2.1 Conflict Detection Service
A new service checks for instructor scheduling conflicts to prevent double-booking.

**Features:**
- Checks if instructor is available for a specific time slot
- Detects overlapping sessions
- Provides list of conflicting sessions with details
- Suggests alternative time slots
- Supports excluding current session (for updates)
- Handles cancelled sessions (ignored in conflict checks)

**Implementation:**
- Service: `App\Services\SchedulingConflictService`
- Methods:
  - `checkInstructorAvailability()` - Check for conflicts
  - `getAvailableTimeSlots()` - Get available time slots for a date
  - `suggestAlternativeTimeSlots()` - Suggest alternatives when conflicts occur
  - `checkBulkConflicts()` - Check multiple sessions at once

**Files Created:**
- `app/Services/SchedulingConflictService.php` (NEW)

### 2.2 Session Completion Enhancements
Session completions now track start and end times for accurate conflict detection.

**Database Changes:**
- Added to `session_completions` table:
  - `start_time` (time, nullable)
  - `end_time` (time, nullable)
  - `status` (enum: scheduled, completed, cancelled, default: completed)

**Migration:**
- `2026_01_25_095551_add_time_columns_to_session_completions.php`

**Files Modified:**
- `database/migrations/2026_01_25_095551_add_time_columns_to_session_completions.php` (NEW)
- `app/Models/SessionCompletion.php`

### 2.3 Automatic Conflict Checking
The system automatically checks for conflicts when creating or updating sessions.

**Features:**
- Validates instructor availability before saving session
- Calculates end time based on hours_completed if not provided
- Shows detailed conflict message with conflicting session times
- Returns conflicts list for display to user
- Excludes current session when updating (prevents false positives)

**Implementation:**
- Updated: `SessionCompletionController@store()`
- Updated: `SessionCompletionController@update()`
- Updated form request: `StoreSessionCompletionRequest`

**Files Modified:**
- `app/Http/Controllers/SessionCompletionController.php`
- `app/Http/Requests/StoreSessionCompletionRequest.php`

## 3. Testing Instructions

### 3.1 Testing Password Strength
1. Try to register/create account with weak password: "password" → Should fail
2. Try password without uppercase: "password123!" → Should fail
3. Try password without number: "Password!" → Should fail
4. Try password without special char: "Password123" → Should fail
5. Try strong password: "Password123!" → Should succeed

### 3.2 Testing Account Lockout
1. Go to login page
2. Enter valid email with wrong password 5 times
3. On 1st-4th attempt: See "X attempts remaining" message
4. On 5th attempt: Account locked for 30 minutes
5. Try to login again: See "locked for X minutes" message
6. Wait 30 minutes OR manually update `locked_until` to past in database
7. Login with correct password: Success, failed attempts reset to 0

### 3.3 Testing Conflict Detection
1. Create a session for an instructor:
   - Date: 2026-01-25
   - Start time: 14:00
   - End time: 16:00
2. Try to create another session for same instructor:
   - Date: 2026-01-25
   - Start time: 15:00 (overlaps with existing session)
   - End time: 17:00
3. Should see error: "Scheduling Conflict: Instructor has conflicting sessions at: 2:00 PM - 4:00 PM"
4. Create session for different time (no overlap) → Should succeed

## 4. Security Logging

All security events are logged via `SystemLog::logWarning()` including:
- Failed login attempts (with attempt count)
- Account lockouts (with locked_until timestamp)
- Successful logins (with last_login_at update)
- Login attempts on locked accounts
- Login attempts on deactivated accounts

**Log Actions:**
- `failed_login` - Incorrect password attempt
- `locked_login_attempt` - Login attempt on locked account
- `blocked_login` - Login attempt on deactivated account
- `school_admin_login` - Successful admin login
- `instructor_login` - Successful instructor login
- `student_login` / `guest_login` - Successful student/guest login

## 5. Database Schema Updates

### Security Columns
```sql
-- Added to admins, instructors, students tables
ALTER TABLE admins ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE admins ADD COLUMN locked_until TIMESTAMP NULL;
ALTER TABLE admins ADD COLUMN last_login_at TIMESTAMP NULL;

ALTER TABLE instructors ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE instructors ADD COLUMN locked_until TIMESTAMP NULL;
ALTER TABLE instructors ADD COLUMN last_login_at TIMESTAMP NULL;

ALTER TABLE students ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE students ADD COLUMN locked_until TIMESTAMP NULL;
ALTER TABLE students ADD COLUMN last_login_at TIMESTAMP NULL;
```

### Session Completion Columns
```sql
-- Added to session_completions table
ALTER TABLE session_completions ADD COLUMN start_time TIME NULL;
ALTER TABLE session_completions ADD COLUMN end_time TIME NULL;
ALTER TABLE session_completions ADD COLUMN status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'completed';
```

## 6. Configuration

### Environment Variables
No new environment variables required, but you can customize:
```env
# Session timeout (minutes)
SESSION_LIFETIME=120

# Session driver (recommended: database for security)
SESSION_DRIVER=database
```

### Customizable Constants (in code)
You can modify these in the respective files:

**Account Lockout Settings** (`AuthController.php`):
- Max failed attempts: `5` (line with `$failedAttempts >= 5`)
- Lockout duration: `30 minutes` (line with `now()->addMinutes(30)`)

**Working Hours** (`SchedulingConflictService.php`):
- Work start: `08:00:00` (line with `Carbon::parse($date . ' 08:00:00')`)
- Work end: `18:00:00` (line with `Carbon::parse($date . ' 18:00:00')`)

**Default Session Duration** (`SchedulingConflictService.php`):
- Default duration: `60 minutes` (parameter in `getAvailableTimeSlots()`)

## 7. Future Enhancements

### Potential Improvements:
1. **Password History**: Prevent reusing last 5 passwords
2. **Two-Factor Authentication (2FA)**: SMS or email verification codes
3. **IP-based Rate Limiting**: Limit login attempts per IP address
4. **Password Expiration**: Force password change every 90 days
5. **Session Activity Tracking**: Track user actions during session
6. **Vehicle Conflict Detection**: Prevent double-booking vehicles
7. **Email Notifications**: Alert users when account is locked
8. **Admin Unlock**: Allow admins to manually unlock accounts
9. **Conflict Resolution UI**: Show calendar view with suggested times
10. **Bulk Schedule Validation**: Check multiple sessions before creating

## 8. Maintenance

### Cleaning Up Locked Accounts
Locked accounts automatically unlock after 30 minutes. If you need to manually unlock:

```sql
-- Unlock all accounts
UPDATE admins SET locked_until = NULL, failed_login_attempts = 0;
UPDATE instructors SET locked_until = NULL, failed_login_attempts = 0;
UPDATE students SET locked_until = NULL, failed_login_attempts = 0;

-- Unlock specific account
UPDATE students SET locked_until = NULL, failed_login_attempts = 0 WHERE email = 'student@example.com';
```

### Monitoring Failed Logins
Query SystemLog for failed login patterns:

```php
// In Tinker or controller
$failedLogins = SystemLog::where('action', 'failed_login')
    ->where('created_at', '>', now()->subDay())
    ->get();
```

## 9. Implementation Checklist

- [x] Create StrongPassword validation rule
- [x] Add security columns to auth tables (failed_login_attempts, locked_until, last_login_at)
- [x] Implement account lockout in AuthController
- [x] Update password validation in all controllers
- [x] Create SchedulingConflictService
- [x] Add start_time, end_time, status to session_completions
- [x] Integrate conflict checking in SessionCompletionController
- [x] Update SessionCompletion model fillable fields
- [x] Update StoreSessionCompletionRequest validation
- [x] Document all features
- [ ] Update UI forms to include start_time/end_time fields
- [ ] Add password strength indicator to registration forms
- [ ] Display conflict details in session creation UI
- [ ] Add session timeout warning modal
- [ ] Test all features thoroughly

## 10. Deployment Notes

When deploying to production:
1. Run migrations: `php artisan migrate`
2. Clear cache: `php artisan cache:clear`
3. Clear config: `php artisan config:clear`
4. Test password validation on registration forms
5. Test account lockout with test account
6. Verify SystemLog entries are being created
7. Check conflict detection with overlapping sessions
8. Monitor failed login attempts in first week
9. Consider adding rate limiting to login routes
10. Ensure session table is cleaned regularly (Laravel's default scheduler handles this)

---

**Last Updated:** January 25, 2026
**Version:** 1.0
**Status:** ✅ Implemented and Ready for Testing
