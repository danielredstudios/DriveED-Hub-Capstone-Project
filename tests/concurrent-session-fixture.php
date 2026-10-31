<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\InstructorTimeSlotController;
use App\Models\{Booking, Course, EnrollmentRequest, Instructor, SessionCompletion, Student};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

$file = base_path('tmp/concurrent-session-state.json');
$mode = $argv[1] ?? '';
$email = 'concurrent-session-recheck@example.invalid';
$snapshot = function () {
    $result = [];
    foreach (['students', 'enrollment_requests', 'session_completions', 'bookings', 'notifications'] as $table) {
        $result[$table] = hash('sha256', DB::table($table)->orderBy('id')->get()->toJson());
    }
    return $result;
};
if ($mode === 'prepare') {
    if (is_file($file) || Student::where('email', $email)->exists()) throw new RuntimeException('Concurrent test already exists.');
    $before = $snapshot();
    $data = DB::transaction(function () use ($email) {
        $instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
        $course = Course::where('school_id', $instructor->school_id)->where('course_type', 'theoretical')->firstOrFail();
        $student = Student::create(['school_id' => $instructor->school_id, 'name' => 'Concurrent Session Recheck', 'email' => $email, 'password' => Illuminate\Support\Str::random(24), 'role' => 'student']);
        $enrollment = EnrollmentRequest::create(['school_id' => $instructor->school_id, 'learner_id' => $student->id, 'course_id' => $course->id, 'status' => 'approved', 'payment_status' => 'paid']);
        $hours = $enrollment->tdc_hours_limit - 1;
        if ($hours < 0) throw new RuntimeException('Unexpected training limit.');
        while ($hours > 0) {
            $chunk = min(8, $hours);
            SessionCompletion::create(['school_id' => $instructor->school_id, 'enrollment_id' => $enrollment->id, 'instructor_id' => $instructor->id, 'session_type' => 'theoretical', 'session_date' => now()->subDay()->toDateString(), 'session_time' => '01:00', 'hours_completed' => $chunk, 'status' => 'completed']);
            $hours -= $chunk;
        }
        $ids = [];
        foreach ([8, 10] as $hour) {
            $booking = Booking::create(['school_id' => $instructor->school_id, 'student_id' => $student->id, 'course_id' => $course->id, 'enrollment_request_id' => $enrollment->id, 'instructor_id' => $instructor->id, 'scheduled_at' => now()->subDay()->startOfDay()->addHours($hour), 'booking_date' => now()->subDay()->toDateString(), 'status' => 'scheduled', 'session_status' => 'scheduled']);
            $ids[] = $booking->id;
        }
        return ['student_id' => $student->id, 'enrollment_id' => $enrollment->id, 'instructor_id' => $instructor->id, 'bookings' => $ids];
    });
    $data['before'] = $before;
    file_put_contents($file, json_encode($data));
    echo "PASS: Concurrent fixture has exactly one training hour remaining.\n";
    exit(0);
}
$data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
if ($mode === 'worker') {
    $instructor = Instructor::findOrFail($data['instructor_id']);
    Auth::guard('instructor')->setUser($instructor);
    $booking = Booking::findOrFail($data['bookings'][(int) $argv[2]]);
    $delayed = false;
    DB::listen(function ($query) use (&$delayed) {
        if (!$delayed && str_contains(strtolower($query->sql), 'enrollment_requests') && str_contains(strtolower($query->sql), 'for update')) {
            $delayed = true;
            usleep(700000);
        }
    });
    $request = Request::create('/lesson/update', 'POST', ['session_status' => 'done', 'attendance_status' => 'attended']);
    $request->setLaravelSession(app('session')->driver());
    $response = app(InstructorTimeSlotController::class)->updateLessonDetails($instructor->school, $booking, $request);
    echo json_encode(['status' => $response->getStatusCode(), 'body' => $response->getData(true)])."\n";
    exit(0);
}
if ($mode === 'verify') {
    $enrollment = EnrollmentRequest::findOrFail($data['enrollment_id']);
    if ((float) $enrollment->used_tdc_hours !== (float) $enrollment->tdc_hours_limit) throw new RuntimeException('Concurrent logging exceeded the allocation.');
    if (SessionCompletion::whereIn('booking_id', $data['bookings'])->count() !== 1) throw new RuntimeException('Expected exactly one accepted session.');
    echo "PASS: Concurrent requests consumed exactly the final hour and created one session.\n";
    exit(0);
}
if ($mode === 'cleanup') {
    DB::transaction(function () use ($data, $email) {
        $student = Student::where('email', $email)->whereKey($data['student_id'])->firstOrFail();
        SessionCompletion::where('enrollment_id', $data['enrollment_id'])->delete();
        Booking::whereIn('id', $data['bookings'])->where('student_id', $student->id)->delete();
        EnrollmentRequest::whereKey($data['enrollment_id'])->where('learner_id', $student->id)->delete();
        $student->delete();
    });
    if ($snapshot() !== $data['before']) throw new RuntimeException('Original records changed during concurrent test.');
    unlink($file);
    echo "PASS: Concurrent test records removed; original table hashes unchanged.\n";
}
