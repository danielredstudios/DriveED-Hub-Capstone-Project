<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Booking, EnrollmentRequest, Instructor, Invitation, School, SessionCompletion, Student, TimeSlot};
use Illuminate\Support\Facades\DB;

$file = base_path('tmp/bug-report-browser-fixture.json');
$school = School::where('slug', 'drived-hub')->firstOrFail();
$mode = $argv[1] ?? 'prepare';
if ($mode === 'cleanup') {
    if (!is_file($file)) exit(0);
    $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
    DB::transaction(function () use ($data, $school) {
        SessionCompletion::where('booking_id', $data['booking_id'])->delete();
        Booking::whereKey($data['booking_id'])->delete();
        TimeSlot::whereKey($data['slot_id'])->first()?->instructors()->detach();
        TimeSlot::whereKey($data['slot_id'])->delete();
        Invitation::whereKey($data['invitation_id'])->delete();
        $school->schoolSetting->update($data['original_branding']);
    });
    unlink($file);
    echo "Browser fixtures removed; original branding restored.\n";
    exit(0);
}
if ($mode === 'hidden') {
    $school->schoolSetting->update(['login_show_school_name' => false, 'login_logo_image' => null]);
    exit(0);
}
if (is_file($file)) throw new RuntimeException('Clean up the previous browser fixture first.');
$data = DB::transaction(function () use ($school, $file) {
    $instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
    $student = Student::where('email', 'student1@driveedhub.test')->firstOrFail();
    $enrollment = EnrollmentRequest::where('learner_id', $student->id)->where('school_id', $school->id)->firstOrFail();
    $slot = TimeSlot::create(['school_id' => $school->id, 'branch_id' => $enrollment->branch_id, 'course_id' => $enrollment->course_id, 'session_type' => $enrollment->course->course_type === 'practical' ? 'practical' : 'theoretical', 'date' => now()->toDateString(), 'start_time' => '00:30:00', 'end_time' => '01:30:00', 'status' => 'open', 'max_students' => 1, 'max_instructors' => 1, 'notes' => 'Bug report QA fixture']);
    $slot->instructors()->attach($instructor->id, ['school_id' => $school->id, 'assignment_type' => 'admin_assigned']);
    $booking = Booking::create(['school_id' => $school->id, 'branch_id' => $enrollment->branch_id, 'student_id' => $student->id, 'instructor_id' => $instructor->id, 'course_id' => $enrollment->course_id, 'enrollment_request_id' => $enrollment->id, 'time_slot_id' => $slot->id, 'scheduled_at' => now()->toDateString().' 00:30:00', 'booking_date' => now()->toDateString(), 'status' => 'scheduled', 'notes' => 'Bug report QA fixture']);
    $invitation = Invitation::create(['school_id' => $school->id, 'branch_id' => $enrollment->branch_id, 'role' => 'student', 'email' => 'qa-email-tracking@example.invalid', 'token' => Illuminate\Support\Str::random(64), 'payload' => ['name' => 'QA email tracking fixture'], 'expires_at' => now()->addDays(7)]);
    $original = $school->schoolSetting->only(['login_show_school_name', 'login_logo_image', 'login_school_name_text', 'login_header_bg_color', 'login_header_text_color']);
    $school->schoolSetting->update(['login_show_school_name' => true, 'login_logo_image' => null, 'login_school_name_text' => 'DriveED Hub', 'login_header_bg_color' => '#174b79', 'login_header_text_color' => '#ffffff']);
    $data = ['booking_id' => $booking->id, 'slot_id' => $slot->id, 'invitation_id' => $invitation->id, 'original_branding' => $original, 'date' => now()->toDateString()];
    if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT)) === false) throw new RuntimeException('Cannot save fixture rollback metadata.');
    return $data;
});
echo "Prepared isolated browser fixtures. No emails sent.\n";
