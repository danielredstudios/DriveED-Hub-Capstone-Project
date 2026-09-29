<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Invitation;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\SystemLog;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding form for the given token.
     */
    public function show(School $school, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('school_id', $school->id)
            ->first();

        if (!$invitation) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'Invalid or expired invitation link.');
        }

        if ($invitation->isUsed()) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'This invitation has already been used.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('schools.login', $school)
                ->with('error', 'This invitation has expired. Please request a new one.');
        }

        return view($school->resolveView('auth.onboarding'), [
            'school' => $school,
            'invitation' => $invitation,
            'role' => $invitation->role
        ]);
    }

    /**
     * Submit the onboarding form and create the account.
     */
    public function submit(Request $request, School $school, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('school_id', $school->id)
            ->firstOrFail();

        if ($invitation->isUsed() || $invitation->isExpired()) {
            return redirect()->route('schools.login', $school)->with('error', 'Invalid invitation.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', new StrongPassword()],
        ];

        // Specific fields based on role
        if ($invitation->role === 'student' || $invitation->role === 'instructor') {
            $rules['contact'] = ['required', 'string', 'max:13', 'regex:/^(09\d{9}|\+639\d{9})$/'];
            $rules['address'] = 'required|string|max:500';
        }

        $validated = $request->validate($rules);

        if ($invitation->role === 'branch_secretary') {
            if (!$invitation->branch_id) {
                return back()->with('error', 'Branch assignment is required for branch manager invitations.');
            }

            $capacity = $this->buildBranchSecretaryCapacitySummary(
                $school,
                (int) $invitation->branch_id,
                $invitation->id
            );

            if (($capacity['used'] + 1) > $capacity['limit']) {
                return back()->with(
                    'error',
                    "This invitation can no longer be accepted because the selected branch is at capacity ({$capacity['used']}/{$capacity['limit']})."
                );
            }
        }

        try {
            DB::beginTransaction();

            $commonData = [
                'school_id' => $school->id,
                'branch_id' => $invitation->branch_id,
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'must_reset_password' => true, // Enforce reset on first login via normal flow
            ];

            $user = null;
            $guard = 'admin';

            switch ($invitation->role) {
                case 'school_admin':
                    $user = Admin::create(array_merge($commonData, [
                        'role' => $invitation->role,
                        'status' => 'active',
                    ]));
                    $guard = 'admin';
                    break;

                case 'branch_secretary':
                    if (!$invitation->branch_id) {
                        throw new \Exception('Branch assignment required for branch manager invitation.');
                    }

                    $user = Admin::create(array_merge($commonData, [
                        'role' => $invitation->role,
                        'status' => 'active',
                    ]));
                    $guard = 'admin';
                    break;

                case 'instructor':
                    $payload = $invitation->payload ?? [];
                    $user = Instructor::create(array_merge($commonData, [
                        'license_number' => $payload['license_number'] ?? null,
                        'license_image' => $payload['license_image'] ?? null,
                        'license_status' => ($payload['license_image'] ?? null) ? 'pending' : 'none',
                        'course_specializations' => $payload['course_specializations'] ?? null,
                        'contact' => $validated['contact'],
                        'address' => $validated['address'],
                        'status' => 'active',
                        'availability' => 'available',
                    ]));
                    $guard = 'instructor';
                    break;

                case 'student':
                    $payload = $invitation->payload ?? [];
                    $user = Student::create(array_merge($commonData, [
                        'contact' => $validated['contact'],
                        'address' => $validated['address'],
                        'status' => 'active',
                        'role' => 'student', // Ensure role is set to student, not guest
                    ]));

                    // Handle Auto-Enrollment (Item 5)
                    if (!empty($payload['course_id'])) {
                        $course = Course::find($payload['course_id']);
                        if ($course) {
                            $isTheoretical = $course->course_type === 'theoretical';
                            
                            $enrollment = EnrollmentRequest::create([
                                'learner_id' => $user->id,
                                'course_id' => $course->id,
                                'school_id' => $school->id,
                                'branch_id' => $user->branch_id,
                                'status' => $isTheoretical ? 'approved' : 'pending',
                                'payment_status' => 'pending', 
                                'notes' => 'Auto-enrolled via invitation by Admin.'
                            ]);

                            // If it's a practical course and they already passed TDC (as marked by admin)
                            // We might want to set has_passed_theoretical if the admin selected a theoretical course?
                            // No, Item 5 says "if we just add them, they will need to enroll"
                            // But Item 6 is about LTO rules.

                            SystemLog::logInfo(
                                "Student auto-enrolled in course: {$course->title}",
                                'enrollment',
                                ['student_id' => $user->id, 'course_id' => $course->id, 'status' => $isTheoretical ? 'approved' : 'pending'],
                                $school->id
                            );
                        }
                    }
                    $guard = 'student';
                    break;

                default:
                    throw new \Exception("Unsupported role: {$invitation->role}");
            }

            // Mark invitation as used
            $invitation->update(['used_at' => now()]);

            SystemLog::logInfo(
                "User activated account via invitation: {$user->email}",
                'authentication',
                ['role' => $invitation->role, 'user_id' => $user->id],
                $school->id
            );

            DB::commit();

            // Log the user in
            Auth::guard($guard)->login($user);

            // Redirect based on role (AuthController should intercept the must_reset_password but just in case)
            $routes = [
                'admin' => 'schools.admin.dashboard',
                'instructor' => 'schools.instructor.dashboard',
                'student' => 'schools.student.dashboard'
            ];

            return redirect()->route($routes[$guard], $school)
                ->with('success', 'Welcome! Your account has been successfully activated.');

        } catch (\Exception $e) {
            DB::rollBack();
            SystemLog::logError("Onboarding failure: " . $e->getMessage(), 'database', $e, ['email' => $invitation->email], $school->id);
            return back()->with('error', 'Failed to activate account. Please try again or contact support.');
        }
    }

    private function getBranchSecretaryLimit(School $school): int
    {
        return max(1, (int) ($school->schoolSetting?->branch_secretary_limit_per_branch ?? 1));
    }

    private function buildBranchSecretaryCapacitySummary(
        School $school,
        int $branchId,
        ?int $excludeInvitationId = null
    ): array {
        $limit = $this->getBranchSecretaryLimit($school);

        $activeCount = Admin::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->where('is_active', true)
            ->count();

        $pendingInvitationQuery = Invitation::query()
            ->where('school_id', $school->id)
            ->where('branch_id', $branchId)
            ->where('role', Admin::ROLE_BRANCH_SECRETARY)
            ->whereNull('used_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        if ($excludeInvitationId !== null) {
            $pendingInvitationQuery->where('id', '!=', $excludeInvitationId);
        }

        $pendingCount = $pendingInvitationQuery->count();
        $used = $activeCount + $pendingCount;

        return [
            'limit' => $limit,
            'active' => $activeCount,
            'pending' => $pendingCount,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
            'at_capacity' => $used >= $limit,
        ];
    }
}
