<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Instructor;
use App\Models\School;
use App\Models\SessionCompletion;
use App\Models\Student;
use App\Support\SessionReportWorkbook;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionReportController extends Controller
{
    public function download(Request $request, School $school, string $format)
    {
        $role = explode('.', $request->route()->getName())[1];
        abort_unless(in_array($role, ['admin', 'student', 'instructor'], true), 403);
        $user = Auth::guard($role)->user();
        abort_unless($user && (int) $user->school_id === (int) $school->id, 403);

        // Sanitize empty string filters before validation
        $rawFilters = $request->all();
        foreach (['start', 'end', 'student_id', 'instructor_id', 'session_id', 'booking_id'] as $key) {
            if (isset($rawFilters[$key]) && trim((string) $rawFilters[$key]) === '') {
                unset($rawFilters[$key]);
            }
        }
        $request->replace($rawFilters);

        $filters = $request->validate([
            'start' => 'nullable|date_format:Y-m-d',
            'end' => ['nullable', 'date_format:Y-m-d', ...($request->filled('start') ? ['after_or_equal:start'] : [])],
            'student_id' => 'nullable|integer',
            'instructor_id' => 'nullable|integer',
            'session_id' => 'nullable|integer',
            'booking_id' => 'nullable|integer',
        ]);

        $bookings = Booking::where('school_id', $school->id)->with(['student', 'instructor', 'course', 'timeSlot']);
        $logs = SessionCompletion::where('school_id', $school->id)->with(['enrollment.learner', 'enrollment.course', 'instructor']);

        // Per-session filtering
        if (!empty($filters['session_id'])) {
            $logs->where('id', $filters['session_id']);
            $bookings->whereRaw('0 = 1');
        } elseif (!empty($filters['booking_id'])) {
            $bookings->where('id', $filters['booking_id']);
            $logs->whereRaw('0 = 1');
        }

        if ($role === 'student') {
            $bookings->where('student_id', $user->id);
            $logs->whereHas('enrollment', fn ($q) => $q->where('learner_id', $user->id));
        } elseif ($role === 'instructor') {
            $bookings->where('instructor_id', $user->id);
            $logs->where('instructor_id', $user->id);
        } elseif ($user->isBranchSecretary()) {
            abort_unless($user->branch_id, 403);
            $bookings->where('branch_id', $user->branch_id);
            $logs->whereHas('enrollment', fn ($q) => $q->where('branch_id', $user->branch_id));
        }

        if (!empty($filters['student_id']) && $role !== 'student') {
            $bookings->where('student_id', $filters['student_id']);
            $logs->whereHas('enrollment', fn ($q) => $q->where('learner_id', $filters['student_id']));
        }

        if (!empty($filters['instructor_id']) && $role !== 'instructor') {
            $bookings->where('instructor_id', $filters['instructor_id']);
            $logs->where('instructor_id', $filters['instructor_id']);
        }

        foreach (['start' => '>=', 'end' => '<='] as $filter => $operator) {
            if (!empty($filters[$filter])) {
                $bookings->where(function ($q) use ($operator, $filters, $filter) {
                    $q->whereDate('booking_date', $operator, $filters[$filter])
                      ->orWhereDate('scheduled_at', $operator, $filters[$filter])
                      ->orWhereHas('timeSlot', fn ($sq) => $sq->whereDate('date', $operator, $filters[$filter]));
                });
                $logs->whereDate('session_date', $operator, $filters[$filter]);
            }
        }
        $bookings = $bookings->orderBy('booking_date')->orderBy('id')->limit(5001)->get();
        $logs = $logs->orderBy('session_date')->orderBy('id')->limit(5001)->get();
        abort_if($bookings->count() + $logs->count() > 5000, 422, 'Too many records selected. Please choose a narrower date range (maximum 5,000 records).');

        $rows = $bookings->map(fn ($b) => [
            'date' => ($b->booking_date?->format('Y-m-d') ?? $b->scheduled_at?->format('Y-m-d') ?? ($b->timeSlot?->date ? \Carbon\Carbon::parse($b->timeSlot->date)->format('Y-m-d') : '')).($b->timeSlot ? "\n".substr($b->timeSlot->start_time ?? '', 0, 5).' - '.substr($b->timeSlot->end_time ?? '', 0, 5) : ($b->scheduled_at ? "\n".$b->scheduled_at->format('H:i') : '')),
            'student' => $b->student?->name ?? 'Not set',
            'course' => $b->course?->title ?? 'Not set',
            'instructor' => $b->instructor?->name ?? 'Not assigned',
            'type' => ucfirst($b->session_type ?? 'Practical'),
            'hours' => (float) ($b->duration_hours ?? 0),
            'status' => ucfirst($b->status ?? 'Pending'),
            'attendance' => $b->attendance_status ?? '',
            'grade' => $b->session_grade !== null ? (string) $b->session_grade : '',
            'notes' => $b->notes ?? '',
            'instructor_feedback' => $b->instructor_feedback ?? '',
            'student_feedback' => $b->student_feedback ?? '',
            'skills' => is_array($b->skills_practiced) ? implode(', ', $b->skills_practiced) : ($b->skills_practiced ?? ''),
            'source' => 'Booked lesson #'.$b->id,
        ])->concat($logs->map(fn ($s) => [
            'date' => ($s->session_date?->format('Y-m-d') ?? '').($s->start_time ? "\n".substr($s->start_time, 0, 5).' - '.substr($s->end_time ?? '', 0, 5) : ''),
            'student' => $s->enrollment?->learner?->name ?? 'Not set',
            'course' => $s->enrollment?->course?->title ?? 'Not set',
            'instructor' => $s->instructor?->name ?? 'Not set',
            'type' => ucfirst($s->session_type ?? 'Completed'),
            'hours' => (float) ($s->hours_completed ?? 0),
            'status' => ucfirst($s->status ?? 'completed'),
            'attendance' => '',
            'grade' => '',
            'notes' => $s->notes ?? '',
            'instructor_feedback' => '',
            'student_feedback' => '',
            'skills' => '',
            'source' => 'Completed session #'.$s->id,
        ]))->sortBy('date')->values();

        $isPerSession = !empty($filters['session_id']) || !empty($filters['booking_id']);
        $targetId = $filters['session_id'] ?? ($filters['booking_id'] ?? null);

        $selectedStudent = !empty($filters['student_id']) ? Student::find($filters['student_id']) : null;
        $selectedInstructor = !empty($filters['instructor_id']) ? Instructor::find($filters['instructor_id']) : null;

        $filterParts = [];
        if (!empty($filters['start']) || !empty($filters['end'])) {
            $filterParts[] = ($filters['start'] ?? 'Beginning').' to '.($filters['end'] ?? 'Present');
        }
        if ($selectedStudent) {
            $filterParts[] = 'Student: '.$selectedStudent->name;
        }
        if ($selectedInstructor) {
            $filterParts[] = 'Instructor: '.$selectedInstructor->name;
        }

        $periodText = $isPerSession
            ? (!empty($filters['session_id']) ? 'Single Session Record #'.$filters['session_id'] : 'Single Booked Lesson #'.($filters['booking_id'] ?? ''))
            : (!empty($filterParts) ? implode(' | ', $filterParts) : 'All Records');

        $data = [
            'school' => $school,
            'rows' => $rows,
            'generatedAt' => now(),
            'preparedFor' => $user->name.' ('.ucfirst($role).')',
            'period' => $periodText,
            'filterSubtitle' => !empty($filterParts) ? implode(' | ', $filterParts) : 'All Records',
            'summary' => [
                'Booked lessons' => $bookings->count(),
                'Completed sessions' => $logs->count(),
                'Graded lessons' => $bookings->whereNotNull('session_grade')->count(),
                'Completed training hours' => round($logs->sum('hours_completed'), 1),
            ],
        ];

        $filenameParts = ['session-logs', $school->slug];
        if ($isPerSession) {
            $filenameParts = ['session-log', $targetId, $school->slug];
        } elseif ($selectedStudent) {
            $filenameParts[] = 'student-'.$selectedStudent->id;
        } elseif ($selectedInstructor) {
            $filenameParts[] = 'instructor-'.$selectedInstructor->id;
        }
        $filenameParts[] = now()->format('Y-m-d');
        $filename = implode('-', $filenameParts);

        if ($format === 'pdf') {
            if (!file_exists(storage_path('fonts'))) {
                @mkdir(storage_path('fonts'), 0775, true);
            }
            ini_set('memory_limit', '512M');
            set_time_limit(120);

            $pdf = Pdf::loadView('exports.session-report-pdf', $data)->setPaper('a4', 'landscape');
            $pdf->render();
            $canvas = $pdf->getDomPDF()->getCanvas();
            $font = $pdf->getDomPDF()->getFontMetrics()->getFont('DejaVu Sans', 'normal');
            $canvas->page_text(745, 565, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0.32, 0.39, 0.46]);
            return $pdf->download($filename.'.pdf');
        }

        abort_unless($format === 'xlsx', 404);
        return response()->streamDownload(function () use ($data) {
            echo app(SessionReportWorkbook::class)->build($data);
        }, $filename.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
