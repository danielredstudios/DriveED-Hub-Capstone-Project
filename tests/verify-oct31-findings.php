<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Admin, EnrollmentRequest, Instructor, SessionCompletion, School};
use App\Support\TrainingHours;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

$checks = 0;
$check = function ($condition, $message) use (&$checks) {
    if (!$condition) throw new RuntimeException($message);
    $checks++;
    echo "PASS: {$message}\n";
};
$reject = function ($callback, $message) use ($check) {
    try { $callback(); } catch (ValidationException $error) {
        $check(isset($error->errors()['hours_completed']), $message);
        return;
    }
    throw new RuntimeException($message);
};

DB::beginTransaction();
try {
    $school = School::where('slug', 'drived-hub')->firstOrFail();
    $instructor = Instructor::where('school_id', $school->id)->firstOrFail();
    foreach (['theoretical', 'combo'] as $courseType) {
        $enrollment = EnrollmentRequest::where('school_id', $school->id)->where('status', 'approved')
            ->whereHas('course', fn ($query) => $query->where('course_type', $courseType))->firstOrFail();
        $remaining = TrainingHours::remaining($enrollment, 'theoretical');
        $check($remaining > 0, "{$courseType}: fixture has TDC hours available");
        $pdcBefore = TrainingHours::remaining($enrollment, 'practical');
        $session = SessionCompletion::create([
            'school_id' => $school->id, 'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructor->id, 'session_type' => 'theoretical',
            'hours_completed' => $remaining, 'session_date' => now()->toDateString(),
            'session_time' => '01:00', 'status' => 'completed', 'notes' => 'Rolled-back regression check',
        ]);
        $check(TrainingHours::remaining($enrollment, 'theoretical') === 0.0, "{$courseType}: full TDC hours detected");
        $reject(fn () => TrainingHours::ensureCanLog($enrollment, 'theoretical', 0.5), "{$courseType}: additional TDC session rejected with explanation");
        $check(TrainingHours::remaining($enrollment, 'practical') === $pdcBefore, "{$courseType}: PDC allowance remains independent");
        TrainingHours::ensureCanLog($enrollment, 'theoretical', (float) $remaining, $session->id);
        $check(true, "{$courseType}: editing existing session excludes its own hours");
        $session->update(['status' => 'cancelled']);
        $check(TrainingHours::remaining($enrollment, 'theoretical') === $remaining, "{$courseType}: cancelled logs do not consume training hours");
        $reject(fn () => TrainingHours::ensureCanLog($enrollment, 'theoretical', $remaining + 0.5), "{$courseType}: sessions exceeding remaining hours rejected");
    }

    Auth::guard('admin')->setUser(Admin::where('school_id', $school->id)->where('role', 'school_admin')->firstOrFail());
    $request = Request::create('/drived-hub/admin/settings', 'POST', [
        'instructor_removal_notice_days' => 1, 'instructor_selection_mode' => 'auto_assign',
        'background_type' => 'color', 'background_opacity' => 100,
        'booking_cutoff_hours' => 0, 'alert_threshold_pending' => 10,
        'timezone' => 'Asia/Manila', 'invitation_expiry_days' => 7,
        'branch_secretary_limit_per_branch' => 1, 'max_file_size_mb' => 5,
        'admin_header_bg' => '#174b79', 'admin_header_text' => '#ffffff',
        'instructor_header_bg' => '#065f46', 'instructor_header_text' => '#ffffff',
        'student_header_bg' => '#7c2d12', 'student_header_text' => '#ffffff',
    ]);
    $request->setLaravelSession(app('session')->driver());
    app(App\Http\Controllers\AdminController::class)->updateSettings($request, $school);
    $settings = $school->schoolSetting->fresh();
    foreach (['admin' => '#174b79', 'instructor' => '#065f46', 'student' => '#7c2d12'] as $role => $color) {
        $check($settings->{$role.'_header_bg'} === $color && $settings->{$role.'_header_text'} === '#ffffff', "{$role}: header colors saved through settings controller");
    }
} finally {
    DB::rollBack();
}
echo "PASS: {$checks} backend checks; test changes rolled back.\n";
