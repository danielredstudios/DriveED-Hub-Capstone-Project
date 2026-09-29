<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enforcedStatuses = ['pending', 'scheduled', 'confirmed', 'done', 'completed'];

        // Phase 1: Remediation (Best-effort backfill)
        $unlinkedBookings = DB::table('bookings')
            ->whereIn('status', $enforcedStatuses)
            ->whereNull('enrollment_request_id')
            ->get();

        foreach ($unlinkedBookings as $booking) {
            $enrollment = DB::table('enrollment_requests')
                ->where('learner_id', $booking->student_id)
                ->where('school_id', $booking->school_id)
                ->where('course_id', $booking->course_id)
                ->whereIn('status', ['approved', 'completed'])
                ->orderBy('approved_at', 'desc')
                ->first();

            if (!$enrollment) {
                // Ghost Booking detected: Create legacy enrollment record
                $enrollmentId = DB::table('enrollment_requests')->insertGetId([
                    'school_id' => $booking->school_id,
                    'learner_id' => $booking->student_id,
                    'course_id' => $booking->course_id,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'enrolled_at' => now(),
                    'approved_at' => now(),
                    'completed_at' => now(),
                    'remarks' => 'Auto-created during Multi-Course Linkage Migration (Ghost Booking cleanup)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $enrollmentIdValue = $enrollmentId;
            } else {
                $enrollmentIdValue = $enrollment->id;
            }

            DB::table('bookings')
                ->where('id', $booking->id)
                ->update(['enrollment_request_id' => $enrollmentIdValue]);
        }

        // Phase 2: Verification (The "Gate")
        $unlinked = DB::table('bookings')
            ->whereIn('status', $enforcedStatuses)
            ->whereNull('enrollment_request_id')
            ->count();

        if ($unlinked > 0) {
            throw new RuntimeException("Hardening gate failed: {$unlinked} booking records are still missing enrollment_request_id linkage after remediation.");
        }

        $mismatch = DB::table('bookings as b')
            ->join('enrollment_requests as er', 'er.id', '=', 'b.enrollment_request_id')
            ->whereIn('b.status', $enforcedStatuses)
            ->where(function ($query) {
                $query->whereColumn('b.school_id', '!=', 'er.school_id')
                    ->orWhereColumn('b.student_id', '!=', 'er.learner_id')
                    ->orWhereColumn('b.course_id', '!=', 'er.course_id');
            })
            ->count();

        if ($mismatch > 0) {
            throw new RuntimeException("Hardening gate failed: {$mismatch} linked bookings do not match enrollment school/student/course constraints.");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Gate-only migration. No schema changes to reverse.
    }
};
