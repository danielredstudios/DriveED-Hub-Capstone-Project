<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\FinancialService;

class ReportController extends Controller
{
    /**
     * Display unified analytics dashboard
     */
    public function index(Request $request, School $school, FinancialService $financialService)
    {
        try {
            $admin = auth()->guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }
            set_time_limit(180);
            $schoolId = $school->id;

            // Get time period filter (default: all time)
            $period = $request->input('period', 'all');
            $startDate = null;
            $endDate = now();

            switch ($period) {
                case 'today':
                    $startDate = now()->startOfDay();
                    break;
                case 'week':
                    $startDate = now()->startOfWeek();
                    break;
                case 'month':
                    $startDate = now()->startOfMonth();
                    break;
                case 'year':
                    $startDate = now()->startOfYear();
                    break;
                default:
                    $startDate = null; // All time
            }

            // â”€â”€ 1. Student metrics (2 queries) â”€â”€
            $studentCounts = $admin->scopeToBranch(Student::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
                ->first();

            $studentsByStatus = $admin->scopeToBranch(Student::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            // â”€â”€ 2. Enrollment trends (Semantic Fix: Use EnrollmentRequest) â”€â”€
            $activeEnrollmentsCount = $admin->scopeToBranch(EnrollmentRequest::where('school_id', $schoolId))
                ->where('status', 'approved')
                ->count();

            $enrollmentsThisMonth = $admin->scopeToBranch(EnrollmentRequest::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]),
                    fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                ->count();



            // â”€â”€ 3. ALL booking stats in ONE query using conditional aggregation â”€â”€
            $bookingStats = $admin->scopeToBranch(Booking::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 'no_show' OR status = 'no-show' THEN 1 ELSE 0 END) as no_show,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status IN ('cancelled', 'no_show', 'no-show') THEN 1 ELSE 0 END) as missed
            ")
                ->first();

            // Bookings by status (for charts) - reuse the aggregated data
            $bookingsByStatus = collect();
            foreach (['completed', 'cancelled', 'no_show', 'pending', 'scheduled', 'confirmed'] as $status) {
                $val = (int)($bookingStats->$status ?? 0);
                if ($val > 0) {
                    $bookingsByStatus->push((object)['status' => $status, 'count' => $val]);
                }
            }

            // â”€â”€ 4. Course stats - ONE bulk query instead of per-course loops â”€â”€
            $courses = Course::where('school_id', $schoolId)->get();

            $courseBookingStats = $admin->scopeToBranch(Booking::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                ->selectRaw("
                course_id,
                COUNT(*) as total_bookings,
                COUNT(DISTINCT student_id) as total_enrolled,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")
                ->groupBy('course_id')
                ->get()
                ->keyBy('course_id');

            // Course revenue - ONE bulk query (Corrected Axis: paid_on + Enrollment Fees)
            $courseRevenue = collect();
            // Course revenue - ONE bulk query (Corrected Axis: approved + received_at) - F-005 Remediation
            try {
                $dateColumn = 'payments.received_at';

                $courseRevenueFromBookings = DB::table('payments')
                    ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
                    ->where('bookings.school_id', $schoolId)
                    ->when($admin->isBranchSecretary(), fn($q) => $q->where('bookings.branch_id', $admin->branch_id))
                    ->where('payments.status', 'approved')
                    ->whereNotNull($dateColumn)
                    ->when($startDate, fn($q) => $q->whereBetween($dateColumn, [$startDate, $endDate]))
                    ->selectRaw('bookings.course_id, SUM(payments.amount) as total_revenue')
                    ->groupBy('bookings.course_id')
                    ->pluck('total_revenue', 'course_id');

                $courseRevenueFromEnrollments = DB::table('payments')
                    ->join('enrollment_requests', 'payments.enrollment_request_id', '=', 'enrollment_requests.id')
                    ->where('enrollment_requests.school_id', $schoolId)
                    ->when($admin->isBranchSecretary(), fn($q) => $q->where('enrollment_requests.branch_id', $admin->branch_id))
                    ->where('payments.status', 'approved')
                    ->whereNotNull($dateColumn)
                    ->when($startDate, fn($q) => $q->whereBetween($dateColumn, [$startDate, $endDate]))
                    ->selectRaw('enrollment_requests.course_id, SUM(payments.amount) as total_revenue')
                    ->groupBy('enrollment_requests.course_id')
                    ->pluck('total_revenue', 'course_id');

                $courseRevenue = $courseRevenueFromBookings;
                foreach ($courseRevenueFromEnrollments as $courseId => $amount) {
                    $courseRevenue[$courseId] = ($courseRevenue[$courseId] ?? 0) + $amount;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Course revenue query failed: ' . $e->getMessage());
            }

            $courseStats = $courses->map(function ($course) use ($courseBookingStats, $courseRevenue) {
                $stats = $courseBookingStats->get($course->id);
                $totalBookings = $stats->total_bookings ?? 0;
                $completedLessons = $stats->completed ?? 0;
                $totalEnrolled = $stats->total_enrolled ?? 0;
                return (object)[
                'title' => $course->title,
                'name' => $course->title,
                'price' => $course->price,
                'total_enrolled' => $totalEnrolled,
                'completion_rate' => $totalBookings > 0 ? round(($completedLessons / $totalBookings) * 100, 1) : 0,
                'average_rating' => null,
                'total_revenue' => $courseRevenue->get($course->id, 0),
                ];
            })->sortByDesc('total_enrolled')->take(10);

            // â”€â”€ 5. Top instructors - already efficient (single GROUP BY query) â”€â”€
            $topInstructors = $admin->scopeToBranch(Booking::where('school_id', $schoolId))
                ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                ->with('instructor')
                ->selectRaw('instructor_id, 
                        COUNT(*) as total_lessons, 
                        SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons,
                        COUNT(DISTINCT student_id) as unique_students')
                ->whereNotNull('instructor_id')
                ->groupBy('instructor_id')
                ->orderByDesc('completed_lessons')
                ->limit(10)
                ->get()
                ->map(fn($b) => (object)[
            'name' => $b->instructor->name ?? 'N/A',
            'total_sessions' => $b->total_lessons,
            'completed_sessions' => $b->completed_lessons,
            'average_rating' => null,
            'completion_rate' => $b->total_lessons > 0 ? round(($b->completed_lessons / $b->total_lessons) * 100, 1) : 0,
            ]);

            // â”€â”€ 6. Lessons by instructor - already efficient (single GROUP BY) â”€â”€
            $lessonsByInstructor = $admin->scopeToBranch(Booking::where('school_id', $schoolId))
                ->with('instructor')
                ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                ->whereNotNull('instructor_id')
                ->selectRaw('instructor_id, COUNT(*) as total_lessons, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_lessons')
                ->groupBy('instructor_id')
                ->get()
                ->map(fn($b) => (object)[
            'instructor_name' => $b->instructor->name ?? 'Unassigned',
            'total_lessons' => $b->total_lessons,
            'completed_lessons' => $b->completed_lessons,
            'completion_rate' => $b->total_lessons > 0 ? round(($b->completed_lessons / $b->total_lessons) * 100, 1) : 0,
            ]);

            // â”€â”€ 7. Cancellation details (1 query with eager loading) â”€â”€
            $cancellationDetails = $admin->scopeToBranch(Booking::where('school_id', $schoolId))
                ->whereIn('status', ['cancelled', 'no_show', 'no-show'])
                ->when($startDate, fn($q) => $q->whereBetween('scheduled_at', [$startDate, $endDate]))
                ->with(['student', 'instructor', 'course'])
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get();

            // â”€â”€ 8. Financial data - 3 queries (Semantic Fix: Standardize on paid_on) â”€â”€
            // â”€â”€ 8. Financial data - Simplified with FinancialService â”€â”€
            $branchId = $admin->isBranchSecretary() ? $admin->branch_id : null;
            $financialSummary = $financialService->getRevenueSummary($school, $branchId, $startDate, $endDate);
            $paymentsByMethod = $financialService->getCollectionByMethod($school, $branchId);

            // â”€â”€ 9. Student progress - ONE bulk query instead of loading ALL students + ALL bookings â”€â”€
            $studentProgress = DB::table('bookings')
                ->join('students', 'bookings.student_id', '=', 'students.id')
                ->where('bookings.school_id', $schoolId)
                ->when($admin->isBranchSecretary(), fn($q) => $q->where('bookings.branch_id', $admin->branch_id))
                ->when($startDate, fn($q) => $q->whereBetween('bookings.scheduled_at', [$startDate, $endDate]))
                ->selectRaw("
                students.id, students.name, students.email, students.enrollment_date, students.status,
                COUNT(*) as total_lessons,
                SUM(CASE WHEN bookings.status = 'completed' THEN 1 ELSE 0 END) as completed_lessons
            ")
                ->groupBy('students.id', 'students.name', 'students.email', 'students.enrollment_date', 'students.status')
                ->orderByDesc('completed_lessons')
                ->limit(20)
                ->get()
                ->map(fn($s) => (object)[
            'name' => $s->name,
            'email' => $s->email,
            'enrollment_date' => $s->enrollment_date,
            'status' => $s->status,
            'total_lessons' => $s->total_lessons,
            'completed_lessons' => $s->completed_lessons,
            'progress_rate' => $s->total_lessons > 0 ? round(($s->completed_lessons / $s->total_lessons) * 100, 1) : 0,
            ]);

            // â”€â”€ Build analytics array â”€â”€
            $totalAllBookings = (int)$bookingStats->total;
            $completedCount = (int)$bookingStats->completed;
            $cancelledCount = (int)$bookingStats->cancelled;
            $noShowCount = (int)$bookingStats->no_show;
            $missedCount = (int)$bookingStats->missed;

            $completionRate = $totalAllBookings > 0 ? round(($completedCount / $totalAllBookings) * 100, 2) : 0;
            $attendanceRate = $totalAllBookings > 0 ? round(($completedCount / $totalAllBookings) * 100, 2) : 0;
            $totalCancellations = $cancelledCount + $noShowCount;
            $cancellationRate = $totalAllBookings > 0 ? round(($totalCancellations / $totalAllBookings) * 100, 2) : 0;



            $analytics = [
                'current_period' => $period,
                'total_students' => (int)$studentCounts->total,
                'active_students' => (int)$studentCounts->active,
                'active_enrollments' => $activeEnrollmentsCount,
                'total_instructors' => $admin->scopeToBranch(Instructor::where('school_id', $schoolId))->count(),
                'total_bookings_this_month' => $totalAllBookings,
                'completed_lessons_this_month' => $completedCount,
                'students_by_status' => $studentsByStatus,
                'enrollments_this_month' => $enrollmentsThisMonth,
                'bookings_by_status' => $bookingsByStatus,
                'total_all_bookings' => $totalAllBookings,
                'completion_rate' => $completionRate,
                'course_stats' => $courseStats,
                'top_instructors' => $topInstructors,
                'attendance' => [
                    'attended' => $completedCount,
                    'missed' => $missedCount,
                    'rate' => $attendanceRate,
                ],
                'cancellations' => [
                    'total' => $cancelledCount,
                    'no_show' => $noShowCount,
                    'rate' => $cancellationRate,
                ],
                'lessons_by_status' => $bookingsByStatus,
                'lessons_by_instructor' => $lessonsByInstructor,
                'cancellation_details' => $cancellationDetails,
                'financial' => [
                    'total_revenue' => $financialSummary['net_revenue'],
                    'gross_revenue' => $financialSummary['gross_revenue'],
                    'total_refunded' => $financialSummary['total_refunded'],
                    'pending_payments' => $financialSummary['pending_amount'],
                    'payments_by_method' => $paymentsByMethod,
                ],
                'student_progress' => $studentProgress,
            ];

            // Make sure $school and helper functions are available
            view()->share('school', $school);
            view()->share('schoolRoute', static function (string $name, array $parameters = []) use ($school) {
                $routeName = str_starts_with($name, 'schools.') ? $name : 'schools.' . $name;
                return route($routeName, array_merge(['school' => $school], $parameters));
            });

            return view($school->resolveView('admin.reports.index'), [
                'analytics' => $analytics,
                'school' => $school,
                'isAjax' => $request->ajax()
            ]);
        }
        catch (\Exception $e) {
            \App\Models\SystemLog::logError(
                'Reports Dashboard Generation Error',
                'database',
                $e,
            ['school_id' => $school->id],
                $school->id,
                'generate_reports'
            );
            return back()->with('error', 'Unable to generate analytics report at this time. Our team has been notified.');
        }
    }

    // End of ReportController
}
