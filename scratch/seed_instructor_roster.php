<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use Carbon\Carbon;

$instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
$student = Student::where('email', 'student1@driveedhub.test')->firstOrFail();
$course = Course::where('school_id', $instructor->school_id)
    ->where('course_type', 'practical')
    ->where('status', 'active')
    ->firstOrFail();

$booking = Booking::firstOrCreate(
    [
        'school_id' => $instructor->school_id,
        'student_id' => $student->id,
        'instructor_id' => $instructor->id,
        'course_id' => $course->id,
        'status' => Booking::STATUS_SCHEDULED,
    ],
    [
        'branch_id' => $student->branch_id,
        'scheduled_at' => Carbon::tomorrow()->setTime(9, 0),
        'booking_date' => Carbon::tomorrow()->setTime(9, 0),
        'payment_status' => 'pending',
        'total_amount' => 0,
        'notes' => 'Demo roster assignment for instructor scoping test.',
    ]
);

echo "Instructor preserved: {$instructor->email} (ID {$instructor->id})" . PHP_EOL;
echo "Student assigned: {$student->email} (ID {$student->id})" . PHP_EOL;
echo "Course: {$course->title} (ID {$course->id})" . PHP_EOL;
echo "Booking ID: {$booking->id}; status: {$booking->status}; scheduled: {$booking->scheduled_at}" . PHP_EOL;
