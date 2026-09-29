<?php

namespace App\Http\Controllers;

use App\Mail\OtpVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\SystemLog;
use App\Support\DemoAccountProtection;

class AuthController extends Controller
{
    public function showLogin(School $school)
    {
        // One-time break-glass flag to prevent redirect loops on dashboard failure
        if (session()->pull('dashboard_failed')) {
            return view($school->resolveView('login'), [
                'school' => $school,
            ]);
        }

        // Redirect if already authenticated
        if (Auth::guard('admin')->check()) {
            return redirect()->route('schools.admin.dashboard', $school);
        }
        if (Auth::guard('instructor')->check()) {
            return redirect()->route('schools.instructor.dashboard', $school);
        }
        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();

            // Redirection for unverified students (fix for 'Back to Login' bypass)
            if (!$student->hasVerifiedEmail()) {
                return redirect()->route('schools.verification.show', $school);
            }

            if ($student->role === 'guest') {
                return redirect()->route('schools.guest.dashboard', $school);
            }
            return redirect()->route('schools.student.dashboard', $school);
        }

        // Eager load schoolSetting to prevent N+1 query in login view
        $school->load('schoolSetting');

        return view($school->resolveView('login'), [
            'school' => $school,
        ]);
    }


    public function login(Request $request, School $school)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        $email = strtolower(trim($request->email));
        $password = $request->password;
        $remember = $request->has('remember');

        $throttleMessage = $this->getLoginThrottleMessage($request, $school, $email);
        if ($throttleMessage !== null) {
            SystemLog::logWarning(
                "Rate-limited login attempt: {$email}",
                'authentication',
                ['email' => $email, 'ip' => $request->ip(), 'reason' => 'rate_limited'],
                $school->id,
                'rate_limited_login_attempt'
            );

            return back()->withErrors([
                'email' => $throttleMessage,
            ])->withInput($request->only('email', 'remember'));
        }

        $request->session()->put('school_id', $school->id);

        // Optimize: Try to find user in single query by checking all tables
        // Check admin first (most privileged)
        $admin = Admin::where('school_id', $school->id)->where('email', $email)->first();
        if ($admin) {
            // Check if account is locked
            if ($admin->locked_until && now()->lessThan($admin->locked_until)) {
                $this->registerFailedLoginAttempt($request, $school, $email);
                $remainingMinutes = now()->diffInMinutes($admin->locked_until) + 1;
                SystemLog::logWarning(
                    "Locked admin account login attempt: {$email}",
                    'authentication',
                ['email' => $email, 'admin_id' => $admin->id, 'locked_until' => $admin->locked_until],
                    $school->id,
                    'locked_login_attempt'
                );
                return back()->withErrors([
                    'email' => "Your account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes.",
                ])->withInput($request->only('email', 'remember'));
            }

            if (Hash::check($password, $admin->password)) {
                // Check if admin account is active (applies to school_admin and branch_secretary)
                if (!$admin->is_active) {
                    SystemLog::logWarning(
                        "Deactivated admin attempted login: {$email}",
                        'authentication',
                    ['email' => $email, 'admin_id' => $admin->id, 'role' => $admin->role, 'reason' => 'account_inactive'],
                        $school->id,
                        'blocked_login'
                    );

                    return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.'])->withInput($request->only('email', 'remember'));
                }

                // Reset failed attempts and update last login
                $admin->update([
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'last_login_at' => now(),
                ]);

                $this->clearOtherAuthGuards('admin');
                Auth::guard('admin')->login($admin, $remember);
                $request->session()->regenerate();

                // Check for forced password reset
                if ($admin->must_reset_password) {
                    SystemLog::logInfo(
                        "Admin forced to reset password: {$admin->name}",
                        'authentication',
                        ['admin_id' => $admin->id, 'email' => $admin->email],
                        $school->id,
                        'force_password_reset_triggered'
                    );

                    return redirect()->route('schools.password.force-reset', $school)
                        ->with('info', 'Please set a new password for your account to continue.');
                }

                // Log successful login — label reflects actual role
                $roleLabel = match ($admin->role) {
                        'branch_secretary' => 'Branch secretary',
                        'school_admin' => 'School admin',
                        default => 'Admin',
                    };
                SystemLog::logInfo(
                    "{$roleLabel} logged in: {$admin->name}",
                    'authentication',
                ['admin_id' => $admin->id, 'email' => $admin->email, 'role' => $admin->role],
                    $school->id,
                    'school_admin_login'
                );

                $this->clearLoginAttemptLimits($request, $school, $email);

                return redirect()->route('schools.admin.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $admin->name . '!');
            }

            // Wrong password for admin - increment failed attempts
            $failedAttempts = $admin->failed_login_attempts + 1;
            $lockMinutes = $this->calculateAccountLockMinutes($failedAttempts);
            $lockAccount = $lockMinutes !== null;

            $admin->update([
                'failed_login_attempts' => $failedAttempts,
                'locked_until' => $lockAccount ? now()->addMinutes($lockMinutes) : null,
            ]);

            $this->registerFailedLoginAttempt($request, $school, $email);

            SystemLog::logWarning(
                "Failed login attempt for school admin: {$email}",
                'authentication',
            ['email' => $email, 'failed_attempts' => $failedAttempts, 'locked' => $lockAccount],
                $school->id,
                'failed_login'
            );

            $errorMessage = $lockAccount
                ? "Your account has been locked for {$lockMinutes} minutes due to multiple failed login attempts."
                : 'The provided credentials do not match our records.';

            return back()->withErrors(['email' => $errorMessage])->withInput($request->only('email', 'remember'));
        }

        // Check instructor
        $instructor = Instructor::where('school_id', $school->id)->where('email', $email)->first();
        if ($instructor) {
            // Check if account is locked
            if ($instructor->locked_until && now()->lessThan($instructor->locked_until)) {
                $this->registerFailedLoginAttempt($request, $school, $email);
                $remainingMinutes = now()->diffInMinutes($instructor->locked_until) + 1;
                SystemLog::logWarning(
                    "Locked instructor account login attempt: {$email}",
                    'authentication',
                ['email' => $email, 'instructor_id' => $instructor->id, 'locked_until' => $instructor->locked_until],
                    $school->id,
                    'locked_login_attempt'
                );
                return back()->withErrors([
                    'email' => "Your account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes.",
                ])->withInput($request->only('email', 'remember'));
            }

            if (!Hash::check($password, $instructor->password)) {
                // Increment failed attempts
                $failedAttempts = $instructor->failed_login_attempts + 1;
                $lockMinutes = $this->calculateAccountLockMinutes($failedAttempts);
                $lockAccount = $lockMinutes !== null;

                $instructor->update([
                    'failed_login_attempts' => $failedAttempts,
                    'locked_until' => $lockAccount ? now()->addMinutes($lockMinutes) : null,
                ]);

                $this->registerFailedLoginAttempt($request, $school, $email);

                SystemLog::logWarning(
                    "Failed login attempt for instructor: {$email}",
                    'authentication',
                ['email' => $email, 'failed_attempts' => $failedAttempts, 'locked' => $lockAccount],
                    $school->id,
                    'failed_login'
                );

                $errorMessage = $lockAccount
                    ? "Your account has been locked for {$lockMinutes} minutes due to multiple failed login attempts."
                    : 'The provided credentials do not match our records.';

                return back()->withErrors(['email' => $errorMessage])->withInput($request->only('email', 'remember'));
            }

            if ($instructor->status !== 'active') {
                $this->registerFailedLoginAttempt($request, $school, $email);
                // Log deactivated account login attempt
                SystemLog::logWarning(
                    "Deactivated instructor attempted login: {$email}",
                    'authentication',
                ['email' => $email, 'instructor_id' => $instructor->id, 'reason' => 'account_inactive'],
                    $school->id,
                    'blocked_login'
                );

                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }

            // Reset failed attempts and update last login
            $instructor->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $this->clearOtherAuthGuards('instructor');
            Auth::guard('instructor')->login($instructor, $remember);
            $request->session()->regenerate();

            // Check for forced password reset
            if ($instructor->must_reset_password) {
                SystemLog::logInfo(
                    "Instructor forced to reset password: {$instructor->name}",
                    'authentication',
                    ['instructor_id' => $instructor->id, 'email' => $instructor->email],
                    $school->id,
                    'force_password_reset_triggered'
                );

                return redirect()->route('schools.password.force-reset', $school)
                    ->with('info', 'Please set a new password for your account to continue.');
            }

            // Log successful instructor login
            SystemLog::logInfo(
                "Instructor logged in: {$instructor->name}",
                'authentication',
                ['instructor_id' => $instructor->id, 'email' => $instructor->email],
                $school->id,
                'instructor_login'
            );

            $this->clearLoginAttemptLimits($request, $school, $email);

            return redirect()->route('schools.instructor.dashboard', $school)
                ->with('success', 'Welcome back, ' . $instructor->name . '!');
        }

        // Check student
        $student = Student::where('school_id', $school->id)->where('email', $email)->first();
        if ($student) {
            // Check if account is locked
            if ($student->locked_until && now()->lessThan($student->locked_until)) {
                $this->registerFailedLoginAttempt($request, $school, $email);
                $remainingMinutes = now()->diffInMinutes($student->locked_until) + 1;
                SystemLog::logWarning(
                    "Locked student account login attempt: {$email}",
                    'authentication',
                ['email' => $email, 'student_id' => $student->id, 'locked_until' => $student->locked_until],
                    $school->id,
                    'locked_login_attempt'
                );
                return back()->withErrors([
                    'email' => "Your account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minutes.",
                ])->withInput($request->only('email', 'remember'));
            }

            if (!Hash::check($password, $student->password)) {
                // Increment failed attempts
                $failedAttempts = $student->failed_login_attempts + 1;
                $lockMinutes = $this->calculateAccountLockMinutes($failedAttempts);
                $lockAccount = $lockMinutes !== null;

                $student->update([
                    'failed_login_attempts' => $failedAttempts,
                    'locked_until' => $lockAccount ? now()->addMinutes($lockMinutes) : null,
                ]);

                $this->registerFailedLoginAttempt($request, $school, $email);

                SystemLog::logWarning(
                    "Failed login attempt for student: {$email}",
                    'authentication',
                ['email' => $email, 'failed_attempts' => $failedAttempts, 'locked' => $lockAccount],
                    $school->id,
                    'failed_login'
                );

                $errorMessage = $lockAccount
                    ? "Your account has been locked for {$lockMinutes} minutes due to multiple failed login attempts."
                    : 'The provided credentials do not match our records.';

                return back()->withErrors(['email' => $errorMessage])->withInput($request->only('email', 'remember'));
            }

            if ($student->status !== 'active') {
                $this->registerFailedLoginAttempt($request, $school, $email);
                // Log deactivated account login attempt
                SystemLog::logWarning(
                    "Deactivated student attempted login: {$email}",
                    'authentication',
                ['email' => $email, 'student_id' => $student->id, 'reason' => 'account_inactive'],
                    $school->id,
                    'blocked_login'
                );

                return back()->withErrors(['email' => 'Your account has been deactivated. Please contact the administrator.']);
            }

            // Check email verification
            if (!$student->hasVerifiedEmail()) {
                // Regenerate verification code and send email
                $otp = $student->generateVerificationCode();

                try {
                    Mail::to($student->email)
                        ->send(new OtpVerificationCode($school, $student, $otp, false));
                }
                catch (\Exception $e) {
                    Log::error('Failed to send verification email: ' . $e->getMessage());
                }

                // Store in session and redirect to verification
                session(['verification_email' => $student->email, 'school_slug' => $school->slug]);

                // In local/dev environment, store OTP in session for testing (since emails don't send to fake addresses)
                if (app()->environment('local', 'development', 'testing')) {
                    session(['dev_verification_code' => $otp]);
                    // Also flash test credentials for easier testing (mirrors guest registration)
                    session()->flash('test_credentials', [
                        'email' => $student->email,
                        'password' => $password,
                        'name' => $student->name,
                        'otp' => $otp,
                    ]);
                }

                $this->clearLoginAttemptLimits($request, $school, $email);

                return redirect()->route('schools.verification.show', $school)
                    ->with('info', 'Please verify your email address. We sent a new verification code to your email.');
            }

            // Reset failed attempts and update last login
            $student->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $this->clearOtherAuthGuards('student');
            Auth::guard('student')->login($student, $remember);
            $request->session()->regenerate();

            // Check for forced password reset
            if ($student->must_reset_password) {
                SystemLog::logInfo(
                    "Student forced to reset password: {$student->name}",
                    'authentication',
                    ['student_id' => $student->id, 'email' => $student->email],
                    $school->id,
                    'force_password_reset_triggered'
                );

                return redirect()->route('schools.password.force-reset', $school)
                    ->with('info', 'Please set a new password for your account to continue.');
            }

            // Log successful student/guest login
            $userType = $student->role === 'guest' ? 'Guest' : 'Student';
            SystemLog::logInfo(
                "{$userType} logged in: {$student->name}",
                'authentication',
            ['student_id' => $student->id, 'email' => $student->email, 'role' => $student->role],
                $school->id,
                strtolower($userType) . '_login'
            );

            $this->clearLoginAttemptLimits($request, $school, $email);

            // Redirect based on role (guest or student)
            if ($student->role === 'guest') {
                return redirect()->route('schools.guest.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $student->name . '!');
            }
            else {
                return redirect()->route('schools.student.dashboard', $school)
                    ->with('success', 'Welcome back, ' . $student->name . '!');
            }
        }

        // No user found with this email - log unknown email attempt
        SystemLog::logWarning(
            "Login attempt with unknown email: {$email}",
            'authentication',
        ['email' => $email, 'reason' => 'email_not_found'],
            $school->id,
            'failed_login'
        );

        $this->registerFailedLoginAttempt($request, $school, $email);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request, School $school)
    {
        $logMessage = null;
        $logContext = [];
        $action = 'logout';

        // Log the primary authenticated guard before logout
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $admin->update(['last_logout_at' => now()]);
            $logMessage = "School admin logged out: {$admin->name}";
            $logContext = ['admin_id' => $admin->id, 'email' => $admin->email];
            $action = 'school_admin_logout';
        }
        elseif (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            $instructor->update(['last_logout_at' => now()]);
            $logMessage = "Instructor logged out: {$instructor->name}";
            $logContext = ['instructor_id' => $instructor->id, 'email' => $instructor->email];
            $action = 'instructor_logout';
        }
        elseif (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            $student->update(['last_logout_at' => now()]);
            $userType = $student->role === 'guest' ? 'Guest' : 'Student';
            $logMessage = "{$userType} logged out: {$student->name}";
            $logContext = ['student_id' => $student->id, 'email' => $student->email, 'role' => $student->role];
            $action = strtolower($userType) . '_logout';
        }

        // Clear ALL guards to prevent stale remember cookies from interfering
        Auth::guard('admin')->logout();
        Auth::guard('instructor')->logout();
        Auth::guard('student')->logout();

        // Log the logout event
        if ($logMessage) {
            SystemLog::logInfo($logMessage, 'authentication', $logContext, $school->id, $action);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('schools.login', $school)
            ->with('success', 'You have been logged out successfully.');
    }

    public function showForceResetForm(School $school)
    {
        $user = Auth::guard('admin')->user() 
             ?? Auth::guard('instructor')->user() 
             ?? Auth::guard('student')->user();

        if (!$user || !$user->must_reset_password) {
            // Re-route to their respective dashboard if they don't need reset
            if (Auth::guard('admin')->check()) return redirect()->route('schools.admin.dashboard', $school);
            if (Auth::guard('instructor')->check()) return redirect()->route('schools.instructor.dashboard', $school);
            if (Auth::guard('student')->check()) return redirect()->route('schools.student.dashboard', $school);
            return redirect()->route('schools.login', $school);
        }

        return view($school->resolveView('password.force-reset'), [
            'school' => $school,
            'user' => $user,
        ]);
    }

    public function handleForceReset(Request $request, School $school)
    {
        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('instructor')->check() ? 'instructor' : 'student');
        $user = Auth::guard($guard)->user();

        if (!$user || !$user->must_reset_password) {
             return redirect()->route('schools.login', $school);
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed', new \App\Rules\StrongPassword()],
        ]);

        if (in_array($guard, ['student', 'instructor'], true)
            && DemoAccountProtection::isProtectedAccount($user->email, $guard, $school)) {
            $user->update(['must_reset_password' => false]);

            SystemLog::logInfo(
                "Protected demo account skipped forced password reset: {$user->name}",
                'authentication',
                ['user_id' => $user->id, 'email' => $user->email, 'guard' => $guard],
                $school->id,
                'force_password_reset_skipped'
            );

            $routes = [
                'admin' => 'schools.admin.dashboard',
                'instructor' => 'schools.instructor.dashboard',
                'student' => $user->role === 'guest' ? 'schools.guest.dashboard' : 'schools.student.dashboard',
            ];

            return redirect()->route($routes[$guard], $school)
                ->with('info', 'This demo account password is fixed and cannot be changed.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_reset_password' => false,
        ]);

        SystemLog::logInfo(
            "User completed forced password reset: {$user->name}",
            'authentication',
            ['user_id' => $user->id, 'email' => $user->email, 'guard' => $guard],
            $school->id,
            'force_password_reset_completed'
        );

        // Redirect based on guard
        $routes = [
            'admin' => 'schools.admin.dashboard',
            'instructor' => 'schools.instructor.dashboard',
            'student' => $user->role === 'guest' ? 'schools.guest.dashboard' : 'schools.student.dashboard'
        ];

        return redirect()->route($routes[$guard], $school)
            ->with('success', 'Your password has been updated successfully. Welcome to the portal!');
    }

    private function clearOtherAuthGuards(string $keepGuard): void
    {
        foreach (['admin', 'instructor', 'student'] as $guard) {
            if ($guard !== $keepGuard) {
                Auth::guard($guard)->logout();
            }
        }
    }

    private function loginLimiterKeyIpAndEmail(Request $request, School $school, string $email): string
    {
        return 'login:ip-email:' . $school->id . ':' . sha1($email) . ':' . sha1((string) $request->ip());
    }

    private function loginLimiterKeyEmail(School $school, string $email): string
    {
        return 'login:email:' . $school->id . ':' . sha1($email);
    }

    private function getLoginThrottleMessage(Request $request, School $school, string $email): ?string
    {
        $ipEmailKey = $this->loginLimiterKeyIpAndEmail($request, $school, $email);
        if (RateLimiter::tooManyAttempts($ipEmailKey, 5)) {
            $seconds = RateLimiter::availableIn($ipEmailKey);
            return 'Too many login attempts. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s) and try again.';
        }

        $emailKey = $this->loginLimiterKeyEmail($school, $email);
        if (RateLimiter::tooManyAttempts($emailKey, 20)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return 'Too many login attempts for this account. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s) and try again.';
        }

        return null;
    }

    private function registerFailedLoginAttempt(Request $request, School $school, string $email): void
    {
        RateLimiter::hit($this->loginLimiterKeyIpAndEmail($request, $school, $email), 60);
        RateLimiter::hit($this->loginLimiterKeyEmail($school, $email), 3600);
    }

    private function clearLoginAttemptLimits(Request $request, School $school, string $email): void
    {
        RateLimiter::clear($this->loginLimiterKeyIpAndEmail($request, $school, $email));
        RateLimiter::clear($this->loginLimiterKeyEmail($school, $email));
    }

    private function calculateAccountLockMinutes(int $failedAttempts): ?int
    {
        if ($failedAttempts >= 12) {
            return 120;
        }

        if ($failedAttempts >= 9) {
            return 60;
        }

        if ($failedAttempts >= 7) {
            return 30;
        }

        if ($failedAttempts >= 5) {
            return 15;
        }

        return null;
    }
}