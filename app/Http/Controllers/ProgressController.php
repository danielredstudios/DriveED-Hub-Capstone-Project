<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Progress;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Display a listing of progress records.
     */
    public function index(Request $request, School $school)
    {
        $query = Progress::where('school_id', '=', $school->id)
            ->with(['student', 'course']);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $query->where('student_id', Auth::guard('student')->id());
        }
        elseif (Auth::guard('instructor')->check()) {
            // Instructors see progress for students they have bookings with
            $instructorId = Auth::guard('instructor')->id();
            $studentIds = \App\Models\Booking::where('school_id', '=', $school->id)
                ->where('instructor_id', '=', $instructorId)
                ->distinct()
                ->pluck('student_id', 'id');
            $query->whereIn('student_id', $studentIds, 'and', false);
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin->isBranchSecretary() && $admin->branch_id) {
                $query->whereHas('student', function ($q) use ($admin) {
                    $q->where('branch_id', $admin->branch_id);
                });
            }
        }

        if (request('student_id')) {
            $query->where('student_id', '=', request('student_id'));
        }

        if (request('course_id')) {
            $query->where('course_id', '=', request('course_id'));
        }

        $progresses = $query->latest('last_updated')->paginate(10);

        // Pre-load booking data for each progress record (avoids N+1 in views)
        $allStudentIds = $progresses->pluck('student_id')->unique()->toArray();
        $allCourseIds = $progresses->pluck('course_id')->unique()->toArray();

        $allBookings = Booking::where('school_id', '=', $school->id)
            ->whereIn('student_id', $allStudentIds, 'and', false)
            ->whereIn('course_id', $allCourseIds, 'and', false)
            ->with(['instructor', 'course'])
            ->orderBy('scheduled_at', 'desc')
            ->get(['*']);

        // Attach computed booking data to each progress record
        foreach ($progresses as $progress) {
            $bookings = $allBookings->where('student_id', $progress->student_id)
                ->where('course_id', $progress->course_id);

            $progress->completedSessions = $bookings->where('status', 'confirmed')->count();
            $progress->totalSessions = ceil($progress->course->duration_hours ?? 10);
            $progress->currentBooking = $bookings->where('status', 'confirmed')->sortByDesc('scheduled_at')->first() ?? $bookings->first();
            $progress->nextBooking = $bookings->where('status', 'pending')->filter(fn($b) => $b->scheduled_at > now())->sortBy('scheduled_at')->first();
            $progress->bookingsList = $bookings;
        }

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'progresses' => $progresses
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.progress";
        return view($school->resolveView($view), array_merge(compact('school', 'progresses'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the form for creating/updating progress.
     */
    public function create(School $school, Request $request)
    {
        $studentId = $request->query('student_id');
        $courseId = $request->query('course_id');

        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        $courses = Course::where('school_id', $school->id)->where('status', 'active')->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : 'instructor';
        $view = "{$guard}.progress-create";
        return view($school->resolveView($view), array_merge(compact('school', 'students', 'courses', 'studentId', 'courseId'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Store or update progress.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('students', 'id')->where('school_id', $school->id)
            ],
            'course_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('courses', 'id')->where('school_id', $school->id)
            ],
            'completion_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Authorization check
        if (Auth::guard('instructor')->check()) {
            $isAssigned = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', Auth::guard('instructor')->id())
                ->where('student_id', $validated['student_id'])
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You can only update progress for students assigned to you.');
            }
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $student = Student::where('id', $validated['student_id'])->where('school_id', $school->id)->firstOrFail();

            if ($admin->isBranchSecretary() && $admin->branch_id && (int)$student->branch_id !== (int)$admin->branch_id) {
                abort(403, 'You can only update progress for students in your branch.');
            }
        }
        else {
            abort(403, 'Only administrators or assigned instructors can create progress.');
        }

        $validated['school_id'] = $school->id;
        $validated['last_updated'] = now();

        // Update existing progress or create new
        $progress = Progress::updateOrCreate(
        [
            'school_id' => $school->id,
            'student_id' => $validated['student_id'],
            'course_id' => $validated['course_id'],
        ],
            $validated
        );

        $progress->load(['student', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'progress' => $progress
            ], 201);
        }

        return redirect()->route('schools.instructor.progress.show', ['school' => $school->slug, 'progress' => $progress->id])
            ->with('success', 'Progress updated successfully');
    }

    /**
     * Display the specified progress.
     */
    public function show(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        // Authorization check
        if (Auth::guard('student')->check()) {
            if ($progress->student_id !== Auth::guard('student')->id()) {
                abort(403, 'You can only view your own progress.');
            }
        }
        elseif (Auth::guard('instructor')->check()) {
            $isAssigned = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', Auth::guard('instructor')->id())
                ->where('student_id', $progress->student_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You can only view progress for students assigned to you.');
            }
        }
        elseif (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin->isBranchSecretary() && $admin->branch_id) {
                $student = $progress->student;
                if ($student && (int)$student->branch_id !== (int)$admin->branch_id) {
                    abort(403, 'You do not have access to students in other branches.');
                }
            }
        }
        else {
            abort(403);
        }

        $progress->load(['student', 'course']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'progress' => $progress
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.progress-show";
        return view($school->resolveView($view), array_merge(compact('school', 'progress'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the form for editing the specified progress.
     */
    public function edit(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        // Authorization check
        if (Auth::guard('instructor')->check()) {
            $isAssigned = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', Auth::guard('instructor')->id())
                ->where('student_id', $progress->student_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You can only edit progress for students assigned to you.');
            }
        }
        elseif (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators or assigned instructors can edit progress.');
        }

        $students = Student::where('school_id', $school->id)->where('status', 'active')->get();
        $courses = Course::where('school_id', $school->id)->where('status', 'active')->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : 'instructor';
        $view = "{$guard}.progress-edit";
        return view($school->resolveView($view), array_merge(compact('school', 'progress', 'students', 'courses'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Update the specified progress.
     */
    public function update(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        // Authorization check
        if (Auth::guard('instructor')->check()) {
            $isAssigned = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', Auth::guard('instructor')->id())
                ->where('student_id', $progress->student_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You can only update progress for students assigned to you.');
            }
        }
        elseif (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators or assigned instructors can update progress.');
        }

        $validated = $request->validate([
            'completion_percent' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['last_updated'] = now();
        $progress->update($validated);
        $progress->load(['student', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'progress' => $progress
            ]);
        }

        return redirect()->route('schools.instructor.progress.show', ['school' => $school->slug, 'progress' => $progress->id])
            ->with('success', 'Progress updated successfully');
    }

    /**
     * Remove the specified progress.
     */
    public function destroy(Request $request, School $school, Progress $progress)
    {
        abort_if($progress->school_id !== $school->id, 404);

        // Only admins or assigned instructors can delete progress records
        if (Auth::guard('instructor')->check()) {
            $isAssigned = \App\Models\Booking::where('school_id', $school->id)
                ->where('instructor_id', Auth::guard('instructor')->id())
                ->where('student_id', $progress->student_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'You can only delete progress for students assigned to you.');
            }
        }
        elseif (!Auth::guard('admin')->check()) {
            abort(403, 'Only administrators or assigned instructors can delete progress records.');
        }

        $progress->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress deleted successfully'
            ]);
        }

        return redirect()->route('schools.instructor.progress.index', ['school' => $school->slug])
            ->with('success', 'Progress deleted successfully');
    }

}
