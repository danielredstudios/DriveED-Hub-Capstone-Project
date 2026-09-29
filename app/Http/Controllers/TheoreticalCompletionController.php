<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentRequest;
use App\Models\School;
use App\Models\Student;
use App\Models\SessionCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFacade;

class TheoreticalCompletionController extends Controller
{
    /**
     * Get the authenticated school and guard info
     */
    private function getSchoolAndGuard(): array
    {
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            return [
                'school' => $admin->school,
                'guard' => 'admin',
                'viewPrefix' => 'school.admin.theoretical',
                'routePrefix' => 'schools.admin.theoretical',
            ];
        }

        $instructor = Auth::guard('instructor')->user();
        if ($instructor) {
            return [
                'school' => $instructor->school,
                'guard' => 'instructor',
                'viewPrefix' => 'school.instructor.theoretical',
                'routePrefix' => 'schools.instructor.theoretical',
            ];
        }

        throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Unauthorized');
    }

    /**
     * Unified theoretical training page with tabs: In Training, Mark Completion, Passed
     */
    public function index(Request $request)
    {
        $ctx = $this->getSchoolAndGuard();
        $school = $ctx['school'];

        $activeQuery = EnrollmentRequest::with(['learner', 'course', 'sessionCompletions'])
            ->whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->where('course_type', 'theoretical');
        })
            ->whereHas('student', function ($query) {
            $query->where('has_passed_theoretical', false);
        })
            ->where('status', 'approved');

        // Calculate absolute totals for stats before pagination
        $totalInTraining = (clone $activeQuery)->count();
        $readyToPass = (clone $activeQuery)->get()->filter(function ($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
            $enrollment->required_hours = $enrollment->course->theoretical_hours ?? $enrollment->course->hours_required ?? 15;
            return $enrollment->required_hours > 0 && ($enrollment->total_hours / $enrollment->required_hours) >= 1;
        })->count();

        $activeEnrollments = $activeQuery->paginate(10, ['*'], 'active_page');

        // Calculate hours & progress for the current page
        $activeEnrollments->getCollection()->transform(function ($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
            $enrollment->required_hours = $enrollment->course->theoretical_hours ?? $enrollment->course->hours_required ?? 15;
            $enrollment->progress = $enrollment->required_hours > 0
                ? min(100, round(($enrollment->total_hours / $enrollment->required_hours) * 100))
                : 0;
            $enrollment->session_count = $enrollment->sessionCompletions->count();
            $enrollment->last_session = $enrollment->sessionCompletions->sortByDesc('session_date')->first();
            return $enrollment;
        });

        // Split for Mark Completion tab (representing ONLY current page)
        $readyEnrollments = $activeEnrollments->getCollection()->where('progress', '>=', 100)->values();
        $notReadyEnrollments = $activeEnrollments->getCollection()->where('progress', '<', 100)->values();

        // ── Passed Students tab ──
        $passedStudents = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->latest('updated_at')
            ->paginate(10, ['*'], 'passed_page');

        $totalPassed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->count();

        return view($ctx['viewPrefix'] . '.index', array_merge(compact(
            'school', 'activeEnrollments', 'totalInTraining', 'readyToPass',
            'readyEnrollments', 'notReadyEnrollments',
            'passedStudents', 'totalPassed'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the Student Training Hub & Life Log
     */
    public function show(Request $request, School $school, $enrollment)
    {
        // Check authentication guards
        if (!Auth::guard('admin')->check() && !Auth::guard('instructor')->check()) {
            abort(403, 'Unauthorized');
        }

        // Retrieval of primary enrollment
        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course', 'sessionCompletions.instructor'])
            ->findOrFail($enrollment);

        $student = $enrollment->learner;

        // Fetch all student enrollments for the Life Log
        $allEnrollments = $student->enrollments()
            ->with(['course', 'package'])
            ->where('school_id', $school->id)
            ->get();

        // Check for active (LIVE) session
        $now = now();
        $liveSession = $student->bookings()
            ->where('status', 'scheduled') // Or 'in_progress' if you have that status
            ->whereHas('timeSlot', function($q) use ($now) {
                $q->where('date', $now->toDateString())
                  ->where('start_time', '<=', $now->toTimeString())
                  ->where('end_time', '>=', $now->toTimeString());
            })
            ->with(['instructor', 'timeSlot'])
            ->first();

        // Validate if student can be marked as passed TDC
        $validation = $this->validateCanPassTheoretical($enrollment);

        $viewPath = Auth::guard('admin')->check()
            ? 'school.admin.theoretical.show'
            : 'school.instructor.theoretical.show';

        // Check for any pending phase progression requests
        $pendingProgression = \App\Models\PhaseProgression::where('enrollment_id', $enrollment->id)
            ->where('status', 'pending')
            ->first();

        return view($viewPath, array_merge(
            compact('school', 'enrollment', 'student', 'allEnrollments', 'liveSession', 'validation', 'pendingProgression'), 
            ['isAjax' => $request->ajax()]
        ));
    }

    /**
     * Validate if a student can be marked as passed theoretical
     */
    private function validateCanPassTheoretical(EnrollmentRequest $enrollment): array
    {
        return \App\Support\EnrollmentValidator::canMarkTheoreticalPassed($enrollment);
    }

    /**
     * Mark a student as passed theoretical (TDC Graduation)
     */
    public function markAsPassed(Request $request)
    {
        $ctx = $this->getSchoolAndGuard();
        $school = $ctx['school'];
        $user = Auth::guard($ctx['guard'])->user();

        if (!$user) {
            abort(403);
        }

        $request->validate([
            'enrollment_id' => 'required|exists:enrollment_requests,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course'])
            ->findOrFail($request->enrollment_id);

        // Verify student hasn't already passed theoretical
        if ($enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'Student has already passed theoretical.');
        }

        DB::beginTransaction();
        try {
            // Mark theoretical as passed
            $enrollment->markTheoreticalPassed($user->id, $request->notes);

            // If it is ONLY a theoretical course, we can complete the enrollment now
            if ($enrollment->course->course_type === 'theoretical') {
                $enrollment->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $enrollment->learner->unlockFromCourse();
                $message = 'Student marked as passed theoretical and enrollment completed!';
            } else {
                // For Combo/Practical, we just mark TDC as passed, they stay "Enrolled" for PDC
                $message = 'Student marked as passed theoretical! They are now eligible for PDC.';
            }

            DB::commit();

            return back()->with('success', $message);

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to mark student as passed theoretical: ' . $e->getMessage(), [
                'enrollment_id' => $request->enrollment_id,
                'exception' => $e
            ]);
            return back()->with('error', 'Unable to mark status at this time.');
        }
    }

    /**
     * Final Graduation (Complete the entire enrollment)
     */
    public function complete(Request $request, School $school, $enrollment)
    {
        $enrollment = $school->enrollmentRequests()
            ->with(['learner', 'course'])
            ->findOrFail($enrollment);

        $user = Auth::guard('admin')->user() ?? Auth::guard('instructor')->user();

        DB::beginTransaction();
        try {
            // Use the centralized graduation logic which also resolves pending phase requests
            $enrollment->markAsCompleted();

            DB::commit();

            return back()->with('success', 'Student has been successfully graduated! Enrollment is now completed.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to graduate student: ' . $e->getMessage());
            return back()->with('error', 'Unable to graduate student at this time.');
        }
    }

    /**
     * Revoke theoretical passed status (admin only)
     */
    public function revoke(School $school, $enrollment)
    {
        // Manually resolve enrollment to avoid scopeBindings() conflict
        $enrollment = EnrollmentRequest::findOrFail($enrollment);

        // Only admins can revoke
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators can revoke theoretical status.');
        }

        // Verify enrollment belongs to this school
        if ($enrollment->school_id !== $school->id) {
            abort(404);
        }

        $user = Auth::guard('admin')->user();

        if (!$enrollment->student->has_passed_theoretical) {
            return back()->with('error', 'This student has not passed theoretical.');
        }

        // Check if student has active practical enrollments
        $hasPracticalEnrollments = $enrollment->student->enrollments()
            ->whereHas('course', function ($query) {
            $query->where('course_type', 'practical');
        })
            ->where('status', 'approved')
            ->exists();

        if ($hasPracticalEnrollments) {
            return back()->with('error', 'Cannot revoke theoretical status. Student has active practical enrollments.');
        }

        DB::beginTransaction();
        try {
            $enrollment->student->update([
                'has_passed_theoretical' => false,
            ]);

            $enrollment->update([
                'status' => 'approved',
                'completed_at' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Theoretical passed status revoked successfully.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            LogFacade::error('Failed to revoke theoretical status: ' . $e->getMessage(), [
                'enrollment_id' => $enrollment->id,
                'school_id' => $school->id,
                'exception' => $e
            ]);
            return back()->with('error', 'Unable to revoke status at this time. Please try again later.');
        }
    }

    /**
     * Get theoretical completion statistics
     */
    public function stats()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $school = Auth::guard('admin')->user()?->school;

        $passed = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(15, ['*'], 'passed_page');

        $pending = EnrollmentRequest::whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id)
                ->where('course_type', 'theoretical');
        })
            ->whereHas('student', function ($query) {
            $query->where('has_passed_theoretical', false);
        })
            ->where('status', 'approved')
            ->count();

        $passedThisMonth = Student::where('school_id', $school->id)
            ->where('has_passed_theoretical', true)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $stats = [
            'total_passed' => $passed,
            'pending_completion' => $pending,
            'passed_this_month' => $passedThisMonth,
        ];

        return response()->json($stats);
    }
}
