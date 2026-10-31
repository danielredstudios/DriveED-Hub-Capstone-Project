<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SessionCompletion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SessionLoggingService
{
    public function linkManual(SessionCompletion $session, bool $preserveBookingFeedback = false): Booking
    {
        return DB::transaction(function () use ($session, $preserveBookingFeedback) {
            $enrollment = $session->enrollment;
            $enrollment->newQuery()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $date = $session->session_date->toDateString();
            $time = Carbon::parse($session->start_time ?? $session->session_time)->format('H:i:s');
            $booking = $session->booking_id ? Booking::findOrFail($session->booking_id) : Booking::where('school_id', $session->school_id)
                ->where('enrollment_request_id', $session->enrollment_id)->where('instructor_id', $session->instructor_id)
                ->where(function ($query) use ($date, $time) {
                    $query->where('scheduled_at', $date.' '.$time)
                        ->orWhereHas('timeSlot', fn ($slot) => $slot->whereDate('date', $date)->where('start_time', $time));
                })->first();
            $booking ??= new Booking;
            if ($preserveBookingFeedback && $booking->exists) {
                $session->fill([
                    'session_grade' => $booking->session_grade ?? $session->session_grade,
                    'attendance_status' => $booking->attendance_status === 'present' ? 'attended' : ($booking->attendance_status ?? $session->attendance_status),
                    'notes' => $booking->instructor_feedback ?? $session->notes,
                ])->save();
            }
            if ($booking->exists && SessionCompletion::where('booking_id', $booking->id)->whereKeyNot($session->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['session_time' => 'This lesson already has a session log. Edit the existing log.']);
            }
            $status = match ($session->status) {
                'scheduled' => 'scheduled',
                'cancelled' => $session->attendance_status === 'absent' ? 'no-show' : 'cancelled',
                default => $booking->status === 'completed' ? 'completed' : 'done',
            };
            $booking->fill([
                'school_id' => $session->school_id, 'branch_id' => $enrollment->branch_id,
                'student_id' => $enrollment->learner_id, 'course_id' => $enrollment->course_id,
                'enrollment_request_id' => $enrollment->id, 'instructor_id' => $session->instructor_id,
                'scheduled_at' => $date.' '.$time, 'booking_date' => $date,
                'status' => $status, 'session_status' => $status,
                'attendance_status' => $session->attendance_status ?? 'attended',
                'session_grade' => $session->session_grade, 'instructor_feedback' => $session->notes,
            ])->save();
            $session->update(['booking_id' => $booking->id]);
            return $booking;
        });
    }

    public function syncBooking(Booking $booking): ?SessionCompletion
    {
        if (!$booking->enrollment_request_id || !$booking->instructor_id) {
            return null;
        }

        return DB::transaction(function () use ($booking) {
            // Lock the allocation before reading sessions so concurrent logs see the committed total.
            $enrollment = \App\Models\EnrollmentRequest::whereKey($booking->enrollment_request_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $slot = $booking->timeSlot;
            $start = $slot?->start_time ?? $booking->scheduled_at?->format('H:i:s');
            $date = $slot?->date ?? $booking->scheduled_at?->toDateString() ?? $booking->booking_date?->toDateString();
            $type = $slot?->session_type ?? ($booking->course?->course_type === 'practical' ? 'practical' : 'theoretical');
            $session = SessionCompletion::where('booking_id', $booking->id)->first();
            if (!$session) {
                $session = SessionCompletion::whereNull('booking_id')->where('school_id', $booking->school_id)
                    ->where('enrollment_id', $booking->enrollment_request_id)
                    ->where('instructor_id', $booking->instructor_id)->where('session_type', $type)
                    ->whereDate('session_date', $date)->where('session_time', $start)->first();
            }
            $session ??= new SessionCompletion;
            $hours = $session->hours_completed ?? ($slot ? round(Carbon::parse($slot->start_time)->diffInMinutes(Carbon::parse($slot->end_time)) / 60, 2) : 1);
            if ((!$session->exists || $session->status !== 'completed') && !in_array($booking->status, ['cancelled', 'no-show', 'no_show'], true)) {
                \App\Support\TrainingHours::ensureCanLog($enrollment, $type, (float) $hours, $session->exists ? $session->id : null);
            }
            $session->fill([
                'booking_id' => $booking->id, 'school_id' => $booking->school_id,
                'enrollment_id' => $booking->enrollment_request_id, 'instructor_id' => $booking->instructor_id,
                'session_type' => $type, 'hours_completed' => $hours,
                'session_date' => $date, 'session_time' => $start,
                'start_time' => $slot?->start_time ?? $session->start_time,
                'end_time' => $slot?->end_time ?? $session->end_time,
                'status' => in_array($booking->status, ['cancelled', 'no-show', 'no_show']) ? 'cancelled' : 'completed',
                'notes' => $booking->instructor_feedback ?? $session->notes,
                'session_grade' => $booking->session_grade, 'attendance_status' => $booking->attendance_status,
                'logged_by' => $booking->instructor_id,
            ])->save();
            return $session;
        });
    }
}
