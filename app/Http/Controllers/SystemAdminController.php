<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TimeSlot;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SystemAdminController extends Controller
{
    /**
     * Show the system admin login form
     */
    public function showLogin()
    {
        // If already logged in as system admin, redirect to dashboard
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()?->role === 'system_admin') {
            return redirect()->route('system-admin.dashboard');
        }

        return view('system-admin.login');
    }

    /**
     * Handle system admin login
     */
    public function login(Request $request)
    {
        try {
            // First, logout any existing admin session (school admin, etc.)
            if (Auth::guard('admin')->check()) {
                Auth::guard('admin')->logout();
            }

            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials['email'] = strtolower(trim($credentials['email']));

            $throttleMessage = $this->getSystemAdminThrottleMessage($request, $credentials['email']);
            if ($throttleMessage !== null) {
                return back()->withErrors([
                    'email' => $throttleMessage,
                ])->withInput($request->only('email'));
            }
            $remember = $request->has('remember');

            // Attempt to authenticate as admin
            if (Auth::guard('admin')->attempt($credentials, $remember)) {

                $admin = Auth::guard('admin')->user();

                // Check if the admin has system_admin role
                if ($admin->role === 'system_admin') {
                    $request->session()->regenerate();
                    $this->clearSystemAdminAttemptLimits($request, $credentials['email']);

                    SystemLog::logInfo(
                        "System admin logged in: {$admin->name}",
                        'authentication',
                    ['admin_id' => $admin->id],
                        null,
                        'system_admin_login'
                    );

                    return redirect()->route('system-admin.dashboard');
                }

                // Not a system admin, logout
                Auth::guard('admin')->logout();
                $this->registerSystemAdminAttempt($request, $credentials['email']);
                return back()->withErrors([
                    'email' => 'Access denied. System Administrator privileges required.',
                ])->withInput($request->only('email'));
            }

            $this->registerSystemAdminAttempt($request, $credentials['email']);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email'));

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'System admin login failed',
                'authentication',
                $e,
            ['email' => $request->email],
                null,
                'system_admin_login'
            );

            return back()->with('error', 'Login failed. Please try again.');
        }
    }

    private function systemAdminLimiterKeyIpAndEmail(Request $request, string $email): string
    {
        return 'system-admin-login:ip-email:' . sha1($email) . ':' . sha1((string) $request->ip());
    }

    private function systemAdminLimiterKeyEmail(string $email): string
    {
        return 'system-admin-login:email:' . sha1($email);
    }

    private function getSystemAdminThrottleMessage(Request $request, string $email): ?string
    {
        $ipEmailKey = $this->systemAdminLimiterKeyIpAndEmail($request, $email);
        if (RateLimiter::tooManyAttempts($ipEmailKey, 5)) {
            $seconds = RateLimiter::availableIn($ipEmailKey);
            return 'Too many login attempts. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s) and try again.';
        }

        $emailKey = $this->systemAdminLimiterKeyEmail($email);
        if (RateLimiter::tooManyAttempts($emailKey, 20)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return 'Too many login attempts for this account. Please wait ' . max(1, (int) ceil($seconds / 60)) . ' minute(s) and try again.';
        }

        return null;
    }

    private function registerSystemAdminAttempt(Request $request, string $email): void
    {
        RateLimiter::hit($this->systemAdminLimiterKeyIpAndEmail($request, $email), 60);
        RateLimiter::hit($this->systemAdminLimiterKeyEmail($email), 3600);
    }

    private function clearSystemAdminAttemptLimits(Request $request, string $email): void
    {
        RateLimiter::clear($this->systemAdminLimiterKeyIpAndEmail($request, $email));
        RateLimiter::clear($this->systemAdminLimiterKeyEmail($email));
    }

    /**
     * Logout system admin
     */
    public function logout(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();

            if ($admin) {
                SystemLog::logInfo(
                    "System admin logged out: {$admin->name}",
                    'authentication',
                ['admin_id' => $admin->id],
                    null,
                    'system_admin_logout'
                );
            }

            Auth::guard('admin')->logout();
            Auth::guard('instructor')->logout();
            Auth::guard('student')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('system-admin.login')->with('success', 'Logged out successfully.');
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'System admin logout failed',
                'authentication',
                $e,
            [],
                null,
                'system_admin_logout'
            );

            return back()->with('error', 'Logout failed.');
        }
    }

    /**
     * Display the system admin dashboard with overview of all schools
     */
    public function dashboard()
    {
        try {
            $stats = [
                'total_schools' => School::count(),
                'total_school_admins' => Admin::where('role', 'school_admin')->count(),
                'total_students' => Student::count(),
                'total_instructors' => Instructor::count(),
                'total_users' => Student::count() + Instructor::count(),
                'total_logs' => SystemLog::count(),
                'error_logs' => SystemLog::whereIn('level', ['error', 'critical'])->count(),
                'warning_logs' => SystemLog::where('level', 'warning')->count(),
            ];

            $schools = School::withCount([
                'students',
                'instructors',
                'admins',
            ])->get();

            $recentActivities = SystemLog::with(['school'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return view('system-admin.dashboard', compact('stats', 'schools', 'recentActivities'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load system admin dashboard',
                'system',
                $e,
            [],
                null,
                'view_dashboard'
            );

            return back()->with('error', 'Unable to load dashboard.');
        }
    }

    /**
     * View all schools
     */
    public function schools(Request $request)
    {
        try {
            $query = School::withCount([
                'students',
                'instructors',
                'admins',
                'courses',
                'bookings'
            ]);

            $search = trim((string) $request->query('search', ''));
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            $schools = $query->paginate(20);

            return view('system-admin.schools', compact('schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load schools overview',
                'system',
                $e,
            [],
                null,
                'view_schools'
            );

            return back()->with('error', 'Unable to load schools.');
        }
    }

    /**
     * Store a new driving school with its admin
     */
    public function storeSchool(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:schools,slug|regex:/^[a-z0-9\-]+$/',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|email|unique:admins,email',
                'admin_password' => ['required', 'string', new StrongPassword()],
            ]);

            DB::beginTransaction();

            // Create the school
            $school = School::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'email' => $request->email,
                'address' => $request->address,
                'status' => 'active',
            ]);

            // Create school settings
            $school->schoolSetting()->create([
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'font_family' => 'Segoe UI',
            ]);

            // Create school admin
            Admin::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => bcrypt($request->admin_password),
                'must_reset_password' => true, // Force reset on first login
                'role' => 'school_admin',
                'school_id' => $school->id,
            ]);

            DB::commit();

            try {
                SystemLog::logInfo(
                    "Created new driving school: {$school->name}",
                    'system',
                    ['school_id' => $school->id, 'admin_email' => $request->admin_email],
                    null,
                    'create_school'
                );
            } catch (\Exception $e) {
                // Logging failure should not crash the response
                Log::error("Failed to log school creation: " . $e->getMessage());
            }

            return redirect()->route('system-admin.schools')->with('success', "Driving school '{$school->name}' created successfully!");
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        }
        catch (\Exception $e) {
            DB::rollBack();

            SystemLog::logError(
                'Failed to create driving school',
                'system',
                $e,
            ['name' => $request->name],
                null,
                'create_school'
            );

            return back()->with('error', 'Unable to create driving school at this time. Please try again later.')->withInput();
        }
    }

    /**
     * Toggle school status (active/inactive)
     */
    public function toggleSchoolStatus(School $school)
    {
        try {
            $newStatus = $school->status === 'active' ? 'inactive' : 'active';
            $school->update(['status' => $newStatus]);

            SystemLog::logInfo(
                "School status changed to {$newStatus}: {$school->name}",
                'system',
            ['school_id' => $school->id, 'new_status' => $newStatus],
                $school->id,
                'toggle_school_status'
            );

            return back()->with('success', "School '{$school->name}' is now {$newStatus}.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to toggle school status',
                'system',
                $e,
            ['school_id' => $school->id],
                $school->id,
                'toggle_school_status'
            );

            return back()->with('error', 'Failed to update school status.');
        }
    }

    /**
     * Delete a school and all its data
     */
    public function deleteSchool(School $school)
    {
        try {
            $schoolName = $school->name;
            $schoolId = $school->id;

            DB::beginTransaction();

            // Delete related data in the correct order to handle restrictOnDelete constraints
            // Children/Dependencies first
            $school->payments()->delete();
            $school->sessionCompletions()->delete();
            $school->bookings()->delete();
            $school->enrollments()->delete();
            $school->enrollmentRequests()->delete();
            $school->progress()->delete();
            $school->scheduleInstructors()->delete(); 
            $school->timeSlots()->delete();
            
            // Other related data
            $school->phaseProgressions()->delete();
            $school->instructorRemovalRequests()->delete();
            $school->registrationRequests()->delete();
            $school->studentActionRequests()->delete();
            $school->reports()->delete();
            $school->notifications()->delete();
            $school->systemLogs()->delete();
            $school->gcashSettings()->delete();
            $school->schoolSetting()->delete();
            $school->branches()->delete();
            
            // Parent records last
            $school->students()->delete();
            $school->instructors()->delete();
            $school->admins()->delete();
            $school->courses()->delete();
            
            // Finally delete the school
            $school->delete();

            DB::commit();

            SystemLog::logWarning(
                "School permanently deleted: {$schoolName}",
                'system',
            ['school_id' => $schoolId, 'school_name' => $schoolName, 'deleted_by' => Auth::guard('admin')->user()?->name ?? 'System'],
                null,
                'delete_school'
            );

            return redirect()->route('system-admin.schools')->with('success', "School '{$schoolName}' and all its data has been permanently deleted.");
        }
        catch (\Exception $e) {
            DB::rollBack();

            SystemLog::logError(
                'Failed to delete school',
                'system',
                $e,
            ['school_id' => $school->id],
                $school->id,
                'delete_school'
            );

            return back()->with('error', 'Unable to delete school at this time. Please try again later.');
        }
    }

    /**
     * View all school admins (admins who manage driving schools)
     */
    public function admins(Request $request)
    {
        try {
            // Get school admins with their schools
            $query = Admin::with('school')->where('role', 'school_admin');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            $admins = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.admins', compact('admins', 'schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load admins overview',
                'system',
                $e,
            [],
                null,
                'view_admins'
            );

            return back()->with('error', 'Unable to load admins.');
        }
    }

    /**
     * Store a new school admin
     */
    public function storeAdmin(Request $request)
    {
        try {
            $request->validate([
                'school_id' => 'required|exists:schools,id',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:admins,email',
                'password' => ['required', 'confirmed', new StrongPassword()],
            ]);

            $school = School::findOrFail($request->school_id);

            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'must_reset_password' => true, // Force reset on first login
                'role' => 'school_admin',
                'school_id' => $school->id,
            ]);

            try {
                SystemLog::logInfo(
                    "Created new school admin: {$admin->name} for {$school->name}",
                    'system',
                    ['admin_id' => $admin->id, 'school_id' => $school->id, 'email' => $admin->email],
                    $school->id,
                    'create_school_admin'
                );
            } catch (\Exception $e) {
                // Logging failure should not crash the response
                Log::error("Failed to log admin creation: " . $e->getMessage());
            }

            return redirect()->route('system-admin.admins')->with('success', "School admin '{$admin->name}' created successfully for {$school->name}!");
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to create school admin',
                'system',
                $e,
            ['name' => $request->name, 'email' => $request->email],
                null,
                'create_school_admin'
            );

            return back()->with('error', 'Unable to create school admin at this time. Please try again later.')->withInput();
        }
    }

    /**
     * Toggle a school admin's active status
     */
    public function toggleAdminStatus(Admin $admin)
    {
        try {
            // Prevent toggling system admins
            if ($admin->role === 'system_admin') {
                return back()->with('error', 'Cannot modify system administrators.');
            }

            // Prevent deactivating the last active school admin
            if ($admin->is_active && $admin->role === 'school_admin') {
                $schoolAdminCount = Admin::where('school_id', '=', $admin->school_id, 'and')
                    ->where('role', '=', 'school_admin', 'and')
                    ->where('is_active', '=', true, 'and')
                    ->count('*');
                
                if ($schoolAdminCount <= 1) {
                    return back()->with('error', 'Cannot deactivate the only active school administrator.');
                }
            }

            $admin->is_active = !$admin->is_active;
            $admin->save();

            $status = $admin->is_active ? 'activated' : 'deactivated';
            $schoolName = $admin->school->name ?? 'Unknown';

            SystemLog::logInfo(
                "School admin {$status}: {$admin->name} ({$schoolName})",
                'system',
            ['admin_id' => $admin->id, 'email' => $admin->email, 'is_active' => $admin->is_active],
                $admin->school_id,
                'toggle_admin_status'
            );

            return back()->with('success', "Admin '{$admin->name}' has been {$status}.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to toggle admin status',
                'system',
                $e,
            ['admin_id' => $admin->id],
                null,
                'toggle_admin_status'
            );

            return back()->with('error', 'Failed to update admin status.');
        }
    }

    /**
     * Delete a school admin
     */
    public function deleteAdmin(Admin $admin)
    {
        try {
            // Prevent deleting system admins
            if ($admin->role === 'system_admin') {
                return back()->with('error', 'Cannot delete system administrators.');
            }

            // Prevent deleting the last school admin
            if ($admin->role === 'school_admin') {
                $schoolAdminCount = Admin::where('school_id', '=', $admin->school_id, 'and')
                    ->where('role', '=', 'school_admin', 'and')
                    ->count('*');
                
                if ($schoolAdminCount <= 1) {
                    return back()->with('error', 'Cannot delete the only school administrator left. Each school must have at least one administrator.');
                }
            }

            $adminName = $admin->name;
            $schoolName = $admin->school->name ?? 'Unknown';
            $schoolId = $admin->school_id;

            SystemLog::logWarning(
                "School admin deleted: {$adminName} from {$schoolName}",
                'system',
            ['admin_id' => $admin->id, 'email' => $admin->email, 'deleted_by' => Auth::guard('admin')->user()?->name ?? 'System'],
                $schoolId,
                'delete_school_admin'
            );

            $admin->delete();

            return back()->with('success', "Admin '{$adminName}' has been deleted.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to delete school admin',
                'system',
                $e,
            ['admin_id' => $admin->id],
                null,
                'delete_school_admin'
            );

            return back()->with('error', 'Failed to delete admin.');
        }
    }

    /**
     * View all users (students and instructors) across all schools
     */
    public function users(Request $request)
    {
        try {
            $studentsBase = Student::with('school');
            $instructorsBase = Instructor::with('school');

            if ($request->filled('school_id')) {
                $studentsBase->where('school_id', $request->school_id);
                $instructorsBase->where('school_id', $request->school_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $studentsBase->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
                $instructorsBase->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $totalStudents = (clone $studentsBase)->count();
            $activeStudents = (clone $studentsBase)->where('status', 'active')->count();
            $totalInstructors = (clone $instructorsBase)->count();
            $activeInstructors = (clone $instructorsBase)->where('status', 'active')->count();

            $students = (clone $studentsBase)
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'students_page');

            $instructors = (clone $instructorsBase)
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'instructors_page');

            $schools = School::orderBy('name')->get();

            return view('system-admin.users', compact(
                'students',
                'instructors',
                'schools',
                'totalStudents',
                'activeStudents',
                'totalInstructors',
                'activeInstructors'
            ));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load users overview',
                'system',
                $e,
            [],
                null,
                'view_users'
            );

            return back()->with('error', 'Unable to load users.');
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleUserStatus($type, $id)
    {
        try {
            if ($type === 'student') {
                $user = Student::findOrFail($id);
            }
            elseif ($type === 'instructor') {
                $user = Instructor::findOrFail($id);
            }
            else {
                return back()->with('error', 'Invalid user type.');
            }

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            SystemLog::logInfo(
                ucfirst($type) . " status changed to {$newStatus}: {$user->name}",
                'database',
            ['user_id' => $user->id, 'type' => $type, 'new_status' => $newStatus],
                $user->school_id,
                'toggle_user_status'
            );

            return back()->with('success', ucfirst($type) . " '{$user->name}' is now {$newStatus}.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to toggle user status',
                'database',
                $e,
            ['type' => $type, 'id' => $id],
                null,
                'toggle_user_status'
            );

            return back()->with('error', 'Failed to update user status.');
        }
    }

    /**
     * Delete a user (student or instructor)
     */
    public function deleteUser($type, $id)
    {
        try {
            if ($type === 'student') {
                $user = Student::findOrFail($id);
            }
            elseif ($type === 'instructor') {
                $user = Instructor::findOrFail($id);
            }
            else {
                return back()->with('error', 'Invalid user type.');
            }

            $userName = $user->name;
            $userEmail = $user->email;
            $schoolId = $user->school_id;

            SystemLog::logWarning(
                "{$type} permanently deleted: {$userName} ({$userEmail})",
                'database',
            ['user_id' => $id, 'type' => $type, 'email' => $userEmail, 'deleted_by' => Auth::guard('admin')->user()?->name ?? 'System'],
                $schoolId,
                'delete_user'
            );

            $user->delete();

            return back()->with('success', ucfirst($type) . " '{$userName}' has been permanently deleted.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to delete user',
                'database',
                $e,
            ['type' => $type, 'id' => $id],
                null,
                'delete_user'
            );

            return back()->with('error', 'Failed to delete user.');
        }
    }

    /**
     * View all students across all schools
     */
    public function students(Request $request)
    {
        try {
            $query = Student::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $students = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.students', compact('students', 'schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load students overview',
                'system',
                $e,
            [],
                null,
                'view_students'
            );

            return back()->with('error', 'Unable to load students.');
        }
    }

    /**
     * View all instructors across all schools
     */
    public function instructors(Request $request)
    {
        try {
            $query = Instructor::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $instructors = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.instructors', compact('instructors', 'schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load instructors overview',
                'system',
                $e,
            [],
                null,
                'view_instructors'
            );

            return back()->with('error', 'Unable to load instructors.');
        }
    }

    /**
     * View all courses across all schools
     */
    public function courses(Request $request)
    {
        try {
            $query = Course::with('school');

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            $courses = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.courses', compact('courses', 'schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load courses overview',
                'system',
                $e,
            [],
                null,
                'view_courses'
            );

            return back()->with('error', 'Unable to load courses.');
        }
    }

    /**
     * View all bookings across all schools
     */
    public function bookings(Request $request)
    {
        try {
            $query = Booking::with(['school', 'student', 'course']);

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $bookings = $query->orderBy('created_at', 'desc')->paginate(50);
            $schools = School::orderBy('name')->get();

            return view('system-admin.bookings', compact('bookings', 'schools'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load bookings overview',
                'system',
                $e,
            [],
                null,
                'view_bookings'
            );

            return back()->with('error', 'Unable to load bookings.');
        }
    }

    /**
     * View all payments across all schools
     */
    public function payments(Request $request)
    {
        try {
            $query = Payment::with(['school', 'booking.student']);

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('received_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('received_at', '<=', $request->date_to);
            }

            $payments = $query->orderBy('received_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(50);
            $schools = School::orderBy('name')->get();

            $totalPaid = Payment::where('status', 'approved')->sum('amount');
            $totalPending = Payment::where('status', 'pending')->sum('amount');

            return view('system-admin.payments', compact('payments', 'schools', 'totalPaid', 'totalPending'));
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load payments overview',
                'system',
                $e,
            [],
                null,
                'view_payments'
            );

            return back()->with('error', 'Unable to load payments.');
        }
    }

    /**
     * Display the system logs dashboard
     */
    public function logs(Request $request)
    {
        try {
            $query = SystemLog::with(['school', 'user', 'resolvedBy'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('school_id')) {
                $query->where('school_id', $request->school_id);
            }

            if ($request->filled('resolved')) {
                if ($request->resolved === 'yes') {
                    $query->resolved();
                }
                else {
                    $query->unresolved();
                }
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->paginate(50);

            // Get statistics
            $stats = [
                'total' => SystemLog::count(),
                'unresolved' => SystemLog::unresolved()->count(),
                'critical' => SystemLog::critical()->unresolved()->count(),
                'today' => SystemLog::whereDate('created_at', today())->count(),
                'this_week' => SystemLog::where('created_at', '>=', now()->startOfWeek())->count(),
            ];

            // Get all schools for filter dropdown
            $schools = School::orderBy('name')->get();

            return view('system-admin.logs', [
                'logs' => $logs,
                'stats' => $stats,
                'schools' => $schools,
            ]);
        }
        catch (\Exception $e) {
            SystemLog::logCritical(
                'Failed to load system admin logs page',
                'system',
                $e,
            [],
                null,
                'view_system_logs'
            );

            return back()->with('error', 'Unable to load system logs.');
        }
    }

    /**
     * Show detailed view of a specific log
     */
    public function showLog(SystemLog $log)
    {
        try {
            $log->load(['school', 'user', 'resolvedBy']);

            return view('system-admin.log-detail', [
                'log' => $log,
            ]);
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to load log detail',
                'system',
                $e,
            ['log_id' => $log->id],
                null,
                'view_log_detail'
            );

            return back()->with('error', 'Unable to load log details.');
        }
    }

    /**
     * Mark a log as resolved
     */
    public function resolveLog(Request $request, SystemLog $log)
    {
        try {
            $request->validate([
                'resolution_notes' => 'nullable|string|max:1000',
            ]);

            $log->resolve($request->resolution_notes);

            SystemLog::logInfo(
                "System log #{$log->id} marked as resolved",
                'system',
            ['log_id' => $log->id],
                $log->school_id,
                'resolve_log'
            );

            return back()->with('success', 'Log marked as resolved successfully.');
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to resolve log',
                'system',
                $e,
            ['log_id' => $log->id],
                $log->school_id,
                'resolve_log'
            );

            return back()->with('error', 'Unable to resolve log.');
        }
    }

    /**
     * Delete old resolved logs
     */
    public function cleanupLogs(Request $request)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:30|max:365',
            ]);

            $cutoffDate = now()->subDays($request->days);

            $count = SystemLog::resolved()
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            SystemLog::logInfo(
                "Cleaned up {$count} old system logs",
                'system',
            ['days' => $request->days, 'count' => $count],
                null,
                'cleanup_logs'
            );

            return back()->with('success', "Successfully deleted {$count} old resolved logs.");
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to cleanup logs',
                'system',
                $e,
            ['days' => $request->days ?? null],
                null,
                'cleanup_logs'
            );

            return back()->with('error', 'Unable to cleanup logs.');
        }
    }

    /**
     * Get logs statistics for dashboard
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'errors_by_category' => SystemLog::unresolved()
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),

                'errors_by_school' => SystemLog::unresolved()
                ->select('school_id', DB::raw('count(*) as count'))
                ->with('school:id,name')
                ->groupBy('school_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),

                'errors_by_level' => SystemLog::unresolved()
                ->select('level', DB::raw('count(*) as count'))
                ->groupBy('level')
                ->get(),

                'recent_critical' => SystemLog::critical()
                ->unresolved()
                ->with(['school', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            ];

            return response()->json($stats);
        }
        catch (\Exception $e) {
            SystemLog::logError(
                'Failed to get statistics',
                'system',
                $e,
            [],
                null,
                'get_statistics'
            );

            return response()->json(['error' => 'Unable to load statistics'], 500);
        }
    }
}
