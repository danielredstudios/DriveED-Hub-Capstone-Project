# System Error Logging and Monitoring

This document explains the comprehensive error logging system implemented in the Driving School Management System.

## Overview

The system now includes a robust error logging and monitoring system that:
- **Automatically logs all errors** (database, validation, authentication, file upload, etc.)
- **Notifies school admins** when errors occur in their school
- **Notifies system administrators** of all critical errors
- **Provides detailed error information** for debugging
- **Allows error resolution tracking** by system admins

## System Architecture

### Database Table: `system_logs`

All errors and system events are stored in the `system_logs` table with the following information:
- Error level (emergency, alert, critical, error, warning, notice, info, debug)
- Category (database, validation, authentication, authorization, file_upload, email, payment, api, system, other)
- School and user information
- Full exception details and stack trace
- Request context (URL, IP, user agent, etc.)
- Resolution tracking

### Model: `SystemLog`

Location: `app/Models/SystemLog.php`

**Key Methods:**
```php
// Log an error
SystemLog::logError($message, $category, $exception, $context, $schoolId, $action);

// Log a critical error
SystemLog::logCritical($message, $category, $exception, $context, $schoolId, $action);

// Log a warning
SystemLog::logWarning($message, $category, $context, $schoolId, $action);

// Log info
SystemLog::logInfo($message, $category, $context, $schoolId, $action);

// Mark as resolved
$log->resolve('Fixed by restarting the service');
```

## Accessing the System Admin Panel

### URL
```
http://localhost:8000/system-admin/logs
```

### Available Routes

1. **Logs List**: `/system-admin/logs`
   - View all system logs with filtering
   - Statistics dashboard
   - Filter by level, category, school, date, status

2. **Log Detail**: `/system-admin/logs/{id}`
   - Detailed view of a specific log
   - Full stack trace
   - Resolution form

3. **Resolve Log**: `POST /system-admin/logs/{id}/resolve`
   - Mark a log as resolved
   - Add resolution notes

4. **Cleanup Logs**: `POST /system-admin/logs/cleanup`
   - Delete old resolved logs
   - Specify retention period (30-365 days)

## Features

### 1. Automatic Error Logging

All controller methods are wrapped with try-catch blocks that automatically log errors:

```php
try {
    // Your code here
    $student->update($data);
    
} catch (\Exception $e) {
    SystemLog::logError(
        'Failed to update student',
        'database',
        $e,
        ['student_id' => $student->id],
        $school->id,
        'update_student'
    );
    
    return back()->with('error', 'Operation failed. Administrator notified.');
}
```

### 2. Error Categories

- **database**: Database query errors, connection issues
- **validation**: Form validation failures
- **authentication**: Login failures, session issues
- **authorization**: Permission denied errors
- **file_upload**: File upload/storage errors
- **email**: Email sending failures
- **payment**: Payment processing errors
- **api**: External API errors
- **system**: System-level errors
- **other**: Uncategorized errors

### 3. Error Levels

From most to least severe:
1. **emergency**: System is unusable
2. **alert**: Action must be taken immediately
3. **critical**: Critical conditions
4. **error**: Error conditions
5. **warning**: Warning conditions
6. **notice**: Normal but significant condition
7. **info**: Informational messages
8. **debug**: Debug-level messages

### 4. Filtering and Search

The system admin panel allows filtering by:
- Error level
- Category
- School
- Resolved status
- Date range

### 5. Error Resolution Workflow

1. System admin views unresolved errors
2. Investigates the issue using:
   - Error message
   - Stack trace
   - Request context
   - User information
3. Fixes the issue
4. Marks the log as resolved with notes

## User Experience

### For School Admins

When an error occurs:
1. User sees friendly error message: *"Operation failed. The system administrator has been notified."*
2. User's input is preserved (withInput())
3. Admin can retry the operation after fixing input issues
4. System admin is notified automatically

### For System Administrators

1. **Dashboard** shows error statistics:
   - Critical errors count
   - Unresolved issues
   - Today's logs
   - Weekly trends

