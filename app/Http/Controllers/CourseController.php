<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\School;
use App\Models\Student;
use App\Support\EnrollmentValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index(Request $request, School $school)
    {
        $query = Course::where('school_id', '=', $school->id)
            ->with(['packages', 'modules']);

        // Filtering
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'active');
        }

        if ($request->filled('course_type')) {
            $query->where('course_type', $request->course_type);
        }

        if ($request->filled('license_type')) {
            $query->where('license_type', $request->license_type);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->select('courses.*')
                    ->leftJoinSub(
                        DB::table('course_packages')
                            ->select('course_id', DB::raw('MIN(price) as min_price'))
                            ->groupBy('course_id'),
                        'package_prices',
                        'courses.id', '=', 'package_prices.course_id'
                    )
                    ->orderBy('package_prices.min_price', 'asc');
                break;
            case 'price_high':
                $query->select('courses.*')
                    ->leftJoinSub(
                        DB::table('course_packages')
                            ->select('course_id', DB::raw('MAX(price) as max_price'))
                            ->groupBy('course_id'),
                        'package_prices',
                        'courses.id', '=', 'package_prices.course_id'
                    )
                    ->orderBy('package_prices.max_price', 'desc');
                break;
            case 'popularity':
                $query->withCount(['bookings' => function($q) {
                    $q->whereIn('status', [
                        \App\Models\Booking::STATUS_SCHEDULED,
                        \App\Models\Booking::STATUS_DONE,
                        \App\Models\Booking::STATUS_COMPLETED
                    ]);
                }])->orderBy('bookings_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
        }

        $courses = $query->paginate(12);

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'courses' => $courses
            ]);
        }

        // Determine which view to use based on user role (only admin and student have course views)
        $guard = Auth::guard('admin')->check() ? 'admin' : 'student';
        $view = $guard === 'admin' ? 'admin.courses' : 'student.courses';

        // Get instructors for admin view
        $instructors = [];
        if ($guard === 'admin') {
            $instructors = \App\Models\Instructor::where('school_id', '=', $school->id)->get(['*']);
        }

        // Check if this is an AJAX request
        $isAjax = request()->ajax();

        // For student view, pass enrollment status per course so cards can show badges
        $enrollmentStatuses = [];
        $enrollmentDetails = [];
        if ($guard === 'student') {
            $student = Auth::guard('student')->user();
            if ($student) {
                $requests = \App\Models\EnrollmentRequest::where('learner_id', '=', $student->id)
                    ->where('school_id', '=', $school->id)
                    ->whereIn('status', ['pending', 'approved', 'completed'])
                    ->orderBy('created_at', 'desc')
                    ->get(['course_id', 'status', 'payment_status', 'payment_proof_path']);
                foreach ($requests as $req) {
                    if (array_key_exists($req->course_id, $enrollmentStatuses)) {
                        continue;
                    }

                    $enrollmentStatuses[$req->course_id] = $req->status;
                    $enrollmentDetails[$req->course_id] = [
                        'payment_status' => $req->payment_status,
                        'has_payment_proof' => !empty($req->payment_proof_path),
                    ];
                }
            }
        }

        return view($school->resolveView($view), compact('school', 'courses', 'instructors', 'isAjax', 'enrollmentStatuses', 'enrollmentDetails'));
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request, School $school)
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()?->isSchoolAdmin()) {
            abort(403, 'Only school administrators can create courses.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:max_width=4000,max_height=4000',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'required|string|max:50', // This is the 'Standard/Intensive' type
            'course_type' => 'required|in:theoretical,practical,combo', // This is the category
            'license_type' => 'required|string|max:100',
            'hours_required' => 'required|numeric|min:1',
            'vehicle_type' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
            'is_featured' => 'nullable',
            'schedules' => 'nullable|array',
            'schedules.*.date' => 'required_with:schedules|date',
            'schedules.*.start_time' => 'required_with:schedules',
            'schedules.*.end_time' => 'required_with:schedules',
            'schedules.*.max_students' => 'nullable|integer|min:1',
            'schedules.*.instructor_id' => 'nullable|exists:instructors,id',
            'schedules.*.notes' => 'nullable|string|max:1000',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], fn($f) => !empty(trim($f))));
        }

        $validated['school_id'] = $school->id;
        $course = Course::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'course' => $course
            ], 201);
        }

        return redirect()->route('courses.index', $school->slug)
            ->with('success', 'Course created successfully');
    }

    /**
     * Display the specified course.
     */
    public function show(Request $request, School $school, Course $course)
    {
        // Authorization check 
        if (Auth::guard('admin')->check()) {
            if (!Auth::guard('admin')->user()?->canManageCourses()) {
                abort(403, 'You do not have permission to view courses in the admin dashboard.');
            }
        }

        // Paginate bookings to avoid memory issues with many students
        $bookings = $course->bookings()
            ->with(['student', 'instructor'])
            ->latest()
            ->paginate(10);

        $course->setRelation('bookings', $bookings);
        $course->load(['modules.lessons']);

        // Check if student can enroll (for student view)
        $canEnroll = null;
        $enrollmentValidation = null;

        if (Auth::guard('student')->check()) {
            $student = Auth::guard('student')->user();
            if ($student) {
                $enrollmentValidation = EnrollmentValidator::canEnrollInCourse($student, $course);
                $canEnroll = $enrollmentValidation['allowed'];
            }
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'course' => $course,
                'can_enroll' => $canEnroll,
                'enrollment_message' => $enrollmentValidation['message'] ?? null,
            ]);
        }

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = $guard === 'admin' ? 'admin.course-show' : ($guard === 'student' ? 'student.course-show' : 'instructor.course-show');

        return view($school->resolveView($view), array_merge(compact('school', 'course', 'canEnroll', 'enrollmentValidation'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, School $school, Course $course)
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()?->canManageCourses()) {
            abort(403, 'Only authorized school administrators can update courses.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120|dimensions:max_width=4000,max_height=4000',
            'price' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|numeric|min:0',
            'max_students' => 'nullable|integer|min:1',
            'type' => 'required|string|max:50',
            'course_type' => 'required|in:theoretical,practical,combo',
            'license_type' => 'required|string|max:100',
            'hours_required' => 'required|numeric|min:1',
            'vehicle_type' => 'nullable|string|max:100',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
            'is_featured' => 'nullable',
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }
        else {
            unset($validated['banner_image']);
        }

        // Handle is_featured checkbox
        $validated['is_featured'] = $request->has('is_featured');

        // Filter out empty features
        if (isset($validated['features'])) {
            $validated['features'] = array_values(array_filter($validated['features'], fn($f) => !empty(trim($f))));
        }

        $course->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'course' => $course
            ]);
        }

        return redirect()->route('courses.show', [$school->slug, $course->id])
            ->with('success', 'Course updated successfully');
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Request $request, School $school, Course $course)
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()?->canManageCourses()) {
            abort(403, 'Only authorized school administrators can delete courses.');
        }

        try {
            // Check if course has bookings
            $bookingsCount = $course->bookings()->count();

            if ($bookingsCount > 0) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete course. It has {$bookingsCount} booking(s). Please delete or reassign bookings first."
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', "Cannot delete course. It has {$bookingsCount} booking(s).");
            }

            // Delete related records first
            $course->progresses()->delete();

            // Now delete the course
            $course->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Course deleted successfully'
                ]);
            }

            return redirect()->route('courses.index', $school->slug)
                ->with('success', 'Course deleted successfully');

        }
        catch (\Exception $e) {
            Log::error('Course deletion error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to delete course at this time.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error deleting course. Please try again.');
        }
    }
}
