<?php

namespace App\Http\Controllers;

use App\Models\SessionCompletion;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\PhaseProgression;
use App\Models\School;
use App\Models\Notification;
use App\Http\Requests\StoreSessionCompletionRequest;
use App\Services\SchedulingConflictService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SessionCompletionController extends Controller
{
    /**
     * Display a listing of session completions
     */
    public function index(School $school, Request $request)
    {
        // Instructor view
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();

            $sessionsQuery = SessionCompletion::with(['enrollment.student', 'enrollment.course'])
                ->where('instructor_id', $instructor->id)
                ->whereHas('enrollment.course', function ($query) use ($school) {
                $query->where('school_id', $school->id);
            })
                ->when($request->session_type, function ($query, $type) {
                $query->where('session_type', $type);
            })
                ->when($request->date_from, function ($query, $date) {
                $query->whereDate('session_date', '>=', $date);
            })
                ->when($request->date_to, function ($query, $date) {
                $query->whereDate('session_date', '<=', $date);
            })
                ->latest('session_date')
                ->latest('session_time');

            // Pre-compute statistics before pagination
            $stats = [
                'total_sessions' => (clone $sessionsQuery)->count(),
                'total_hours' => (clone $sessionsQuery)->sum('hours_completed'),
                'theoretical_count' => (clone $sessionsQuery)->where('session_type', 'theoretical')->count(),
                'practical_count' => (clone $sessionsQuery)->where('session_type', 'practical')->count(),
            ];

            $sessions = $sessionsQuery->paginate(10);

            return view('school.instructor.sessions.index', compact('school', 'sessions', 'stats'))->with('isAjax', $request->ajax());
        }

        // Admin view
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            $baseSessionsQuery = SessionCompletion::with(['enrollment.student', 'enrollment.course', 'instructor'])
                ->whereHas('enrollment.course', function ($query) use ($school) {
                $query->where('school_id', $school->id);
            })
                ->when($request->instructor_id, function ($query, $instructorId) {
                $query->where('instructor_id', $instructorId);
            })
                ->when($request->date_from, function ($query, $date) {
                $query->whereDate('session_date', '>=', $date);
            })
                ->when($request->date_to, function ($query, $date) {
                $query->whereDate('session_date', '<=', $date);
            })
                ->latest('session_date')
                ->latest('session_time');

            // Branch secretary scope: filter by their branch
            if ($admin->isBranchSecretary() && $admin->branch_id) {
                $baseSessionsQuery->whereHas('enrollment', function ($query) use ($admin) {
                    $query->where('branch_id', $admin->branch_id);
                });
            }

            // Pre-compute statistics before pagination
            $stats = [
                'total_sessions' => (clone $baseSessionsQuery)->count(),
                'total_hours' => (clone $baseSessionsQuery)->sum('hours_completed'),
                'theoretical_count' => (clone $baseSessionsQuery)->where('session_type', 'theoretical')->count(),
                'practical_count' => (clone $baseSessionsQuery)->where('session_type', 'practical')->count(),
            ];

            $sessionsQuery = (clone $baseSessionsQuery)
                ->when($request->session_type, function ($query, $type) {
                    $query->where('session_type', $type);
                });

            $sessions = $sessionsQuery->paginate(10)->withQueryString();

            // Get instructors (branch secretaries only see instructors in their branch)
            $instructorQuery = Instructor::where('school_id', $school->id);
            $admin->scopeToBranch($instructorQuery);
            $instructors = $instructorQuery->get();

            $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';

            return view('school.admin.sessions.index', compact('school', 'sessions', 'instructors', 'stats', 'isAjax'));
        }

        abort(403);
    }

    /**
     * Show the form for creating a new session completion
     */
    public function create(School $school, Request $request)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }

        $instructor = Auth::guard('instructor')->user();

        // Get active enrollments for this school
        $enrollments = EnrollmentRequest::with(['learner', 'course', 'sessionCompletions'])
            ->whereHas('course', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })
            ->where('status', 'approved')
            ->get();

        // Compute total hours for each enrollment (used in the view)
        $enrollments->each(function ($enrollment) {
            $enrollment->total_hours = $enrollment->sessionCompletions->sum('hours_completed');
        });

        // Pre-select enrollment if provided
        $selectedEnrollment = $request->enrollment_id
            ?EnrollmentRequest::find($request->enrollment_id)
            : null;

        return view('school.instructor.sessions.create', compact('school', 'enrollments', 'selectedEnrollment'))->with('isAjax', $request->ajax());
    }

    /**
     * Store a newly created session completion
     */
    public function store(School $school, StoreSessionCompletionRequest $request)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }

        $instructor = Auth::guard('instructor')->user();

        // Verify enrollment belongs to this school
        $enrollment = EnrollmentRequest::findOrFail($request->enrollment_id);
        if ($enrollment->course->school_id !== $school->id) {
            abort(404);
        }

        // Calculate start and end times if provided, or use session_time and hours_completed
        $startTime = $request->start_time ?? $request->session_time;
        $endTime = $request->end_time;

        if (!$endTime && $request->hours_completed) {
            // Calculate end time based on hours_completed
            $endTime = Carbon::parse($startTime)->addHours($request->hours_completed)->format('H:i');
        }

        // Check for scheduling conflicts if we have start and end times
        if ($startTime && $endTime) {
            $conflictService = new SchedulingConflictService();
            $check = $conflictService->checkInstructorAvailability(
                $instructor->id,
                $request->session_date,
                $startTime,
                $endTime
            );

            if (!$check['available']) {
                return back()
                    ->withInput()
                    ->with('error', 'Scheduling Conflict: ' . $check['message'])
                    ->with('conflicts', $check['conflicts']);
            }
        }

        DB::beginTransaction();
        try {
            $sessionCompletion = SessionCompletion::create([
                'school_id' => $school->id,
                'enrollment_id' => $request->enrollment_id,
                'instructor_id' => $instructor->id,
                'session_type' => $request->session_type,
                'hours_completed' => $request->hours_completed,
                'session_date' => $request->session_date,
                'session_time' => $request->session_time,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => $request->status ?? 'completed',
                'notes' => $request->notes,
                'logged_by' => $instructor->id,
            ]);

            // Auto-progression trigger: check if hours requirement is met
            $this->checkAndTriggerProgression($enrollment, $school);

            // Notify student about new session completion
            if ($enrollment->learner) {
                try {
                    Notification::send(
                        $enrollment->learner,
                        'session_completed',
                        'Session Logged',
                        "{$request->hours_completed} hour(s) of {$request->session_type} training logged by {$instructor->first_name} {$instructor->last_name}.",
                        'session',
                        "/{$school->slug}/student/my-course"
                    );
                }
                catch (\Exception $e) {
                    Log::warning('Failed to send session completion notification: ' . $e->getMessage());
                }
            }

            DB::commit();

            return redirect()
                ->route('schools.instructor.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session logged successfully.');

        }
        catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to log session: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified session completion
     */
    public function show(Request $request, School $school, $sessionCompletion)
    {
        // Fetch session manually
        $sessionCompletion = SessionCompletion::with([
            'enrollment.student',
            'enrollment.course',
            'instructor'
        ])->findOrFail($sessionCompletion);

        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }

        // Instructor view - only their sessions
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            if ($sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
            return view('school.instructor.sessions.show', compact('school', 'sessionCompletion'))->with('isAjax', $request->ajax());
        }

        // Admin view
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            
            // Branch secretary can only view sessions from their branch
            if ($admin->isBranchSecretary() && $admin->branch_id) {
                if ($sessionCompletion->enrollment->branch_id !== $admin->branch_id) {
                    abort(403, 'You do not have access to this session.');
                }
            }
            
            $isAjax = request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest';
            
            return view('school.admin.sessions.show', compact('school', 'sessionCompletion', 'isAjax'));
        }

        abort(403);
    }

    /**
     * Show the form for editing the specified session completion
     */
    public function edit(Request $request, School $school, $sessionCompletion)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }

        $instructor = Auth::guard('instructor')->user();

        // Fetch session manually
        $sessionCompletion = SessionCompletion::with(['enrollment.student', 'enrollment.course'])
            ->findOrFail($sessionCompletion);

        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }

        // Only the instructor who logged the session can edit it
        if ($sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }

        return view('school.instructor.sessions.edit', compact('school', 'sessionCompletion'))->with('isAjax', $request->ajax());
    }

    /**
     * Update the specified session completion
     */
    public function update(School $school, StoreSessionCompletionRequest $request, $sessionCompletion)
    {
        if (!Auth::guard('instructor')->check()) {
            abort(403);
        }

        $instructor = Auth::guard('instructor')->user();

        // Fetch session manually
        $sessionCompletion = SessionCompletion::findOrFail($sessionCompletion);

        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }

        // Only the instructor who logged the session can edit it
        if ($sessionCompletion->instructor_id !== $instructor->id) {
            abort(403);
        }

        // Calculate start and end times if provided, or use session_time and hours_completed
        $startTime = $request->start_time ?? $request->session_time;
        $endTime = $request->end_time;

        if (!$endTime && $request->hours_completed) {
            // Calculate end time based on hours_completed
            $endTime = Carbon::parse($startTime)->addHours($request->hours_completed)->format('H:i');
        }

        // Check for scheduling conflicts if we have start and end times
        if ($startTime && $endTime) {
            $conflictService = new SchedulingConflictService();
            $check = $conflictService->checkInstructorAvailability(
                $instructor->id,
                $request->session_date,
                $startTime,
                $endTime,
                $sessionCompletion->id // Exclude current session from conflict check
            );

            if (!$check['available']) {
                return back()
                    ->withInput()
                    ->with('error', 'Scheduling Conflict: ' . $check['message'])
                    ->with('conflicts', $check['conflicts']);
            }
        }

        $sessionCompletion->update([
            'hours_completed' => $request->hours_completed,
            'session_date' => $request->session_date,
            'session_time' => $request->session_time,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => $request->status ?? $sessionCompletion->status ?? 'completed',
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('schools.instructor.sessions.show', ['school' => $school->slug, 'sessionCompletion' => $sessionCompletion->id])
            ->with('success', 'Session updated successfully.');
    }

    /**
     * Remove the specified session completion
     */
    public function destroy(School $school, $sessionCompletion)
    {
        // Fetch session manually
        $sessionCompletion = SessionCompletion::findOrFail($sessionCompletion);

        // Verify belongs to school
        if ($sessionCompletion->enrollment->course->school_id !== $school->id) {
            abort(404);
        }

        // Admin can delete any session
        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();

            // Branch secretary can only delete sessions from their branch
            if ($admin->isBranchSecretary() && !$admin->canAccessBranch($sessionCompletion->enrollment?->branch_id)) {
                abort(403, 'You do not have access to delete this session.');
            }

            $sessionCompletion->delete();
            return redirect()
                ->route('schools.admin.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session deleted successfully.');
        }

        // Instructor can delete their own sessions
        if (Auth::guard('instructor')->check()) {
            $instructor = Auth::guard('instructor')->user();
            if ($sessionCompletion->instructor_id !== $instructor->id) {
                abort(403);
            }
            $sessionCompletion->delete();
            return redirect()
                ->route('schools.instructor.sessions.index', ['school' => $school->slug])
                ->with('success', 'Session deleted successfully.');
        }

        abort(403);
    }

    /**
     * Get session statistics for an enrollment
     */
    public function enrollmentStats(School $school, $enrollment)
    {
        // Manually resolve enrollment to avoid scopeBindings() conflict
        $enrollment = EnrollmentRequest::findOrFail($enrollment);

        // Verify enrollment belongs to this school
        if ($enrollment->school_id !== $school->id) {
            abort(404);
        }

        $stats = [
            'total_sessions' => $enrollment->sessionCompletions()->count(),
            'total_hours' => $enrollment->sessionCompletions()->sum('hours_completed'),
            'recent_sessions' => $enrollment->sessionCompletions()
            ->with(['instructor'])
            ->latest('session_date')
            ->take(5)
            ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Check if hours requirement is met and trigger a phase progression request
     */
    private function checkAndTriggerProgression(EnrollmentRequest $enrollmentRequest, School $school): void
    {
        $course = $enrollmentRequest->course;
        if (!$course || !$course->hours_required || $course->hours_required <= 0) {
            return;
        }

        // Calculate total hours from all completed sessions
        $totalHours = $enrollmentRequest->sessionCompletions()
            ->where('status', 'completed')
            ->sum('hours_completed');

        // Check if hours requirement is met
        if ($totalHours < $course->hours_required) {
            return;
        }

        // Determine the phase transition based on course type
        $fromPhase = $course->isTheoretical() ? 'theoretical' : 'practical';
        $toPhase = $course->isTheoretical() ? 'practical' : 'completed';

        // Don't create duplicate pending requests
        $existingRequest = PhaseProgression::where('enrollment_id', $enrollmentRequest->id)
            ->where('from_phase', $fromPhase)
            ->where('to_phase', $toPhase)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return;
        }

        PhaseProgression::create([
            'enrollment_id' => $enrollmentRequest->id,
            'school_id' => $school->id,
            'from_phase' => $fromPhase,
            'to_phase' => $toPhase,
            'requested_at' => now(),
            'status' => 'pending',
        ]);

        // Notify school admins about the pending progression request
        $admins = \App\Models\Admin::where('school_id', $school->id)->get();
        foreach ($admins as $admin) {
            try {
                Notification::send(
                    $admin,
                    'phase_progression_pending',
                    'Phase Progression Request',
                    "Student {$enrollmentRequest->learner->first_name} {$enrollmentRequest->learner->last_name} has completed {$fromPhase} hours and is ready for {$toPhase} phase.",
                    'enrollment',
                    "/{$school->slug}/admin/phase-progressions"
                );
            }
            catch (\Exception $e) {
                Log::warning('Failed to send progression notification to admin: ' . $e->getMessage());
            }
        }

        Log::info("Phase progression triggered: Enrollment #{$enrollmentRequest->id} ({$fromPhase} → {$toPhase}), Total hours: {$totalHours}/{$course->hours_required}");
    }
}
