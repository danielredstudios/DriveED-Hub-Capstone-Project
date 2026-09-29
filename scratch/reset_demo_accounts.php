<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Admin;
use App\Models\Booking;
use App\Models\EnrollmentRequest;
use App\Models\Instructor;
use App\Models\Notification;
use App\Models\Progress;
use App\Models\SessionCompletion;
use App\Models\Student;
use App\Models\StudentLessonCompletion;
use Illuminate\Support\Facades\DB;

$emails = [
    'admin1@driveedhub.test',
    'instructor1@driveedhub.test',
    'student1@driveedhub.test',
    'student4@driveedhub.test',
    'guest4@driveedhub.test',
];

$students = Student::whereIn('email', [
    'student1@driveedhub.test',
    'student4@driveedhub.test',
    'guest4@driveedhub.test',
])->get();
$instructors = Instructor::where('email', 'instructor1@driveedhub.test')->get();
$admins = Admin::where('email', 'admin1@driveedhub.test')->get();

if ($students->count() !== 3 || $instructors->count() !== 1 || $admins->count() !== 1) {
    fwrite(STDERR, "Expected all five demo accounts but found a different set. Nothing was changed.\n");
    exit(1);
}

$studentIds = $students->pluck('id')->all();
$instructorIds = $instructors->pluck('id')->all();
$adminIds = $admins->pluck('id')->all();
$enrollmentIds = EnrollmentRequest::whereIn('learner_id', $studentIds)->pluck('id')->all();
$bookingIds = Booking::where(function ($query) use ($studentIds, $instructorIds) {
    $query->whereIn('student_id', $studentIds)
        ->orWhereIn('instructor_id', $instructorIds);
})->pluck('id')->all();

$counts = [
    'enrollment_requests' => count($enrollmentIds),
    'bookings' => count($bookingIds),
    'payments' => DB::table('payments')->where(function ($query) use ($studentIds, $enrollmentIds, $bookingIds) {
        $query->whereIn('payer_user_id', $studentIds)
            ->orWhereIn('enrollment_request_id', $enrollmentIds)
            ->orWhereIn('booking_id', $bookingIds);
    })->count(),
    'session_completions' => SessionCompletion::where(function ($query) use ($instructorIds, $enrollmentIds) {
        $query->whereIn('instructor_id', $instructorIds)
            ->orWhereIn('enrollment_id', $enrollmentIds);
    })->count(),
    'progress' => Progress::whereIn('student_id', $studentIds)->count(),
    'lesson_completions' => StudentLessonCompletion::whereIn('student_id', $studentIds)->count(),
    'notifications' => Notification::where(function ($query) use ($students, $instructors, $admins) {
        $query->where(function ($q) use ($students) {
            $q->where('notifiable_type', Student::class)->whereIn('notifiable_id', $students->pluck('id'));
        })->orWhere(function ($q) use ($instructors) {
            $q->where('notifiable_type', Instructor::class)->whereIn('notifiable_id', $instructors->pluck('id'));
        })->orWhere(function ($q) use ($admins) {
            $q->where('notifiable_type', Admin::class)->whereIn('notifiable_id', $admins->pluck('id'));
        });
    })->count(),
];

echo "Accounts preserved: " . implode(', ', $emails) . PHP_EOL;
echo "Records selected for reset:" . PHP_EOL;
foreach ($counts as $table => $count) {
    echo "  {$table}: {$count}" . PHP_EOL;
}

if (!in_array('--reset', $argv, true)) {
    echo PHP_EOL . "Dry run only. Re-run with --reset to delete these records." . PHP_EOL;
    exit(0);
}

DB::transaction(function () use ($studentIds, $instructorIds, $adminIds, $enrollmentIds, $bookingIds, $students, $instructors, $admins): void {
    DB::table('payments')->where(function ($query) use ($studentIds, $enrollmentIds, $bookingIds) {
        $query->whereIn('payer_user_id', $studentIds)
            ->orWhereIn('enrollment_request_id', $enrollmentIds)
            ->orWhereIn('booking_id', $bookingIds);
    })->delete();

    SessionCompletion::where(function ($query) use ($instructorIds, $enrollmentIds) {
        $query->whereIn('instructor_id', $instructorIds)
            ->orWhereIn('enrollment_id', $enrollmentIds);
    })->delete();

    Progress::whereIn('student_id', $studentIds)->delete();
    StudentLessonCompletion::whereIn('student_id', $studentIds)->delete();

    if (DB::getSchemaBuilder()->hasTable('phase_progressions')) {
        DB::table('phase_progressions')->whereIn('enrollment_id', $enrollmentIds)->delete();
    }

    Notification::where(function ($query) use ($students, $instructors, $admins) {
        $query->where(function ($q) use ($students) {
            $q->where('notifiable_type', Student::class)->whereIn('notifiable_id', $students->pluck('id'));
        })->orWhere(function ($q) use ($instructors) {
            $q->where('notifiable_type', Instructor::class)->whereIn('notifiable_id', $instructors->pluck('id'));
        })->orWhere(function ($q) use ($admins) {
            $q->where('notifiable_type', Admin::class)->whereIn('notifiable_id', $admins->pluck('id'));
        });
    })->delete();

    if (DB::getSchemaBuilder()->hasTable('student_action_requests')) {
        DB::table('student_action_requests')->whereIn('requested_by', $admins->pluck('id'))->delete();
    }

    Booking::whereIn('id', $bookingIds)->delete();
    EnrollmentRequest::whereIn('id', $enrollmentIds)->delete();
});

echo PHP_EOL . "Reset complete. Account credentials and profile records were preserved." . PHP_EOL;
