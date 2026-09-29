<?php

namespace App\Services;

use App\Models\SessionCompletion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulingConflictService
{
    /**
     * Check if an instructor is available for a specific time slot
     *
     * @param int $instructorId
     * @param string $date Date in Y-m-d format
     * @param string $startTime Time in H:i:s or H:i format
     * @param string $endTime Time in H:i:s or H:i format
     * @param int|null $excludeSessionId Session ID to exclude (for updates)
     * @return array ['available' => bool, 'conflicts' => array, 'message' => string]
     */
    public function checkInstructorAvailability($instructorId, $date, $startTime, $endTime, $excludeSessionId = null)
    {
        // Convert times to Carbon instances for comparison
        $requestStart = Carbon::parse($date . ' ' . $startTime);
        $requestEnd = Carbon::parse($date . ' ' . $endTime);

        // Find overlapping sessions for this instructor
        $query = SessionCompletion::where('instructor_id', $instructorId)
            ->where('session_date', $date)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->orWhereNull('status'); // Handle old records without status
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        $existingSessions = $query->get();

        $conflicts = [];
        foreach ($existingSessions as $session) {
            $sessionStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
            $sessionEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

            // Check if times overlap
            // Overlap occurs if: requestStart < sessionEnd AND requestEnd > sessionStart
            if ($requestStart->lessThan($sessionEnd) && $requestEnd->greaterThan($sessionStart)) {
                $conflicts[] = [
                    'id' => $session->id,
                    'student_name' => $session->enrollment->student->name ?? 'Unknown',
                    'start_time' => Carbon::parse($session->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($session->end_time)->format('H:i'),
                    'date' => $session->session_date->format('Y-m-d'),
                    'status' => $session->status ?? 'completed',
                    'session_type' => $session->session_type,
                ];
            }
        }

        if (empty($conflicts)) {
            return [
                'available' => true,
                'conflicts' => [],
                'message' => 'Instructor is available for this time slot.',
            ];
        }

        $conflictTimes = array_map(function ($c) {
            return Carbon::parse($c['start_time'])->format('g:i A') . ' - ' . Carbon::parse($c['end_time'])->format('g:i A');
        }, $conflicts);

        return [
            'available' => false,
            'conflicts' => $conflicts,
            'message' => 'Instructor has conflicting sessions at: ' . implode(', ', $conflictTimes),
        ];
    }

    /**
     * Get available time slots for an instructor on a specific date
     *
     * @param int $instructorId
     * @param string $date Date in Y-m-d format
     * @param int $durationMinutes Duration of the session in minutes
     * @return array Array of available time slots
     */
    public function getAvailableTimeSlots($instructorId, $date, $durationMinutes = 60)
    {
        // Get all booked sessions for this instructor on this date
        $bookedSessions = SessionCompletion::where('instructor_id', $instructorId)
            ->where('session_date', $date)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->where(function($q) {
                $q->where('status', '!=', 'cancelled')
                  ->orWhereNull('status');
            })
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        // Define working hours (8 AM to 6 PM)
        $workStart = Carbon::parse($date . ' 08:00:00');
        $workEnd = Carbon::parse($date . ' 18:00:00');

        $availableSlots = [];
        $currentTime = $workStart->copy();

        foreach ($bookedSessions as $session) {
            $sessionStart = Carbon::parse($date . ' ' . $session->start_time);
            $sessionEnd = Carbon::parse($date . ' ' . $session->end_time);

            // Check if there's a gap before this session
            if ($currentTime->diffInMinutes($sessionStart) >= $durationMinutes) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $sessionStart->format('H:i'),
                    'start_formatted' => $currentTime->format('g:i A'),
                    'end_formatted' => $sessionStart->format('g:i A'),
                ];
            }

            $currentTime = $sessionEnd->copy();
        }

        // Check if there's time left at the end of the day
        if ($currentTime->diffInMinutes($workEnd) >= $durationMinutes) {
            $availableSlots[] = [
                'start' => $currentTime->format('H:i'),
                'end' => $workEnd->format('H:i'),
                'start_formatted' => $currentTime->format('g:i A'),
                'end_formatted' => $workEnd->format('g:i A'),
            ];
        }

        return $availableSlots;
    }

    /**
     * Find alternative time slots when there's a conflict
     *
     * @param int $instructorId
     * @param string $date
     * @param int $durationMinutes
     * @param int $limit Number of suggestions to return
     * @return array
     */
    public function suggestAlternativeTimeSlots($instructorId, $date, $durationMinutes = 60, $limit = 3)
    {
        $availableSlots = $this->getAvailableTimeSlots($instructorId, $date, $durationMinutes);
        
        // Return up to $limit suggestions
        return array_slice($availableSlots, 0, $limit);
    }

    /**
     * Check for conflicts across multiple instructors (useful for batch scheduling)
     *
     * @param array $sessions Array of ['instructor_id' => int, 'date' => string, 'start_time' => string, 'end_time' => string]
     * @return array
     */
    public function checkBulkConflicts(array $sessions)
    {
        $results = [];

        foreach ($sessions as $index => $session) {
            $check = $this->checkInstructorAvailability(
                $session['instructor_id'],
                $session['date'],
                $session['start_time'],
                $session['end_time'],
                $session['exclude_session_id'] ?? null
            );

            $results[$index] = [
                'session' => $session,
                'available' => $check['available'],
                'conflicts' => $check['conflicts'],
                'message' => $check['message'],
            ];
        }

        return $results;
    }
}
