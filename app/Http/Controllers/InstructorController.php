<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\Progress;
use App\Models\SessionCompletion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Log;

class InstructorController extends Controller
{
    /**
     * Display the instructor dashboard.
     */
    public function dashboard(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        $instructor->load('branch');

        // 1. Schedule Statistics
        $todaysSchedules = TimeSlot::where('school_id', '=', $school->id, 'and')
            ->whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id, 'and');
        })
            ->whereDate('date', '=', Carbon::today(), 'and')
            ->count();

        $weeklySchedules = TimeSlot::where('school_id', '=', $school->id, 'and')
            ->whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id, 'and');
        })
            ->whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ], 'and', false)
            ->count();

        $nextLesson = TimeSlot::where('school_id', '=', $school->id, 'and')
            ->whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id, 'and');
        })
            ->where('date', '>=', Carbon::now(), 'and')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        $bookingStudents = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '!=', 'cancelled', 'and')
            ->distinct()
            ->pluck('student_id', 'id')
            ->toArray();

        $sessionStudents = SessionCompletion::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->with(['enrollment' => function($q) {
                $q->select('id', 'learner_id');
            }])
            ->get()
            ->map(fn($session) => $session->enrollment->learner_id ?? null)
            ->filter()
            ->unique()
            ->toArray();

        $activeStudents = count(array_unique(array_merge($bookingStudents, $sessionStudents)));

        $pendingBookings = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'scheduled', 'and')
            ->count('*');

        // 3. Upcoming Bookings - optimized with selective eager loading
        $upcomingBookings = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('scheduled_at', '>=', Carbon::now(), 'and')
            ->where('status', '=', 'scheduled', 'and')
            ->with([
            'student:id,name,email,contact',
            'course:id,title,duration_hours'
        ])
            ->select('id', 'instructor_id', 'student_id', 'course_id', 'scheduled_at', 'status', 'notes')
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        return view($school->resolveView('instructor.dashboard'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'instructor' => $instructor,
            'todaysLessons' => $todaysSchedules,
            'weeklyLessons' => $weeklySchedules,
            'nextLesson' => $nextLesson,
            'activeStudents' => $activeStudents,
            'pendingBookings' => $pendingBookings,
            'upcomingBookings' => $upcomingBookings,
        ]);
    }

    // ==========================
    // INSTRUCTOR FEATURES
    // ==========================

    public function myStudents(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $bookingStudentIds = Booking::where('school_id', '=', $school->id, 'and')
            ->whereNotNull('student_id')
            ->whereIn('status', [
                Booking::STATUS_SCHEDULED,
                Booking::STATUS_DONE,
                Booking::STATUS_COMPLETED,
                Booking::STATUS_NO_SHOW,
            ], 'and', false)
            ->where(function ($query) use ($instructor) {
                $query->where('instructor_id', '=', $instructor->id, 'and')
                    ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructor) {
                        $slotQuery->where('instructors.id', '=', $instructor->id, 'and');
                    });
            })
            ->distinct()
            ->pluck('student_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        // Include students from session completions (e.g., theoretical training logs)
        $sessionStudentIds = SessionCompletion::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->with(['enrollment' => function($q) {
                $q->select('id', 'learner_id');
            }])
            ->get()
            ->map(fn($session) => $session->enrollment->learner_id ?? null)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->toArray();

        $assignedStudentIds = array_values(array_unique(array_merge($bookingStudentIds, $sessionStudentIds)));

        // Normalize roster IDs to existing students in this school to prevent card/list mismatches.
        $rosterStudentIds = [];
        if (!empty($assignedStudentIds)) {
            $rosterStudentIds = Student::where('school_id', '=', $school->id, 'and')
                ->where('role', '=', 'student', 'and')
                ->whereIn('id', $assignedStudentIds, 'and', false)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }

        $instructorId = $instructor->id;
        $activeCardFilter = $request->get('card', 'all');

        if (!in_array($activeCardFilter, ['all', 'active', 'inactive', 'upcoming'], true)) {
            $activeCardFilter = 'all';
        }

        $upcomingStudentIds = [];
        if (!empty($rosterStudentIds)) {
            $upcomingStudentIds = Booking::where('school_id', '=', $school->id, 'and')
            ->whereIn('student_id', $rosterStudentIds, 'and', false)
                ->where('status', '=', Booking::STATUS_SCHEDULED, 'and')
                ->where('scheduled_at', '>', now(), 'and')
                ->where(function ($bookingQuery) use ($instructorId) {
                    $bookingQuery->where('instructor_id', '=', $instructorId, 'and')
                        ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructorId) {
                            $slotQuery->where('instructors.id', '=', $instructorId, 'and');
                        });
                })
                ->distinct()
                ->pluck('student_id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }

        // AUD-003 Fix: Only get students assigned to this instructor to prevent PII leakage
        $query = Student::where('school_id', '=', $school->id)->where('role', '=', 'student');

        if (empty($rosterStudentIds)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('id', $rosterStudentIds, 'and', false);
        }

        // Apply Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply card-based filters
        switch ($activeCardFilter) {
            case 'active':
                $query->where('status', '=', 'active', 'and');
                break;
            case 'inactive':
                $query->where('status', '=', 'inactive', 'and');
                break;
            case 'upcoming':
                if (empty($upcomingStudentIds)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $upcomingStudentIds, 'and', false);
                }
                break;
            case 'all':
            default:
                break;
        }

        $query->orderBy('name', 'asc');

        $students = $query->with(['progresses.course', 'bookings' => function ($query) use ($instructorId) {
            $query->where(function ($bookingQuery) use ($instructorId) {
                $bookingQuery->where('instructor_id', '=', $instructorId, 'and')
                    ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructorId) {
                        $slotQuery->where('instructors.id', '=', $instructorId, 'and');
                    });
            })
                ->whereIn('status', [
                    Booking::STATUS_SCHEDULED,
                    Booking::STATUS_DONE,
                    Booking::STATUS_COMPLETED,
                    Booking::STATUS_NO_SHOW,
                ], 'and', false)
                ->orderBy('scheduled_at', 'desc');
        }])
            ->paginate(12)
            ->withQueryString();

        $statsTotalStudents = count($rosterStudentIds);
        $statsActiveStudents = 0;
        $statsInactiveStudents = 0;
        $statsUpcomingStudents = count($upcomingStudentIds);

        if (!empty($rosterStudentIds)) {
            $statsActiveStudents = Student::where('school_id', '=', $school->id, 'and')
                ->whereIn('id', $rosterStudentIds, 'and', false)
                ->where('status', '=', 'active', 'and')
                ->count('*');

            $statsInactiveStudents = Student::where('school_id', '=', $school->id, 'and')
                ->whereIn('id', $rosterStudentIds, 'and', false)
                ->where('status', '=', 'inactive', 'and')
                ->count('*');
        }

        // Add computed data for each student
        $students->getCollection()->each(function ($student) use ($rosterStudentIds) {
            // Mark if student is assigned to this instructor
            $student->is_assigned = in_array((int) $student->id, $rosterStudentIds, true);

            // Get most recent booking with this instructor
            $recentBooking = $student->bookings->first();
            $student->recent_note = $recentBooking && $recentBooking->notes ? $recentBooking->notes : 'No notes yet';
            $student->recent_note_date = $recentBooking ? $recentBooking->scheduled_at : null;

            // Calculate overall progress
            $student->overall_progress = $student->progresses->avg('completion_percent') ?? 0;
            $student->avg_progress = $student->overall_progress; // Alias for consistency
            $student->total_sessions = $student->bookings->whereIn('status', [
                Booking::STATUS_DONE,
                Booking::STATUS_COMPLETED,
            ])->count();
            $student->sessions_count = $student->total_sessions; // Alias for consistency
            $student->next_session = $student->bookings->where('status', Booking::STATUS_SCHEDULED)
                ->where('scheduled_at', '>', now())
                ->first();
        });

        return view($school->resolveView('instructor.students'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'students' => $students,
            'instructor' => $instructor,
            'statsTotalStudents' => $statsTotalStudents,
            'statsActiveStudents' => $statsActiveStudents,
            'statsInactiveStudents' => $statsInactiveStudents,
            'statsUpcomingStudents' => $statsUpcomingStudents,
            'activeCardFilter' => $activeCardFilter,
        ]);
    }

    public function showStudent(Request $request, School $school, $id)
    {
        $instructor = Auth::guard('instructor')->user();

        // AUD-003 Fix: IDOR Guard - Verify student is assigned to this instructor
        $isAssigned = Booking::where('school_id', '=', $school->id, 'and')
            ->where('student_id', '=', $id, 'and')
            ->where(function ($query) use ($instructor) {
                $query->where('instructor_id', '=', $instructor->id, 'and')
                    ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructor) {
                        $slotQuery->where('instructors.id', '=', $instructor->id, 'and');
                    });
            })
            ->exists();

        if (!$isAssigned) {
            $isAssigned = SessionCompletion::where('school_id', $school->id)
                ->where('instructor_id', $instructor->id)
                ->whereHas('enrollment', function ($query) use ($id, $school) {
                    $query->where('learner_id', $id)
                        ->where('school_id', $school->id);
                })
                ->exists();
        }
        
        abort_unless($isAssigned, 403, 'Unauthorized access: You are not assigned to this student.');

        // Get student with only essential data (no personal details like address, email)
        // Load ALL bookings to show session history from all instructors
        $student = Student::select(['id', 'name', 'contact', 'status', 'school_id'])
            ->with(['bookings' => function ($query) use ($school) {
            $query->where('school_id', '=', $school->id, 'and')
                ->orderBy('scheduled_at', 'desc');
        }, 'bookings.course', 'bookings.instructor'])
            ->where('school_id', '=', $school->id, 'and')
            ->findOrFail($id);

        // Get all sessions (from all instructors) so current instructor can see previous notes
        $sessions = $student->bookings->map(function ($booking) use ($instructor) {
            return [
                'id' => $booking->id,
                'date' => $booking->scheduled_at,
                'instructor_name' => $booking->instructor->name ?? 'Unknown',
                'is_mine' => ($booking->instructor_id == $instructor->id),
                'course' => $booking->course->title ?? 'N/A',
                'status' => $booking->status,
                'notes' => $booking->notes ?? 'No notes provided',
            ];
        });

        // Calculate counts for the view
        $myCompletedCount = Booking::where('student_id', '=', $student->id, 'and')
            ->where(function ($query) use ($instructor) {
                $query->where('instructor_id', '=', $instructor->id, 'and')
                    ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructor) {
                        $slotQuery->where('instructors.id', '=', $instructor->id, 'and');
                    });
            })
            ->whereIn('status', [Booking::STATUS_DONE, Booking::STATUS_COMPLETED], 'and', false)
            ->count('*');

        $myUpcomingCount = Booking::where('student_id', '=', $student->id, 'and')
            ->where(function ($query) use ($instructor) {
                $query->where('instructor_id', '=', $instructor->id, 'and')
                    ->orWhereHas('timeSlot.instructors', function ($slotQuery) use ($instructor) {
                        $slotQuery->where('instructors.id', '=', $instructor->id, 'and');
                    });
            })
            ->where('status', '=', Booking::STATUS_SCHEDULED, 'and')
            ->count('*');

        return view($school->resolveView('instructor.student-detail'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'student' => $student,
            'sessions' => $sessions,
            'instructor' => $instructor,
            'myCompletedCount' => $myCompletedCount,
            'myUpcomingCount' => $myUpcomingCount,
        ]);
    }

    public function updateProgress(Request $request, School $school, $studentId)
    {
        try {
            $instructor = Auth::guard('instructor')->user();

            // Verify authorization
            $isAssigned = Booking::where('instructor_id', '=', $instructor->id, 'and')
                ->where('student_id', '=', $studentId, 'and')
                ->exists();

            abort_unless($isAssigned, 403);

            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'completion_percent' => 'required|numeric|min:0|max:100',
                'remarks' => 'nullable|string|max:500',
            ]);

            Progress::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'course_id' => $validated['course_id'],
                ],
                [
                    'completion_percent' => $validated['completion_percent'],
                    'remarks' => $validated['remarks'],
                    'updated_by_instructor' => $instructor->id,
                ]
            );

            return back()->with('success', 'Progress updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update student progress: ' . $e->getMessage());
            return back()->with('error', 'Unable to update progress.');
        }
    }

    public function mySchedule(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        
        $schedules = TimeSlot::whereHas('instructors', function ($query) use ($instructor) {
            $query->where('instructor_id', '=', $instructor->id, 'and');
        })
            ->with(['course', 'branch'])
            ->where('date', '>=', now()->toDateString(), 'and')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy('date');

        return view($school->resolveView('instructor.schedule'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'schedules' => $schedules,
            'instructor' => $instructor,
        ]);
    }

    /**
     * Display performance reports for the instructor.
     */
    public function reports(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        // 1. Basic Stats
        $totalLessonsCompleted = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->count('*');

        // Assume 1 hour per lesson if duration is not explicitly tracked per booking
        $totalHoursTaught = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->with(['course'])
            ->get(['id', 'course_id'])
            ->sum(function($booking) {
                return $booking->course->duration_hours ?? 1;
            });

        $totalStudentsTaught = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->distinct()
            ->count('student_id');

        $activeStudents = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'scheduled', 'and')
            ->distinct()
            ->count('student_id');

        // Attendance Rate (last 30 days)
        $last30DaysBookings = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('scheduled_at', '>=', now()->subDays(30), 'and')
            ->whereIn('status', ['completed', 'no-show'], 'and', false)
            ->count('*');
        
        $attended = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('scheduled_at', '>=', now()->subDays(30), 'and')
            ->where('status', '=', 'completed', 'and')
            ->count('*');

        $attendanceRate = $last30DaysBookings > 0 ? round(($attended / $last30DaysBookings) * 100) : 100;

        // 2. Monthly Trends
        $thisMonthLessons = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->whereMonth('scheduled_at', '=', now()->month, 'and')
            ->whereYear('scheduled_at', '=', now()->year, 'and')
            ->count('*');

        $lastMonthLessons = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->whereMonth('scheduled_at', '=', now()->subMonth()->month, 'and')
            ->whereYear('scheduled_at', '=', now()->subMonth()->year, 'and')
            ->count('*');

        // 6 Months Trend
        $lessonsByMonth = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->where('scheduled_at', '>=', now()->subMonths(6), 'and')
            ->selectRaw("DATE_FORMAT(scheduled_at, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get(['month', 'count']);

        // 3. Status Distribution (30 days)
        $lessonsByStatus = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('scheduled_at', '>=', now()->subDays(30), 'and')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get(['status', 'count']);

        // 4. Top Students
        $topStudents = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->with(['student:id,name'])
            ->selectRaw('student_id, COUNT(*) as lesson_count')
            ->groupBy('student_id')
            ->orderBy('lesson_count', 'desc')
            ->limit(5)
            ->get(['student_id', 'lesson_count']);

        // 5. Upcoming Schedule
        $upcomingLessons = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'scheduled', 'and')
            ->where('scheduled_at', '>=', now(), 'and')
            ->with(['student:id,name', 'course:id,title'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(10)
            ->get(['id', 'student_id', 'course_id', 'scheduled_at', 'status']);

        $avgGrade = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->whereNotNull('session_grade', 'and')
            ->avg('session_grade');

        return view($school->resolveView('instructor.reports'), array_merge(compact(
            'school', 'instructor', 'totalLessonsCompleted', 'totalHoursTaught', 
            'totalStudentsTaught', 'activeStudents', 'attendanceRate',
            'thisMonthLessons', 'lastMonthLessons', 'lessonsByMonth', 
            'lessonsByStatus', 'topStudents', 'upcomingLessons', 'avgGrade'
        ), ['isAjax' => $request->ajax()]));
    }

    /**
     * Display student grades for the instructor.
     */
    public function grades(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        // Get students who have bookings with this instructor
        $bookingStudentIds = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->distinct()
            ->pluck('student_id', null)
            ->toArray();

        // Include students from session completions
        $sessionStudentIds = SessionCompletion::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->with(['enrollment' => function($q) {
                $q->select('id', 'learner_id');
            }])
            ->get()
            ->map(fn($session) => $session->enrollment->learner_id ?? null)
            ->filter()
            ->unique()
            ->toArray();

        $studentIds = array_unique(array_merge($bookingStudentIds, $sessionStudentIds));

        $students = Student::where('school_id', '=', $school->id, 'and')
            ->where('role', '=', 'student', 'and')
            ->whereIn('id', $studentIds, 'and', false)
            ->with(['bookings' => function($q) use ($instructor) {
                $q->where('instructor_id', '=', $instructor->id, 'and');
            }])
            ->paginate(15, ['id', 'name', 'contact', 'status', 'school_id']);

        $gradedSessions = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->whereNotNull('session_grade', 'and')
            ->count('*');

        $averageGrade = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->whereNotNull('session_grade', 'and')
            ->avg('session_grade') ?? 0;

        $pendingGrades = Booking::where('school_id', '=', $school->id, 'and')
            ->where('instructor_id', '=', $instructor->id, 'and')
            ->where('status', '=', 'completed', 'and')
            ->whereNull('session_grade', 'and', false)
            ->count('*');

        return view($school->resolveView('instructor.grades'), array_merge(compact(
            'school', 'instructor', 'students', 'gradedSessions', 'averageGrade', 'pendingGrades'
        ), ['isAjax' => $request->ajax()]));
    }

    public function profile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();
        $instructor->load('branch');
        
        return view($school->resolveView('instructor.profile'), [
            'isAjax' => $request->ajax(),
            'school' => $school,
            'instructor' => $instructor,
        ]);
    }

    public function updateProfile(Request $request, School $school)
    {
        $instructor = Auth::guard('instructor')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('instructors', 'email')->ignore($instructor->id),
            ],
            'contact' => 'nullable|string|max:13|regex:/^(09\d{9}|\+639\d{9}|9\d{9})$/',
            'license_number' => 'nullable|string|max:50',
            'availability' => 'required|in:available,unavailable',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => ['nullable', 'string', 'confirmed', new StrongPassword()],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'availability' => $request->availability,
            'contact' => (function ($contact) {
                if (empty($contact)) return null;
                $contact = trim((string) $contact);
                if (preg_match('/^9\d{9}$/', $contact)) {
                    return '+63' . $contact;
                } elseif (preg_match('/^09\d{9}$/', $contact)) {
                    return '+63' . substr($contact, 1);
                }
                return $contact;
            })($request->contact),
            'license_number' => $request->license_number,
        ];

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $instructor->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $data['password'] = Hash::make($request->new_password);
        }

        $instructor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}
