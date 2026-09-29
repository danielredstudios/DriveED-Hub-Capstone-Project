<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

DB::transaction(function (): void {
    $instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
    $student = Student::where('email', 'student1@driveedhub.test')->firstOrFail();
    $schoolId = $instructor->school_id;
    $branchId = $student->branch_id ?: $instructor->branch_id;
    $practicalCourse = Course::where('school_id', $schoolId)
        ->where('course_type', 'practical')
        ->where('status', 'active')
        ->firstOrFail();
    $theoreticalCourse = Course::where('school_id', $schoolId)
        ->where('course_type', 'theoretical')
        ->where('status', 'active')
        ->firstOrFail();
    $package = CoursePackage::where('course_id', $practicalCourse->id)->orderBy('sort_order')->first();

    $enrollment = EnrollmentRequest::firstOrCreate(
        [
            'learner_id' => $student->id,
            'course_id' => $practicalCourse->id,
        ],
        [
            'school_id' => $schoolId,
            'branch_id' => $branchId,
            'status' => 'approved',
            'payment_status' => 'paid',
            'requested_license_type' => $practicalCourse->license_type ?? 'professional',
            'requested_dl_code' => 'B',
            'experience_level' => 'experienced',
            'package_id' => $package?->id,
            'price' => $package?->price ?? $practicalCourse->price ?? 0,
            'approved_at' => now(),
            'enrolled_at' => now(),
        ]
    );

    $tomorrow = Carbon::tomorrow()->startOfDay();
    $practicalSlot = TimeSlot::firstOrCreate(
        [
            'school_id' => $schoolId,
            'course_id' => $practicalCourse->id,
            'date' => $tomorrow->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
        ],
        [
            'branch_id' => $branchId,
            'session_type' => 'practical',
            'max_instructors' => 1,
            'max_students' => 1,
            'status' => 'open',
            'notes' => 'Demo cancellable practical slot.',
        ]
    );
    $practicalSlot->instructors()->syncWithoutDetaching([
        $instructor->id => [
            'school_id' => $schoolId,
            'assignment_type' => 'admin_assigned',
        ],
    ]);

    $booking = Booking::updateOrCreate(
        [
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'time_slot_id' => $practicalSlot->id,
        ],
        [
            'branch_id' => $branchId,
            'instructor_id' => $instructor->id,
            'course_id' => $practicalCourse->id,
            'enrollment_request_id' => $enrollment->id,
            'scheduled_at' => $tomorrow->copy()->setTime(9, 0),
            'booking_date' => $tomorrow->copy()->setTime(9, 0),
            'status' => Booking::STATUS_SCHEDULED,
            'payment_status' => 'paid',
            'total_amount' => $package?->price ?? $practicalCourse->price ?? 0,
            'notes' => 'Demo cancellable practical booking.',
        ]
    );

    $batchGroup = 'demo-tdc-batch-instructor1';
    for ($day = 1; $day <= 3; $day++) {
        $date = $tomorrow->copy()->addDays($day - 1);
        $slot = TimeSlot::updateOrCreate(
            [
                'school_id' => $schoolId,
                'course_id' => $theoreticalCourse->id,
                'date' => $date->toDateString(),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'batch_group' => $batchGroup,
                'batch_day_number' => $day,
            ],
            [
                'branch_id' => $branchId,
                'session_type' => 'theoretical',
                'max_instructors' => 1,
                'max_students' => 30,
                'status' => 'open',
                'notes' => "Demo TDC Batch Day {$day} [{$batchGroup} Day {$day}]",
            ]
        );
        $slot->instructors()->syncWithoutDetaching([
            $instructor->id => [
                'school_id' => $schoolId,
                'assignment_type' => 'admin_assigned',
            ],
        ]);
    }

    echo "Seeded demo data:" . PHP_EOL;
    echo "  Approved practical enrollment: {$enrollment->id}" . PHP_EOL;
    echo "  Cancellable practical booking: {$booking->id}" . PHP_EOL;
    echo "  TDC batch group: {$batchGroup} (3 days)" . PHP_EOL;
    echo "  Credentials unchanged for all accounts." . PHP_EOL;
});