2. **Detailed Logs** include:
   - Full error message
   - Exception class
   - Complete stack trace
   - Request URL and method
   - User and school information
   - IP address and user agent
   - Custom context data

3. **Resolution Tracking**:
   - Mark errors as resolved
   - Add resolution notes
   - Track who resolved each issue
   - View resolution history

## Implementation Examples

### Example 1: Dashboard Error Handling
```php
public function dashboard(School $school)
{
    try {
        $totalStudents = Student::where('school_id', $school->id)->count();
        // ... more code
        
        return view($school->resolveView('admin.dashboard'), $data);
    } catch (\Exception $e) {
        SystemLog::logError(
            'Failed to load admin dashboard',
            'database',
            $e,
            ['school_id' => $school->id],
            $school->id,
            'view_dashboard'
        );
        
        return back()->with('error', 'Unable to load dashboard. Administrator notified.');
    }
}
```

### Example 2: Update Settings with Transaction

```php
public function updateSettings(Request $request, School $school)
{
    try {
        $validated = $request->validate([...]);
        
        DB::beginTransaction();
        
        $school->update(['settings' => $validated]);
        SchoolSetting::updateOrCreate([...]);
        
        DB::commit();
        
        SystemLog::logInfo(
            'School settings updated successfully',
            'system',
            ['school_id' => $school->id],
            $school->id,
            'update_settings'
        );
        
        return back()->with('success', 'Settings updated!');
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        SystemLog::logError(
            'Failed to update school settings',
            'database',
            $e,
            ['school_id' => $school->id],
            $school->id,
            'update_settings'
        );
        
        return back()->with('error', 'Settings update failed. Administrator notified.');
    }
}
```

## Testing the System

### 1. Test Database Error Logging

Access the logs page:
```
http://localhost:8000/system-admin/logs
```

You should see:
- Statistics cards showing counts
- Filter options
- List of all logged errors

### 2. Test Error Detail View

Click "View" on any log entry to see:
- Full error message
- Stack trace
- Request context
- Resolution form

### 3. Test Error Resolution

1. Click a log detail
2. Add resolution notes
3. Click "Mark as Resolved"
4. Verify status changes to "Resolved"

### 4. Trigger Test Errors

You can test the system by:
1. Entering invalid data in forms
2. Accessing non-existent resources
3. Simulating database connection issues

## Maintenance

### Cleaning Up Old Logs

To prevent database bloat:

1. Go to `/system-admin/logs`
2. Use the cleanup feature (when implemented)
3. Specify retention period (e.g., 90 days)
4. Only resolved logs older than the period are deleted

### Recommended Retention

- **Critical/Emergency**: Keep indefinitely
- **Error**: Keep 180 days
- **Warning**: Keep 90 days
- **Info/Debug**: Keep 30 days

## Security Considerations

### Access Control

**TODO**: Add authentication middleware to system admin routes:
```php
Route::prefix('system-admin')
    ->middleware(['auth', 'system.admin'])  // Add this
    ->name('system-admin.')
    ->group(function () {
        // routes...
    });
```

### Sensitive Data

- Passwords are never logged
- User input is sanitized
- Stack traces may contain sensitive info (keep logs secure)

## Future Enhancements

1. **Email Notifications**: Send emails to system admin for critical errors
2. **Slack Integration**: Post critical errors to Slack channel
3. **Auto-Resolution**: Mark certain error types as auto-resolved after time
4. **Error Grouping**: Group similar errors together
5. **Trending Analysis**: Identify recurring error patterns
6. **Performance Monitoring**: Track response times and slow queries

## Summary

The error logging system provides:
- ✅ Automatic error capture
- ✅ Detailed error information
- ✅ User-friendly error messages
- ✅ System admin dashboard
- ✅ Error resolution tracking
- ✅ Database transaction safety
- ✅ Context preservation
- ✅ Filtering and search
- ✅ Statistics and trends

This ensures errors are properly tracked, users are informed gracefully, and system administrators can efficiently debug and resolve issues.
