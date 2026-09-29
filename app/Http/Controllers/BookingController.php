<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\School;
use App\Models\Student;
use App\Models\Branch;
use App\Models\SystemLog;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $query = Booking::where('bookings.school_id', $school->id)
            ->with([
                'student:id,name,email,contact,branch_id,has_passed_theoretical,theoretical_passed_at',
                'instructor:id,name,email',
                'course:id,title,duration_hours,price,course_type',
                'package:id,course_id,name,price,training_hours,transmission_type',
                'timeSlot:id,date,start_time,end_time,session_type',
                'enrollmentRequest:id,learner_id,course_id,package_id,status,completed_at'
            ]);

        // Base query for statistics cards (before status/date filters)
        $statsQuery = Booking::where('bookings.school_id', $school->id);

        // Filter by role
        if (Auth::guard('student')->check()) {
            $query->where('bookings.student_id', Auth::guard('student')->id());
            $statsQuery->where('bookings.student_id', Auth::guard('student')->id());
        } elseif (Auth::guard('instructor')->check()) {
            $query->where('bookings.instructor_id', Auth::guard('instructor')->id());
            $statsQuery->where('bookings.instructor_id', Auth::guard('instructor')->id());
        }

        // Tab-based filtering
        $activeTab = $request->query('tab', 'verify');
        $activeFilter = (string) $request->query('status', 'all');

        if ($activeTab === 'verify') {
            // Default to 'all' if no status is provided, to show everything as requested by the user.
            if (!request('status') || $activeFilter === 'all') {
                $activeFilter = 'all'; 
                // No additional where clauses, show all statuses for this school/role.
            } elseif ($activeFilter === 'done') {
                $query->whereIn('bookings.status', ['done', 'no-show', 'no_show']);
            } elseif ($activeFilter === 'flagged') {
                $query->whereIn('bookings.status', ['cancelled', 'no_show', 'no-show']);
            } else {
                $query->where('bookings.status', $activeFilter);
            }
        }

        // Additional filters (server-side so pagination and filters stay in sync)
        if ($activeFilter !== '' && $activeFilter !== 'all' && $activeFilter !== 'done' && $activeFilter !== 'flagged') {
            $query->where('bookings.status', $activeFilter);
        }

        // Fetch instructors and branches for filters
        $instructors = Instructor::where('school_id', $school->id)->orderBy('name')->get();
        $branches = Branch::where('school_id', $school->id)->orderBy('name')->get();

        // Additional date and branch filters
        if ($request->filled('branch_id')) {
            $query->where('bookings.branch_id', $request->branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('bookings.booking_date', '>=', $request->date_from)
                ->orWhereDate('bookings.scheduled_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('bookings.booking_date', '<=', $request->date_to)
                ->orWhereDate('bookings.scheduled_at', '<=', $request->date_to);
        }
        if ($request->filled('instructor_id')) {
            $query->where('bookings.instructor_id', $request->instructor_id);
        }

        // Search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhereHas('instructor', function($iq) use ($search) {
                    $iq->where('name', 'like', "%{$search}%");
                })->orWhereHas('course', function($cq) use ($search) {
                    $cq->where('title', 'like', "%{$search}%");
                });
            });
        }

        $activeSort = (string) $request->query('sort', 'audit_priority');
        $allowedSorts = [
            'audit_priority', 'session_newest', 'session_oldest', 'recently_updated',
            'student_az', 'student_za', 'instructor_az', 'instructor_za',
            'branch_az', 'branch_za', 'course_az', 'course_za',
            'type_az', 'type_za', 'hours_high', 'hours_low',
            'status_az', 'status_za', 'time_early', 'time_late'
        ];
        
        if (!in_array($activeSort, $allowedSorts, true)) {
            $activeSort = 'audit_priority';
        }

        if (request('upcoming')) {
            $query->upcoming();
        } elseif (request('past')) {
            $query->past();
        }

        // Always join sort_slot on this page to handle date/time fallbacks reliably
        $query->leftJoin('time_slots as sort_slot', 'sort_slot.id', '=', 'bookings.time_slot_id')
              ->select('bookings.*');

        $sessionDateExpr = "COALESCE(sort_slot.date, DATE(bookings.booking_date), DATE(bookings.scheduled_at))";
        $sessionTimeExpr = "COALESCE(sort_slot.start_time, TIME(bookings.booking_date), TIME(bookings.scheduled_at))";

        switch ($activeSort) {
            case 'session_newest':
                $query->orderByRaw("{$sessionDateExpr} DESC")
                    ->orderByRaw("{$sessionTimeExpr} DESC")
                    ->orderByDesc('bookings.id');
                break;
            case 'session_oldest':
                $query->orderByRaw("{$sessionDateExpr} ASC")
                    ->orderByRaw("{$sessionTimeExpr} ASC")
                    ->orderBy('bookings.id');
                break;
            case 'time_early':
                $query->orderByRaw("{$sessionTimeExpr} ASC")
                    ->orderByRaw("{$sessionDateExpr} DESC")
                    ->orderBy('bookings.id');
                break;
            case 'time_late':
                $query->orderByRaw("{$sessionTimeExpr} DESC")
                    ->orderByRaw("{$sessionDateExpr} DESC")
                    ->orderBy('bookings.id');
                break;
            case 'instructor_az':
                $query->orderBy(
                    Instructor::select('name')
                        ->whereColumn('instructors.id', 'bookings.instructor_id')
                        ->limit(1),
                    'asc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'instructor_za':
                $query->orderBy(
                    Instructor::select('name')
                        ->whereColumn('instructors.id', 'bookings.instructor_id')
                        ->limit(1),
                    'desc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'student_az':
                $query->orderBy(
                    Student::select('name')
                        ->whereColumn('students.id', 'bookings.student_id')
                        ->limit(1),
                    'asc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'student_za':
                $query->orderBy(
                    Student::select('name')
                        ->whereColumn('students.id', 'bookings.student_id')
                        ->limit(1),
                    'desc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'branch_az':
                $query->orderBy(
                    Branch::select('name')
                        ->whereColumn('branches.id', 'bookings.branch_id')
                        ->limit(1),
                    'asc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'branch_za':
                $query->orderBy(
                    Branch::select('name')
                        ->whereColumn('branches.id', 'bookings.branch_id')
                        ->limit(1),
                    'desc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'course_az':
                $query->orderBy(
                    Course::select('title')
                        ->whereColumn('courses.id', 'bookings.course_id')
                        ->limit(1),
                    'asc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'course_za':
                $query->orderBy(
                    Course::select('title')
                        ->whereColumn('courses.id', 'bookings.course_id')
                        ->limit(1),
                    'desc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'type_az':
                $query->orderBy(
                    \App\Models\TimeSlot::select('session_type')
                        ->whereColumn('time_slots.id', 'bookings.time_slot_id')
                        ->limit(1),
                    'asc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'type_za':
                $query->orderBy(
                    \App\Models\TimeSlot::select('session_type')
                        ->whereColumn('time_slots.id', 'bookings.time_slot_id')
                        ->limit(1),
                    'desc'
                )->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'hours_high':
                $query->orderByRaw("(TIMESTAMPDIFF(MINUTE, sort_slot.start_time, sort_slot.end_time) / 60) DESC")
                    ->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'hours_low':
                $query->orderByRaw("(TIMESTAMPDIFF(MINUTE, sort_slot.start_time, sort_slot.end_time) / 60) ASC")
                    ->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'status_az':
                $query->orderBy('bookings.status', 'asc')->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'status_za':
                $query->orderBy('bookings.status', 'desc')->orderByRaw("{$sessionDateExpr} DESC");
                break;
            case 'recently_updated':
                $query->orderByDesc('bookings.updated_at')->orderByDesc('bookings.id');
                break;
            case 'audit_priority':
            default:
                $query->orderByRaw("CASE
                        WHEN bookings.status = 'done' THEN 0
                        WHEN bookings.status = 'no-show' THEN 1
                        WHEN bookings.status = 'no_show' THEN 1
                        WHEN bookings.status = 'scheduled' THEN 2
                        WHEN bookings.status = 'completed' THEN 3
                        WHEN bookings.status = 'cancelled' THEN 4
                        ELSE 5
                    END ASC")
                    ->orderByRaw("{$sessionDateExpr} DESC")
                    ->orderByRaw("{$sessionTimeExpr} DESC")
                    ->orderByDesc('bookings.id');
                break;
        }

        $bookings = $query
            ->paginate(15)
            ->withQueryString();

        // Calculate consolidated counts for the focused 5-card verification view
        $allSessionsCount = (clone $statsQuery)->count();
        $pendingRequestsCount = (clone $statsQuery)->where('status', 'pending')->count();
        $awaitingVerificationCount = (clone $statsQuery)->whereIn('status', ['done', 'no-show', 'no_show'])->count();
        $verifiedSessionsCount = (clone $statsQuery)->where('status', 'completed')->count();
        $flaggedIssuesCount = (clone $statsQuery)->where('status', 'cancelled')->count();

        $stats = [
            'all' => $allSessionsCount,
            'pending' => $pendingRequestsCount,
            'done' => $awaitingVerificationCount,
            'completed' => $verifiedSessionsCount,
            'flagged' => $flaggedIssuesCount,
        ];

        // Only return JSON if explicitly requested via Accept header
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookings' => $bookings
            ]);
        }

        // Only admin has bookings list view
        $view = 'admin.verify-session-completion';
        return view($school->resolveView($view), array_merge(compact('school', 'bookings', 'stats', 'allSessionsCount', 'pendingRequestsCount', 'awaitingVerificationCount', 'verifiedSessionsCount', 'flaggedIssuesCount', 'activeFilter', 'activeSort', 'activeTab', 'instructors', 'branches'), ['isAjax' => $request->ajax()]));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request, School $school)
    {
        // Get parameters from request
        $timeSlotId = request('time_slot_id');
        $instructorParam = request('instructor');
        $instructorId = request('instructor_id');

        // Load time slot if provided
        $timeSlot = null;
        if ($timeSlotId) {
            $timeSlot = \App\Models\TimeSlot::with('course', 'instructors')->find($timeSlotId);
        }

        // Select only necessary columns for dropdown lists
        $courses = Course::where('school_id', $school->id)
            ->where('status', 'active')
            ->select('id', 'title', 'duration_hours', 'price', 'description')
            ->orderBy('title')
            ->get();

        $instructors = Instructor::where('school_id', $school->id)
            ->where('status', 'active')
            ->where('availability', 'available')
            ->select('id', 'name', 'email', 'availability')
            ->orderBy('name')
            ->get();

        $students = Student::where('school_id', $school->id)
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'contact')
            ->orderBy('name')
            ->get();

        $guard = Auth::guard('admin')->check() ? 'admin' : (Auth::guard('student')->check() ? 'student' : 'instructor');
        $view = "{$guard}.booking-create";

        return view($school->resolveView($view), array_merge(compact(
            'school',
            'courses',
            'instructors',
            'students',
            'timeSlot',
            'instructorParam',
            'instructorId'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $school->id)
            ],
            'enrollment_request_id' => [
                'required',
                Rule::exists('enrollment_requests', 'id')->where(function ($query) use ($school, $request) {
                    $query->where('school_id', $school->id)
                        ->where('learner_id', $request->input('student_id'))
                        ->where('status', 'approved');
                })
            ],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('school_id', $school->id)
            ],
            'instructor_id' => [
                'nullable',
                Rule::exists('instructors', 'id')->where('school_id', $school->id)
            ],
            'time_slot_id' => [
                'nullable',
                Rule::exists('time_slots', 'id')->where('school_id', $school->id)
            ],
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
        ]);

        // Layer 2: Fail-Closed Retrieval
        $student = $school->students()->findOrFail($validated['student_id']);
        $course = $school->courses()->findOrFail($validated['course_id']);

        if (Auth::guard('student')->check() && (int) Auth::guard('student')->id() !== (int) $validated['student_id']) {
            abort(403, 'Unauthorized booking attempt for another student.');
        }

        if (!empty($validated['instructor_id'])) {
            $instructor = $school->instructors()->findOrFail($validated['instructor_id']);
            
            if (!$instructor->canTeach($course)) {
                $message = "Instructor {$instructor->name} is not accredited to teach this course.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['instructor_id' => $message]);
            }
        }

        if (!empty($validated['time_slot_id'])) {
            $timeSlot = $school->timeSlots()->findOrFail($validated['time_slot_id']);
        }

        $validated['school_id'] = $school->id;

        // Get school settings and timezone with defensive fallback
        $settings = $school->schoolSetting;
        $schoolTimezone = $school->timezone;

        // Defensive: Validate timezone source to avoid Carbon exceptions
        try {
            if (!$schoolTimezone || !in_array($schoolTimezone, \DateTimeZone::listIdentifiers())) {
                $schoolTimezone = config('app.timezone', 'UTC');
            }
        } catch (\Exception $e) {
            $schoolTimezone = 'UTC';
        }

        $now = \Carbon\Carbon::now($schoolTimezone);
        $queueEnabled = $settings?->enable_booking_queue ?? true;
        $bookingCutoffHours = $settings?->booking_cutoff_hours ?? 0;
        $advanceBookingDays = $settings?->advance_booking_days ?? 0;

        // Combine date/time and enforce cutoff + advance booking
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at, $schoolTimezone);

        // 1. Hard Cutoff Check (Hours)
        if ($now->copy()->addHours($bookingCutoffHours)->gt($scheduledAt)) {
            $message = "Bookings must be made at least {$bookingCutoffHours} hour(s) before the scheduled time.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['scheduled_at' => $message]);
        }

        // 2. Advance Booking Check (Days)
        $scheduledDate = $scheduledAt->copy()->startOfDay();
        $minBookingDate = $now->copy()->addDays($advanceBookingDays)->startOfDay();

        if ($scheduledDate->lt($minBookingDate)) {
            $message = $advanceBookingDays > 0
                ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                : "Cannot schedule in the past.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['scheduled_at' => $message]);
        }

        // Set initial status based on queue setting
        $validated['status'] = $queueEnabled ? 'pending' : 'scheduled';

        // Set booking_date
        $validated['booking_date'] = $validated['scheduled_at'];

        // If time_slot_id is provided, get the date from the time slot
        if (!empty($validated['time_slot_id'])) {
            $timeSlot = \App\Models\TimeSlot::find($validated['time_slot_id']);
            if ($timeSlot) {
                // Combine time slot date and start time for precise cutoff check
                $slotStartTime = \Carbon\Carbon::parse($timeSlot->date->format('Y-m-d') . ' ' . $timeSlot->start_time, $schoolTimezone);

                // 1. Hard Cutoff Check (Hours) for Time Slot
                if ($now->copy()->addHours($bookingCutoffHours)->gt($slotStartTime)) {
                    $message = "This time slot is now closed for bookings (cutoff: {$bookingCutoffHours} hour(s)).";
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return back()->withErrors(['time_slot' => $message]);
                }

                // 2. Advance Booking Check (Days) for Time Slot
                $slotDate = $slotStartTime->copy()->startOfDay();
                if ($slotDate->lt($minBookingDate)) {
                    $message = $advanceBookingDays > 0
                        ? "Schedules must be made at least {$advanceBookingDays} day(s) in advance."
                        : "Cannot schedule in the past.";

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }
                    return back()->withErrors(['time_slot' => $message]);
                }

                $validated['booking_date'] = $timeSlot->date;

                // Override course_id with time slot's course if available
                if ($timeSlot->course_id) {
                    $validated['course_id'] = $timeSlot->course_id;
                }

                // Check if time slot is still available
                if ($timeSlot->status !== 'open') {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This time slot is no longer available.'
                        ], 422);
                    }
                    return back()->withErrors(['time_slot' => 'This time slot is no longer available.']);
                }

                // Check for conflicts with existing bookings
                $conflict = \App\Models\Booking::where('student_id', $validated['student_id'])
                    ->where('time_slot_id', $validated['time_slot_id'])
                    ->whereIn('status', ['pending', 'scheduled', 'confirmed'])
                    ->exists();

                if ($conflict) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You already have a schedule for this time slot.'
                        ], 422);
                    }
                    return back()->withErrors(['time_slot' => 'You already have a schedule for this time slot.']);
                }

                $slotSessionType = $timeSlot->session_type;
                if (!in_array($slotSessionType, ['theoretical', 'practical'], true)) {
                    $slotSessionType = ($timeSlot->course?->course_type === 'practical') ? 'practical' : 'theoretical';
                }

                $activeSlotStatuses = ['pending', 'scheduled', 'confirmed', 'done', 'completed'];
                $slotBookingsCount = \App\Models\Booking::where('time_slot_id', $timeSlot->id)
                    ->whereIn('status', $activeSlotStatuses)
                    ->count();

                $assignedInstructors = $timeSlot->instructors;

                if ($slotSessionType === 'theoretical') {
                    $maxStudents = (int) ($timeSlot->max_students ?? 30);
                    if ($slotBookingsCount >= $maxStudents) {
                        $message = 'This theoretical slot is already full.';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $message], 422);
                        }
                        return back()->withErrors(['time_slot' => $message]);
                    }
                } else {
                    if ($assignedInstructors->isEmpty()) {
                        $message = 'This practical slot has no assigned instructor.';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $message], 422);
                        }
                        return back()->withErrors(['time_slot' => $message]);
                    }

                    // PDC: each assigned instructor can only handle one student per slot.
                    if ($slotBookingsCount >= $assignedInstructors->count()) {
                        $message = 'This practical slot is already full (1 student per instructor).';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $message], 422);
                        }
                        return back()->withErrors(['time_slot' => $message]);
                    }
                }

                if (!empty($validated['instructor_id']) && $assignedInstructors->isNotEmpty()) {
                    $isInstructorAssignedToSlot = $assignedInstructors->contains('id', (int) $validated['instructor_id']);
                    if (!$isInstructorAssignedToSlot) {
                        $message = 'Selected instructor is not assigned to this slot.';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $message], 422);
                        }
                        return back()->withErrors(['instructor_id' => $message]);
                    }
                }

                // Auto-assign instructor if not provided
                if (empty($validated['instructor_id'])) {
                    if ($assignedInstructors->isNotEmpty()) {
                        // Find instructor with least bookings for this slot (load balancing)
                        $instructorBookingCounts = [];
                        foreach ($assignedInstructors as $instructor) {
                            $count = \App\Models\Booking::where('time_slot_id', $timeSlot->id)
                                ->where('instructor_id', $instructor->id)
                                ->whereIn('status', $activeSlotStatuses)
                                ->count();
                            $instructorBookingCounts[$instructor->id] = $count;
                        }

                        if ($slotSessionType === 'practical') {
                            $availableInstructors = array_keys(array_filter($instructorBookingCounts, fn($count) => $count < 1));
                            if (empty($availableInstructors)) {
                                $message = 'This practical slot is already full (1 student per instructor).';
                                if ($request->ajax() || $request->wantsJson()) {
                                    return response()->json(['success' => false, 'message' => $message], 422);
                                }
                                return back()->withErrors(['time_slot' => $message]);
                            }
                            $validated['instructor_id'] = $availableInstructors[array_rand($availableInstructors)];
                        } else {
                            // Theoretical: distribute to least-loaded instructor for this slot.
                            $minBookings = min($instructorBookingCounts);
                            $availableInstructors = array_keys(array_filter($instructorBookingCounts, fn($count) => $count === $minBookings));
                            $validated['instructor_id'] = $availableInstructors[array_rand($availableInstructors)];
                        }
                    }
                } elseif ($slotSessionType === 'practical') {
                    $selectedInstructorBookings = \App\Models\Booking::where('time_slot_id', $timeSlot->id)
                        ->where('instructor_id', $validated['instructor_id'])
                        ->whereIn('status', $activeSlotStatuses)
                        ->count();

                    if ($selectedInstructorBookings >= 1) {
                        $message = 'Selected instructor already has a student in this practical slot.';
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json(['success' => false, 'message' => $message], 422);
                        }
                        return back()->withErrors(['instructor_id' => $message]);
                    }
                }
            }
        }

        $effectiveCourseId = (int) $validated['course_id'];
        $effectiveCourse = $school->courses()->findOrFail($effectiveCourseId);

        $linkedEnrollment = $school->enrollmentRequests()
            ->where('id', $validated['enrollment_request_id'])
            ->where('learner_id', $validated['student_id'])
            ->where('course_id', $effectiveCourseId)
            ->where('status', 'approved')
            ->first();

        if (!$linkedEnrollment) {
            $message = 'The selected enrollment record does not match this learner and lesson course.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['enrollment_request_id' => $message]);
        }

        $requestedSessionType = null;
        if (!empty($validated['time_slot_id'])) {
            $linkedTimeSlot = $school->timeSlots()->find($validated['time_slot_id']);
            $requestedSessionType = $linkedTimeSlot?->session_type;
        }

        if (!in_array($requestedSessionType, ['theoretical', 'practical'], true)) {
            $requestedSessionType = ($effectiveCourse->course_type === 'practical') ? 'practical' : 'theoretical';
        }

        // Double-booking prevention: instructor overlap + student same batch repeat
        if (!empty($validated['instructor_id'])) {
            $checkTime = $validated['time_slot_id'] ? ($school->timeSlots()->find($validated['time_slot_id']) ?? null) : null;
            $checkDate = $checkTime ? $checkTime->date->format('Y-m-d') : \Carbon\Carbon::parse($validated['scheduled_at'])->format('Y-m-d');
            $checkStart = $checkTime ? $checkTime->start_time : \Carbon\Carbon::parse($validated['scheduled_at'])->format('H:i');
            $checkEnd = $checkTime ? $checkTime->end_time : \Carbon\Carbon::parse($validated['scheduled_at'])->addHour()->format('H:i');
            $overlappingInstructorBooking = Booking::where('school_id',$school->id)
                ->where('instructor_id',$validated['instructor_id'])
                ->whereIn('status',['pending','scheduled','confirmed','done'])
                ->where(function($q) use ($checkDate, $checkStart, $checkEnd){
                    $q->whereDate('booking_date',$checkDate)
                      ->orWhereHas('timeSlot', fn($sq)=>$sq->where('date',$checkDate));
                })->with('timeSlot')->get()->filter(function($b) use ($checkStart,$checkEnd){
                    try{
                        $s = $b->timeSlot ? $b->timeSlot->start_time : (\Carbon\Carbon::parse($b->scheduled_at)->format('H:i'));
                        $e = $b->timeSlot ? $b->timeSlot->end_time : (\Carbon\Carbon::parse($b->scheduled_at)->addHour()->format('H:i'));
                        $s1=\Carbon\Carbon::createFromFormat('H:i',$s); $e1=\Carbon\Carbon::createFromFormat('H:i',$e);
                        $s2=\Carbon\Carbon::createFromFormat('H:i',$checkStart); $e2=\Carbon\Carbon::createFromFormat('H:i',$checkEnd);
                        return $s1->lt($e2) && $s2->lt($e1);
                    }catch(\Exception $ex){ return false; }
                })->count();
            if ($overlappingInstructorBooking > 0) {
                $message = 'Instructor already booked at this time. Please choose another slot.';
                if ($request->ajax() || $request->wantsJson()) return response()->json(['success'=>false,'message'=>$message],422);
                return back()->withErrors(['instructor_id'=>$message]);
            }
            // Prevent student retaking same batch lesson (check notes batch group)
            if ($checkTime && $checkTime->batch_group && $checkTime->batch_day_number) {
                $alreadyBookedBatchDay = Booking::where('school_id', $school->id)
                    ->where('student_id', $student->id)
                    ->where('enrollment_request_id', $linkedEnrollment->id)
                    ->where('status', '!=', Booking::STATUS_CANCELLED)
                    ->whereHas('timeSlot', function ($query) use ($checkTime) {
                        $query->where('batch_group', $checkTime->batch_group)
                            ->where('batch_day_number', $checkTime->batch_day_number);
                    })
                    ->exists();

                if ($alreadyBookedBatchDay) {
                    $message = "You have already selected Day {$checkTime->batch_day_number} of this batch.";
                    if ($request->ajax() || $request->wantsJson()) return response()->json(['success'=>false,'message'=>$message],422);
                    return back()->withErrors(['time_slot_id'=>$message]);
                }
            }
        }
        if ($requestedSessionType === 'practical') {
            if (!$student->hasVerifiedLicense()) {
                $message = "A verified student driver's license is required before booking practical sessions.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['time_slot_id' => $message]);
            }

            if (($effectiveCourse->course_type ?? null) === 'combo' && !$student->hasPassedTheoretical()) {
                $message = 'For combo enrollment, complete the theoretical phase first before booking practical sessions.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['time_slot_id' => $message]);
            }
        }

        $booking = DB::transaction(function () use ($validated, $school, $queueEnabled) {
            $booking = Booking::create($validated);
            $booking->load(['student', 'instructor', 'course', 'timeSlot']);

            // Log booking creation
            SystemLog::logInfo(
                "New booking created for student: {$booking->student->name}",
                'booking',
                [
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'student_name' => $booking->student->name,
                    'course' => $booking->course->title ?? 'N/A',
                    'instructor' => $booking->instructor->name ?? 'Not assigned',
                    'scheduled_at' => $validated['scheduled_at'],
                    'status' => $booking->status
                ],
                $school->id,
                'create_booking'
            );

            return $booking;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $queueEnabled ? 'Schedule added to queue successfully' : 'Schedule created successfully',
                'booking' => $booking
            ], 201);
        }

        $message = $queueEnabled
            ? 'Schedule added to your queue! It will be confirmed automatically in ' . ($settings->booking_queue_days ?? 3) . ' days.'
            : 'Schedule created successfully';

        return redirect()->route('schools.student.schedule', $school->slug)
            ->with('success', $message);
    }

    /**
     * Display the specified booking.
     */
    public function show(School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        // IDOR Guard: Check if student is the owner or instructor is assigned
        if (Auth::guard('student')->check()) {
            abort_if((int) $booking->student_id !== (int) Auth::guard('student')->id(), 403, 'Unauthorized access to booking details.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int) $booking->instructor_id !== (int) Auth::guard('instructor')->id(), 403, 'Unauthorized access to booking details.');
        }

        $booking->load(['student', 'instructor', 'course', 'payment']);

        // Always return JSON - booking details shown in modals
        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        // IDOR Guard: Only admin or the student owner can update (students usually cancel via status)
        if (Auth::guard('student')->check()) {
            abort_if((int) $booking->student_id !== (int) Auth::guard('student')->id(), 403, 'Unauthorized update attempt.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int) $booking->instructor_id !== (int) Auth::guard('instructor')->id(), 403, 'Instructors cannot update bookings directly.');
        }

        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('school_id', $school->id)
            ],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where('school_id', $school->id)
            ],
            'instructor_id' => [
                'nullable',
                Rule::exists('instructors', 'id')->where('school_id', $school->id)
            ],
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,completed,cancelled,no-show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Layer 2: Fail-Closed Retrieval
        $student = $school->students()->findOrFail($validated['student_id']);
        $course = $school->courses()->findOrFail($validated['course_id']);
        if (!empty($validated['instructor_id'])) {
            $instructor = $school->instructors()->findOrFail($validated['instructor_id']);

            if (!$instructor->canTeach($course)) {
                $message = "Instructor {$instructor->name} is not accredited to teach this course.";
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                return back()->withErrors(['instructor_id' => $message]);
            }
        }

        // If status is being changed to cancelled, track who cancelled it
        if (isset($validated['status']) && $validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_by'] = 'admin';
            $validated['cancelled_at'] = now();
            if (empty($validated['cancellation_reason'])) {
                $validated['cancellation_reason'] = 'Cancelled by school administrator';
            }

            // Log booking cancellation
            SystemLog::logInfo(
                "Booking cancelled by admin for student: {$booking->student->name}",
                'booking',
                [
                    'booking_id' => $booking->id,
                    'student_name' => $booking->student->name,
                    'reason' => $validated['cancellation_reason'],
                    'cancelled_by' => Auth::guard('admin')->user()?->name ?? 'Admin'
                ],
                $school->id,
                'cancel_booking'
            );
        }

        $booking->update($validated);
        $booking->load(['student', 'instructor', 'course']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'booking' => $booking
            ]);
        }

        return redirect()->route('schools.admin.verify-session-completion.show', [$school->slug, $booking->id])
            ->with('success', 'Schedule updated successfully');
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        // Log booking deletion
        SystemLog::logWarning(
            "Booking permanently deleted",
            'booking',
            [
                'booking_id' => $booking->id,
                'student_name' => $booking->student->name ?? 'Unknown',
                'course' => $booking->course->title ?? 'N/A',
                'deleted_by' => Auth::guard('admin')->user()?->name ?? 'Admin'
            ],
            $school->id,
            'delete_booking'
        );

        $booking->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        }

        return redirect()->route('schools.admin.verify-session-completion.index', $school->slug)
            ->with('success', 'Schedule deleted successfully');
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $isAdmin = Auth::guard('admin')->check();

        // IDOR Guard
        if (Auth::guard('student')->check()) {
            abort_if((int) $booking->student_id !== (int) Auth::guard('student')->id(), 403, 'Unauthorized status update.');
        } elseif (Auth::guard('instructor')->check()) {
            abort_if((int) $booking->instructor_id !== (int) Auth::guard('instructor')->id(), 403, 'Unauthorized status update.');
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,done,completed,cancelled,no-show',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Admin verify workflow policy: instructor owns in-progress statuses.
        if ($isAdmin && in_array($validated['status'], ['scheduled', 'done', 'no-show'], true)) {
            $message = 'Admin can only set Completed or Cancelled from verification. Done/No-show must come from instructor updates.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }
            return back()->with('error', $message);
        }

        // Security: Only Admins can set status to 'completed' (The Handshake)
        if ($validated['status'] === 'completed' && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can officially complete and log training sessions.'
            ], 403);
        }

        // Logic: Cannot complete a session that wasn't marked as 'done' by an instructor first (Optional but recommended)
        if ($validated['status'] === 'completed') {
            if (!$booking->enrollment_request_id) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Cannot complete booking: This student has no linked enrollment request.'
                    ], 422);
                }
                return back()->with('error', 'Cannot complete booking: This student has no linked enrollment request.');
            }

            if ($booking->status !== 'completed' && $booking->status !== 'done') {
                $message = 'Cannot verify this lesson yet. Instructor must mark it as done first.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->with('error', $message);
            }

            if ($booking->status !== 'completed' && ($booking->attendance_status ?? '') !== 'attended') {
                $message = 'Cannot verify this lesson yet. Attendance must be marked as attended by the instructor first.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->with('error', $message);
            }
        }

        // Keep booking-level and lesson-level status fields in sync for reporting and guards.
        if ($validated['status'] === 'done') {
            $validated['session_status'] = 'done';
        } elseif ($validated['status'] === 'completed') {
            $validated['session_status'] = 'completed';
            if (!$booking->attendance_marked_at) {
                $validated['attendance_marked_at'] = now();
            }
        } elseif ($validated['status'] === 'no-show') {
            $validated['session_status'] = 'no-show';
            if (!$booking->attendance_status) {
                $validated['attendance_status'] = 'absent';
            }
            if (!$booking->attendance_marked_at) {
                $validated['attendance_marked_at'] = now();
            }
        }

        // If status is being changed to cancelled, track who cancelled it
        if ($validated['status'] === 'cancelled' && $booking->status !== 'cancelled') {
            $validated['cancelled_by'] = $isAdmin ? 'admin' : (Auth::guard('instructor')->check() ? 'instructor' : 'student');
            $validated['cancelled_at'] = now();
            if (empty($validated['cancellation_reason'])) {
                $validated['cancellation_reason'] = 'Cancelled by ' . ($isAdmin ? 'administrator' : 'user');
            }
        }

        DB::beginTransaction();
        try {
            $oldStatus = $booking->status;
            $booking->update($validated);

            // [AUTO-LOGGING LOGIC]
            // When Admin marks as completed, create the official SessionCompletion record
            if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
                $this->autoLogSession($booking, $school);
            }

            DB::commit();

            $message = $validated['status'] === 'completed'
                ? 'Session verified and officially logged to training history.'
                : 'Schedule status updated successfully';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'booking' => $booking
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update booking status: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Failed to update status.');
        }
    }

    /**
     * Automatically generate a SessionCompletion record from a completed Booking.
     */
    private function autoLogSession(Booking $booking, School $school)
    {
        // Fail-Closed Guard (Secondary check, primary is in updateStatus)
        if (!$booking->enrollment_request_id) {
            Log::warning("Session auto-log skipped: Enrollment Request ID missing for Booking #{$booking->id}");
            return;
        }

        $timeSlot = $booking->timeSlot;
        $course = $booking->course;

        $sessionType = $timeSlot?->session_type;
        if (!in_array($sessionType, ['theoretical', 'practical'], true)) {
            $sessionType = ($course && $course->course_type === 'practical') ? 'practical' : 'theoretical';
        }

        $sessionDate = $timeSlot ? $timeSlot->date : ($booking->scheduled_at ? $booking->scheduled_at->toDateString() : now()->toDateString());
        $sessionTime = $timeSlot ? $timeSlot->start_time : ($booking->scheduled_at ? $booking->scheduled_at->toTimeString() : now()->toTimeString());
        $startTime = $timeSlot ? $timeSlot->start_time : null;
        $endTime = $timeSlot ? $timeSlot->end_time : null;

        $existingCompletion = \App\Models\SessionCompletion::query()
            ->where('school_id', $school->id)
            ->where('enrollment_id', $booking->enrollment_request_id)
            ->where('instructor_id', $booking->instructor_id)
            ->where('session_type', $sessionType)
            ->whereDate('session_date', $sessionDate)
            ->where(function ($query) use ($startTime, $endTime, $sessionTime) {
                if ($startTime && $endTime) {
                    $query->where('start_time', $startTime)
                        ->where('end_time', $endTime);
                    return;
                }

                $query->where('session_time', $sessionTime);
            })
            ->exists();

        if ($existingCompletion) {
            Log::info("Session auto-log skipped: matching completion already exists for Booking #{$booking->id}");
            return;
        }

        // Calculate hours from time slot if possible, otherwise fallback to course default
        $hours = 1.0;
        if ($timeSlot && $timeSlot->start_time && $timeSlot->end_time) {
            $start = \Carbon\Carbon::parse($timeSlot->start_time);
            $end = \Carbon\Carbon::parse($timeSlot->end_time);
            $hours = round($start->diffInMinutes($end) / 60, 2);
        }

        \App\Models\SessionCompletion::create([
            'school_id' => $school->id,
            'enrollment_id' => $booking->enrollment_request_id,
            'instructor_id' => $booking->instructor_id,
            'session_type' => $sessionType,
            'hours_completed' => $hours,
            'session_date' => $sessionDate,
            'session_time' => $sessionTime,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'completed',
            'notes' => $booking->instructor_feedback ?? 'Auto-generated from verified schedule.',
            'logged_by' => Auth::id() ?? $booking->instructor_id,
        ]);
    }

    /**
     * Confirm a queued booking manually.
     */
    public function confirmBooking(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $studentId = Auth::guard('student')->id();
        abort_if(!$studentId || (int) $booking->student_id !== (int) $studentId, 403);

        // Only allow pending bookings to be confirmed
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only pending schedules can be confirmed.']);
        }

        $booking->update(['status' => 'scheduled']);

        // Log booking confirmation
        SystemLog::logInfo(
            "Booking confirmed for student: {$booking->student->name}",
            'booking',
            [
                'booking_id' => $booking->id,
                'student_name' => $booking->student->name ?? 'Unknown',
                'course' => $booking->course->title ?? 'N/A'
            ],
            $school->id,
            'confirm_booking'
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule confirmed successfully',
                'booking' => $booking
            ]);
        }

        return back()->with('success', 'Schedule confirmed and moved to your schedule!');
    }

    /**
     * Remove a booking from queue (cancel before confirmation).
     */
    public function removeFromQueue(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $studentId = Auth::guard('student')->id();
        abort_if(!$studentId || (int) $booking->student_id !== (int) $studentId, 403);

        // Only allow pending bookings to be removed from queue
        if ($booking->status !== 'pending') {
            return back()->withErrors(['booking' => 'Only queued schedules can be removed.']);
        }

        DB::transaction(function () use ($booking): void {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_by' => 'student',
                'cancellation_reason' => 'Cancelled by student (queue)',
                'cancelled_at' => now(),
            ]);
        });
        // Notify instructor and free slot count (cancelled excluded from capacity)
        try {
            $instructorsToNotify = collect();
            if ($booking->instructor_id) {
                $inst = \App\Models\Instructor::find($booking->instructor_id);
                if ($inst) $instructorsToNotify->push($inst);
            }
            if ($booking->timeSlot && $booking->timeSlot->instructors) {
                foreach ($booking->timeSlot->instructors as $inst) {
                    if (!$instructorsToNotify->contains('id', $inst->id)) {
                        $instructorsToNotify->push($inst);
                    }
                }
            }
            $dateStr = $booking->timeSlot?->date?->format('M d, Y') ?? ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : ($booking->scheduled_at ? \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y') : 'scheduled date'));
            foreach ($instructorsToNotify as $instructor) {
                \App\Models\Notification::send(
                    $instructor,
                    'booking_cancelled',
                    'Booking Cancelled by Student',
                    ($booking->student?->name ?? 'Student') . " cancelled booking on " . $dateStr,
                    'booking',
                    "/{$school->slug}/instructor/my-schedule"
                );
            }
            if ($booking->time_slot_id) {
                \Illuminate\Support\Facades\Cache::forget("slot_availability_{$booking->time_slot_id}");
            }
        } catch (\Exception $e) { \Illuminate\Support\Facades\Log::warning('Cancellation notify failed: '.$e->getMessage()); }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule removed from queue. Instructor notified and slot freed.'
            ]);
        }

        return back()->with('success', 'Schedule removed from queue. Instructor notified and slot freed.');
    }

    /**
     * Cancel a confirmed or scheduled booking by the student.
     */
    public function cancelBooking(Request $request, School $school, Booking $booking)
    {
        abort_if($booking->school_id !== $school->id, 404);

        $studentId = Auth::guard('student')->id();
        abort_if(!$studentId || (int) $booking->student_id !== (int) $studentId, 403);

        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors(['booking' => 'This booking cannot be cancelled.']);
        }

        DB::transaction(function () use ($booking, $request): void {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_by' => 'student',
                'cancellation_reason' => $request->input('reason', 'Cancelled by student'),
                'cancelled_at' => now(),
            ]);
        });

        // Sync cancellation to Instructor and update available slot count
        try {
            $instructorsToNotify = collect();
            if ($booking->instructor_id) {
                $inst = \App\Models\Instructor::find($booking->instructor_id);
                if ($inst) $instructorsToNotify->push($inst);
            }
            if ($booking->timeSlot && $booking->timeSlot->instructors) {
                foreach ($booking->timeSlot->instructors as $inst) {
                    if (!$instructorsToNotify->contains('id', $inst->id)) {
                        $instructorsToNotify->push($inst);
                    }
                }
            }
            $dateStr = $booking->timeSlot?->date?->format('M d, Y') ?? ($booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : ($booking->scheduled_at ? \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y') : 'scheduled date'));
            foreach ($instructorsToNotify as $instructor) {
                \App\Models\Notification::send(
                    $instructor,
                    'booking_cancelled',
                    'Confirmed Schedule Cancelled by Student',
                    ($booking->student?->name ?? 'Student') . " cancelled their schedule on " . $dateStr,
                    'booking',
                    "/{$school->slug}/instructor/my-schedule"
                );
            }

            // Invalidate availability cache so the slot immediately frees up
            if ($booking->time_slot_id) {
                \Illuminate\Support\Facades\Cache::forget("slot_availability_{$booking->time_slot_id}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Booking cancellation sync failed: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule plan cancelled. Instructor notified and slot updated.'
            ]);
        }

        return back()->with('success', 'Schedule plan cancelled. Instructor notified and slot updated.');
    }
}
