<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\EnrollmentRequest;
use App\Models\Course;
use App\Models\Payment;
use App\Models\TimeSlot;
use App\Models\Booking;
use App\Models\SessionCompletion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Export students list as PDF
     */
    public function studentsPdf(Request $request, School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $branchFilter = $this->resolveStudentBranchFilter($request, $school, $admin);
        $studentQuery = Student::where('school_id', $school->id)
            ->where('role', 'student')
            ->with(['enrollments.course'])
            ->orderBy('name');
        $this->applyStudentBranchFilter($studentQuery, $branchFilter);
        $students = $studentQuery->limit(500)->cursor();

        try {
            $pdf = Pdf::loadView('exports.students-pdf', [
                'school' => $school,
                'students' => $students,
                'generatedAt' => now(),
                'branchFilterLabel' => $branchFilter['label'],
            ]);

            return $pdf->download('students-list-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (studentsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_students_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export enrollment requests as PDF
     */
    public function enrollmentsPdf(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $query = EnrollmentRequest::where('school_id', $school->id)
            ->with(['learner', 'course']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->limit(500)->cursor();

        try {
            $pdf = Pdf::loadView('exports.enrollments-pdf', [
                'school' => $school,
                'enrollments' => $enrollments,
                'status' => $request->status ?? 'all',
                'generatedAt' => now(),
            ]);

            return $pdf->download('enrollment-requests-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (enrollmentsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_enrollments_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export enrollment requests as Excel (styled HTML format)
     */
    public function enrollmentsExcel(School $school, Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            set_time_limit(120);

            $query = EnrollmentRequest::where('school_id', $school->id)
                ->with(['learner', 'course']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $enrollments = $query->orderBy('created_at', 'desc')->limit(1000)->cursor();

            $rows = [];
            foreach ($enrollments as $enrollment) {
                $rows[] = [
                    $enrollment->created_at->format('M d, Y'),
                    $enrollment->learner->name ?? 'N/A',
                    $enrollment->course->title ?? 'N/A',
                    'PHP ' . number_format($enrollment->price, 2),
                    ucfirst($enrollment->status),
                    $enrollment->payment_status ? ucfirst($enrollment->payment_status) : 'Unpaid',
                    $enrollment->reviewed_at ? Carbon::parse($enrollment->reviewed_at)->format('M d, Y') : 'Pending',
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Enrollment Requests',
                ['Date', 'Student', 'Course', 'Price', 'Status', 'Payment', 'Reviewed At'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_enrollments_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (enrollmentsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_enrollments_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export student progress report as PDF
     */
    public function studentProgressPdf(School $school, Student $student)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        if ($student->school_id !== $school->id) {
            abort(404);
        }

        $enrollments = EnrollmentRequest::where('learner_id', $student->id)
            ->with(['course', 'sessionCompletions.instructor'])
            ->cursor();

        $totalSessions = SessionCompletion::whereHas('enrollmentRequest', function ($query) use ($student) {
            $query->where('learner_id', $student->id);
        })->count();

        $totalHours = SessionCompletion::whereHas('enrollmentRequest', function ($query) use ($student) {
            $query->where('learner_id', $student->id);
        })->sum('hours_completed');

        try {
            $pdf = Pdf::loadView('exports.student-progress-pdf', [
                'school' => $school,
                'student' => $student,
                'enrollments' => $enrollments,
                'totalSessions' => $totalSessions,
                'totalHours' => $totalHours,
                'generatedAt' => now(),
            ]);

            return $pdf->download('progress-' . $student->name . '-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (studentProgressPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'student_id' => $student->id], $school->id, 'export_student_progress_pdf');
            return back()->with('error', 'Failed to generate progress report PDF.');
        }
    }

    /**
     * Export students list as Excel (styled HTML format)
     */
    public function studentsExcel(Request $request, School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            set_time_limit(120);

            $branchFilter = $this->resolveStudentBranchFilter($request, $school, $admin);
            $studentQuery = Student::where('school_id', $school->id)
                ->where('role', 'student')
                ->with(['enrollments.course'])
                ->orderBy('name');
            $this->applyStudentBranchFilter($studentQuery, $branchFilter);
            $students = $studentQuery->limit(500)->cursor();

            $rows = [];
            foreach ($students as $student) {
                $activeEnrollments = $student->enrollments->where('status', 'approved')->count();
                $status = $student->status ?? ($activeEnrollments > 0 ? 'active' : 'inactive');
                $rows[] = [
                    $student->name,
                    $student->email,
                    $student->contact ?? 'N/A',
                    ucfirst($status),
                    $activeEnrollments,
                    $student->enrollment_date ?Carbon::parse($student->enrollment_date)->format('M d, Y') : 'N/A',
                    $student->created_at->format('M d, Y'),
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Student List - ' . $branchFilter['label'],
            ['Name', 'Email', 'Phone', 'Status', 'Active Enrollments', 'Enrollment Date', 'Registration Date'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_students_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (studentsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_students_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    private function resolveStudentBranchFilter(Request $request, School $school, $admin): array
    {
        if ($admin->isBranchSecretary()) {
            $branch = \App\Models\Branch::where('school_id', $school->id)->find($admin->branch_id);
            return [
                'value' => $admin->branch_id,
                'label' => $branch?->name ?? 'Assigned Branch',
            ];
        }

        $value = (string) $request->input('branch', 'all');
        if ($value === 'unassigned') {
            return ['value' => 'unassigned', 'label' => 'Unassigned'];
        }

        if ($value !== 'all' && ctype_digit($value)) {
            $branch = \App\Models\Branch::where('school_id', $school->id)->find((int) $value);
            if ($branch) {
                return ['value' => (int) $branch->id, 'label' => $branch->name];
            }
        }

        return ['value' => 'all', 'label' => 'All Branches'];
    }

    private function applyStudentBranchFilter($query, array $branchFilter): void
    {
        if ($branchFilter['value'] === 'unassigned') {
            $query->whereNull('branch_id');
        } elseif ($branchFilter['value'] !== 'all') {
            $query->where('branch_id', $branchFilter['value']);
        }
    }

    /**
     * Export instructors list as PDF
     */
    public function instructorsPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $instructors = Instructor::where('school_id', $school->id)
            ->orderBy('name')
            ->limit(500)
            ->cursor();

        try {
            $pdf = Pdf::loadView('exports.instructors-pdf', [
                'school' => $school,
                'instructors' => $instructors,
                'generatedAt' => now(),
            ]);

            return $pdf->download('instructors-list-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (instructorsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_instructors_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export instructors list as Excel (styled HTML format)
     */
    public function instructorsExcel(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            set_time_limit(120);

            $instructors = Instructor::where('school_id', '=', $school->id)
                ->withCount([
                'bookings',
                'bookings as completed_lessons_count' => function ($query) {
                $query->where('status', '=', 'completed');
            }
            ])
                ->orderBy('name')
                ->limit(500)
                ->cursor();

            $rows = [];
            foreach ($instructors as $instructor) {
                $rows[] = [
                    $instructor->name,
                    $instructor->email,
                    $instructor->contact ?? 'N/A',
                    $instructor->license_number ?? 'N/A',
                    ucfirst($instructor->status ?? 'active'),
                    $instructor->availability ?? 'N/A',
                    $instructor->bookings_count,
                    $instructor->completed_lessons_count,
                    $instructor->created_at->format('M d, Y'),
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Instructor List',
            ['Name', 'Email', 'Phone', 'License #', 'Status', 'Availability', 'Total Lessons', 'Completed', 'Registered'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_instructors_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (instructorsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_instructors_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export schedules as PDF
     */
    public function schedulesPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $schedules = TimeSlot::where('school_id', '=', $school->id)
            ->with(['instructors'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(500)
            ->cursor();

        try {
            $pdf = Pdf::loadView('exports.schedules-pdf', [
                'school' => $school,
                'schedules' => $schedules,
                'generatedAt' => now(),
            ]);

            return $pdf->download('schedules-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (schedulesPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_schedules_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export payments as PDF
     */
    public function paymentsPdf(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $query = Payment::where('school_id', $school->id)
            ->when($admin->isBranchSecretary(), function ($q) use ($admin) {
                return $q->where('branch_id', $admin->branch_id);
            }, null)
            ->with(['booking.course', 'enrollmentRequest.course', 'payer']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $payments = $query->whereNotNull('received_at')
            ->orderBy('received_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->cursor();

        try {
            $pdf = Pdf::loadView('exports.payments-pdf', [
                'school' => $school,
                'payments' => $payments,
                'status' => $request->status ?? 'all',
                'generatedAt' => now(),
            ]);

            return $pdf->download('payments-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (paymentsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_payments_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export payments as Excel (styled HTML format)
     */
    public function paymentsExcel(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            set_time_limit(120);

            $payments = Payment::where('school_id', $school->id)
                ->when($admin->isBranchSecretary(), function ($q) use ($admin) {
                    return $q->where('branch_id', $admin->branch_id);
                }, null)
                ->with(['booking.course', 'enrollmentRequest.course', 'payer'])
                ->whereNotNull('received_at')
                ->orderBy('received_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->cursor();

            $rows = [];
            foreach ($payments as $payment) {
                $rows[] = [
                    $payment->received_at ? $payment->received_at->format('M d, Y') : 'N/A',
                    $payment->payer->name ?? 'N/A',
                    $payment->booking->course->title ?? $payment->enrollmentRequest->course->title ?? 'N/A',
                    'PHP ' . number_format($payment->amount, 2),
                    ucfirst($payment->method ?? 'N/A'),
                    $payment->reference ?? $payment->or_number ?? '-',
                    ucfirst($payment->status),
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Payment List',
            ['Date', 'Student', 'Course', 'Amount', 'Method', 'Reference', 'Status'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_payments_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (paymentsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_payments_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export bookings list as Excel
     */
    public function bookingsExcel(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            $bookings = Booking::where('school_id', '=', $school->id)
                ->when($admin->isBranchSecretary(), function ($q) use ($admin) {
                    return $q->where('branch_id', $admin->branch_id);
                }, null)
                ->with(['student', 'instructor', 'course'])
                ->orderBy('scheduled_at', 'desc')
                ->limit(1000)
                ->cursor();

            $rows = [];
            foreach ($bookings as $booking) {
                $rows[] = [
                    $booking->student->name ?? 'N/A',
                    $booking->instructor->name ?? 'Unassigned',
                    $booking->course->title ?? 'N/A',
                    $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y h:i A') : 'N/A',
                    ucfirst($booking->status),
                    $booking->session_grade ?? 'Not Graded',
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Booking List',
                ['Student', 'Instructor', 'Course', 'Scheduled At', 'Status', 'Grade'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_bookings_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (bookingsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_bookings');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export courses as PDF
     */
    public function coursesPdf(School $school, Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        $query = Course::where('school_id', $school->id)
            ->with(['packages']);

        $sort = $request->get('sort', 'title');

        switch ($sort) {
            case 'title_asc':
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
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
                        Booking::STATUS_SCHEDULED, 
                        Booking::STATUS_DONE, 
                        Booking::STATUS_COMPLETED
                    ]);
                }])->orderBy('bookings_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'newest':
                $query->orderBy('sort_order')->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('title', 'asc');
                break;
        }

        $courses = $query->cursor();

        try {
            $pdf = Pdf::loadView('exports.courses-pdf', [
                'school' => $school,
                'courses' => $courses,
                'generatedAt' => now(),
            ]);

            return $pdf->download('courses-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (coursesPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_courses_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export courses list as Excel
     */
    public function coursesExcel(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            $courses = Course::where('school_id', '=', $school->id)
                ->with(['packages'])
                ->orderBy('title')
                ->get();

            $courseStats = Booking::where('school_id', '=', $school->id)
                ->selectRaw('course_id, COUNT(*) as enrollments, SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
                ->groupBy('course_id')
                ->get()
                ->keyBy('course_id');

            $rows = [];
            foreach ($courses as $course) {
                $stats = $courseStats->get($course->id);
                $enrollments = (int)($stats->enrollments ?? 0);
                $completed = (int)($stats->completed ?? 0);
                $rate = $enrollments > 0 ? round(($completed / $enrollments) * 100, 1) . '%' : '0%';
                $rows[] = [
                    $course->title,
                    'PHP ' . number_format($course->price, 2),
                    $course->duration_hours ?? 'N/A',
                    $enrollments,
                    $completed,
                    $rate,
                    ucfirst($course->status ?? 'active'),
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Course List',
                ['Title', 'Price', 'Duration', 'Enrollments', 'Completed', 'Rate', 'Status'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_courses_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (coursesExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_courses');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export administrators and branch managers as PDF
     */
    public function adminsPdf(School $school)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin || $admin->school_id !== $school->id) {
            abort(403);
        }

        set_time_limit(120);

        $admins = \App\Models\Admin::where('school_id', $school->id)
            ->whereIn('role', ['school_admin', 'branch_secretary'])
            ->orderBy('name')
            ->cursor();

        try {
            $pdf = Pdf::loadView('exports.admins-pdf', [
                'school' => $school,
                'admins' => $admins,
                'generatedAt' => now(),
            ]);

            return $pdf->download('admins-list-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('PDF Generation Error (adminsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_admins_pdf');
            return back()->with('error', 'Failed to generate PDF. Please try again later.');
        }
    }

    /**
     * Export administrators and branch managers as Excel
     */
    public function adminsExcel(School $school)
    {
        try {
            $admin = Auth::guard('admin')->user();
            if (!$admin || $admin->school_id !== $school->id) {
                abort(403);
            }

            set_time_limit(120);

            $admins = \App\Models\Admin::where('school_id', $school->id)
                ->whereIn('role', ['school_admin', 'branch_secretary'])
                ->orderBy('name')
                ->cursor();

            $rows = [];
            foreach ($admins as $item) {
                $rows[] = [
                    $item->name,
                    $item->email,
                    $item->contact ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $item->role === 'branch_secretary' ? 'branch_manager' : $item->role)),
                    $item->branch->name ?? 'All Branches',
                    ucfirst($item->status ?? 'active'),
                    $item->created_at->format('M d, Y'),
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Administrators & Managers',
                ['Name', 'Email', 'Phone', 'Role', 'Branch Scope', 'Status', 'Registered'],
                $rows
            );

            return $this->excelResponse($html, $school->slug . '_admins_' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Excel Export Error (adminsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id], $school->id, 'export_admins_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    // ============================================================
    // SHARED HELPERS
    // ============================================================

    /**
     * Build styled HTML table for Excel export (matches ReportController format)
     */
    private function buildExcelHtml(string $title, array $headers, array $rows, ?string $subtitle = null): string
    {
        $date = now()->format('F d, Y');

        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; font-size: 18pt; margin-bottom: 5px; }
        .subtitle { color: #555; font-size: 11pt; margin-bottom: 2px; }
        .date { color: #666; font-size: 10pt; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #2F5496;
            font-size: 11pt;
        }
        td {
            padding: 10px 8px;
            border: 1px solid #D9D9D9;
            font-size: 10pt;
        }
        tr:nth-child(even) { background-color: #F2F2F2; }
        tr:hover { background-color: #E8F4FD; }
        .footer { margin-top: 20px; font-size: 9pt; color: #666; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($title) . '</h1>';

        if ($subtitle) {
            $html .= '
    <div class="subtitle">' . htmlspecialchars($subtitle) . '</div>';
        }

        $html .= '
    <div class="date">Generated: ' . $date . '</div>
    <table>
        <thead>
            <tr>';

        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }

        $html .= '
            </tr>
        </thead>
        <tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '
        </tbody>
    </table>
    <div class="footer">Total Records: ' . count($rows) . '</div>
</body>
</html>';

        return $html;
    }

    /**
     * Return styled Excel response
     */
    private function excelResponse(string $html, string $filename)
    {
        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ============================================================
    // INSTRUCTOR EXPORT METHODS
    // ============================================================

    private function getInstructor(School $school): Instructor
    {
        $instructor = Auth::guard('instructor')->user();
        if (!$instructor || $instructor->school_id !== $school->id) {
            abort(403);
        }
        /** @var Instructor $instructor */
        return $instructor;
    }

    /**
     * Export instructor's students list as PDF
     */
    public function instructorStudentsPdf(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $assignedStudentIds = Booking::where('school_id', $school->id)
                ->where('instructor_id', $instructor->id)
                ->distinct()
                ->pluck('student_id')
                ->toArray();

            $students = Student::where('school_id', $school->id)
                ->whereIn('id', $assignedStudentIds)
                ->with(['bookings' => function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            }])
                ->orderBy('name')
                ->cursor();

            $students->each(function ($student) use ($instructor) {
                $student->completed_sessions = $student->bookings->where('status', 'completed')->count();
                $student->upcoming_sessions = $student->bookings->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now())->count();
                $student->avg_grade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');
            });

            $pdf = Pdf::loadView('exports.instructor-students-pdf', [
                'school' => $school,
                'instructor' => $instructor,
                'students' => $students,
                'generatedAt' => now(),
            ]);

            return $pdf->download('my-students-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor PDF Export Error (instructorStudentsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_students_pdf');
            return back()->with('error', 'Failed to generate PDF report.');
        }
    }

    /**
     * Export instructor's students list as Excel (styled HTML format)
     */
    public function instructorStudentsExcel(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $assignedStudentIds = Booking::where('school_id', $school->id)
                ->where('instructor_id', $instructor->id)
                ->distinct()
                ->pluck('student_id')
                ->toArray();

            $students = Student::where('school_id', $school->id)
                ->whereIn('id', $assignedStudentIds)
                ->with(['bookings' => function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id);
            }])
                ->orderBy('name')
                ->cursor();

            $rows = [];
            foreach ($students as $student) {
                $completedSessions = $student->bookings->where('status', 'completed')->count();
                $upcomingSessions = $student->bookings->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now())->count();
                $avgGrade = $student->bookings->whereNotNull('session_grade')->avg('session_grade');

                $rows[] = [
                    $student->name,
                    $student->contact ?? 'N/A',
                    ucfirst($student->status ?? 'active'),
                    $completedSessions,
                    $upcomingSessions,
                    $avgGrade ? number_format($avgGrade, 1) : 'N/A',
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - My Students',
            ['Name', 'Phone', 'Status', 'Completed Sessions', 'Upcoming', 'Avg Grade'],
                $rows,
                'Instructor: ' . $instructor->name
            );

            return $this->excelResponse($html, 'my-students-' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor Excel Export Error (instructorStudentsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_students_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export instructor's session logs as PDF
     */
    public function instructorSessionsPdf(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $sessions = SessionCompletion::where('school_id', $school->id)
                ->where('instructor_id', $instructor->id)
                ->with(['enrollment.student', 'enrollment.course'])
                ->orderBy('session_date', 'desc')
                ->cursor();

            $totalHours = $sessions->sum('hours_completed');
            $theoreticalCount = $sessions->where('session_type', 'theoretical')->count();
            $practicalCount = $sessions->where('session_type', 'practical')->count();

            $pdf = Pdf::loadView('exports.instructor-sessions-pdf', [
                'school' => $school,
                'instructor' => $instructor,
                'sessions' => $sessions,
                'totalHours' => $totalHours,
                'theoreticalCount' => $theoreticalCount,
                'practicalCount' => $practicalCount,
                'generatedAt' => now(),
            ]);

            return $pdf->download('session-logs-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor PDF Export Error (instructorSessionsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_sessions_pdf');
            return back()->with('error', 'Failed to generate PDF report.');
        }
    }

    /**
     * Export instructor's session logs as Excel (styled HTML format)
     */
    public function instructorSessionsExcel(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $sessions = SessionCompletion::where('school_id', $school->id)
                ->where('instructor_id', $instructor->id)
                ->with(['enrollment.student', 'enrollment.course'])
                ->orderBy('session_date', 'desc')
                ->cursor();

            $rows = [];
            foreach ($sessions as $session) {
                $rows[] = [
                    $session->session_date ? $session->session_date->format('M d, Y') : 'N/A',
                    $session->session_time ?Carbon::parse($session->session_time)->format('h:i A') : 'N/A',
                    $session->enrollment->student->name ?? $session->enrollment->learner->name ?? 'N/A',
                    $session->enrollment->course->title ?? 'N/A',
                    ucfirst($session->session_type),
                    number_format($session->hours_completed, 1),
                    ucfirst($session->status ?? 'completed'),
                    $session->notes ?? '',
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Session Logs',
            ['Date', 'Time', 'Student', 'Course', 'Type', 'Hours', 'Status', 'Notes'],
                $rows,
                'Instructor: ' . $instructor->name
            );

            return $this->excelResponse($html, 'session-logs-' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor Excel Export Error (instructorSessionsExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_sessions_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export instructor's grades report as PDF
     */
    public function instructorGradesPdf(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $studentIds = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->distinct('student_id')
                ->pluck('student_id');

            $students = Student::whereIn('id', $studentIds)
                ->with(['bookings' => function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                    ->orderBy('scheduled_at', 'desc');
            }])
                ->orderBy('name')
                ->cursor();

            $gradedSessions = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->whereNotNull('session_grade')
                ->count();

            $averageGrade = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->whereNotNull('session_grade')
                ->avg('session_grade') ?? 0;

            $pdf = Pdf::loadView('exports.instructor-grades-pdf', [
                'school' => $school,
                'instructor' => $instructor,
                'students' => $students,
                'gradedSessions' => $gradedSessions,
                'averageGrade' => $averageGrade,
                'generatedAt' => now(),
            ]);

            return $pdf->download('grades-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor PDF Export Error (instructorGradesPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_grades_pdf');
            return back()->with('error', 'Failed to generate PDF report.');
        }
    }

    /**
     * Export instructor's grades as Excel (styled HTML format)
     */
    public function instructorGradesExcel(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);

            $studentIds = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->distinct('student_id')
                ->pluck('student_id');

            $students = Student::whereIn('id', $studentIds)
                ->with(['bookings' => function ($query) use ($instructor) {
                $query->where('instructor_id', $instructor->id)
                    ->orderBy('scheduled_at', 'desc');
            }])
                ->orderBy('name')
                ->cursor();

            $rows = [];
            foreach ($students as $student) {
                $totalSessions = $student->bookings->count();
                $gradedBookings = $student->bookings->whereNotNull('session_grade');
                $gradedCount = $gradedBookings->count();
                $avgGrade = $gradedBookings->avg('session_grade');
                $lastBooking = $student->bookings->first();
                $lastGrade = $lastBooking ? $lastBooking->session_grade : null;

                $rows[] = [
                    $student->name,
                    $student->email,
                    $totalSessions,
                    $gradedCount,
                    $avgGrade ? number_format($avgGrade, 1) : 'N/A',
                    $lastBooking && $lastBooking->scheduled_at ? $lastBooking->scheduled_at->format('M d, Y') : 'N/A',
                    $lastGrade ? number_format($lastGrade, 1) : 'N/A',
                ];
            }

            $html = $this->buildExcelHtml(
                $school->name . ' - Grades Report',
            ['Student Name', 'Email', 'Total Sessions', 'Graded', 'Avg Grade', 'Last Session', 'Last Grade'],
                $rows,
                'Instructor: ' . $instructor->name
            );

            return $this->excelResponse($html, 'grades-report-' . date('Y-m-d') . '.xls');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor Excel Export Error (instructorGradesExcel): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_grades_excel');
            return back()->with('error', 'Failed to generate Excel file.');
        }
    }

    /**
     * Export instructor's performance report as PDF
     */
    public function instructorReportsPdf(School $school)
    {
        try {
            $instructor = $this->getInstructor($school);
            $last30Days = Carbon::now()->subDays(30);

            // Consolidate basic stats into single query
            $stats = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->selectRaw("
                    COUNT(*) as total_lessons,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                    COUNT(DISTINCT student_id) as total_students,
                    SUM(CASE WHEN scheduled_at >= ? AND status = 'completed' THEN 1 ELSE 0 END) as attended_last_30,
                    SUM(CASE WHEN scheduled_at >= ? AND status IN ('completed', 'no-show') THEN 1 ELSE 0 END) as total_scheduled_last_30,
                    AVG(CASE WHEN status = 'completed' AND session_grade IS NOT NULL THEN session_grade ELSE NULL END) as avg_grade
                ", [$last30Days, $last30Days])
                ->first();

            $totalLessonsCompleted = (int)$stats->completed_count;
            $totalStudentsTaught = (int)$stats->total_students;
            $totalHoursTaught = $totalLessonsCompleted * 2; // Assuming 2 hours per lesson as per previous logic
            $attendanceRate = $stats->total_scheduled_last_30 > 0
                ? round(($stats->attended_last_30 / $stats->total_scheduled_last_30) * 100, 1)
                : 0;
            $avgGrade = $stats->avg_grade;

            $topStudents = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->where('status', 'completed')
                ->with('student')
                ->selectRaw('student_id, COUNT(*) as lesson_count')
                ->groupBy('student_id')
                ->orderByDesc('lesson_count')
                ->limit(10)
                ->cursor();

            $lessonsByMonth = Booking::where('instructor_id', $instructor->id)
                ->where('school_id', $school->id)
                ->where('status', 'completed')
                ->where('scheduled_at', '>=', Carbon::now()->subMonths(6))
                ->selectRaw('DATE_FORMAT(scheduled_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->cursor();

            $pdf = Pdf::loadView('exports.instructor-reports-pdf', [
                'school' => $school,
                'instructor' => $instructor,
                'totalLessonsCompleted' => $totalLessonsCompleted,
                'totalStudentsTaught' => $totalStudentsTaught,
                'totalHoursTaught' => $totalHoursTaught,
                'attendanceRate' => $attendanceRate,
                'avgGrade' => $avgGrade,
                'topStudents' => $topStudents,
                'lessonsByMonth' => $lessonsByMonth,
                'generatedAt' => now(),
            ]);

            return $pdf->download('performance-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \App\Models\SystemLog::logError('Instructor PDF Export Error (instructorReportsPdf): ' . $e->getMessage(), 'database', $e, ['school_id' => $school->id, 'instructor_id' => $instructor->id], $school->id, 'instructor_export_performance_pdf');
            return back()->with('error', 'Failed to generate performance report.');
        }
    }
}
