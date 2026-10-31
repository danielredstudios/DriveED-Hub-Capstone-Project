<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{Admin, Course, EnrollmentRequest, Instructor, Notification, School, SessionCompletion, Student, SystemLog};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (config('mail.default') !== 'array' || config('session.driver') !== 'cookie') {
    throw new RuntimeException('Use the isolated reproduction instance with array mail and cookie sessions.');
}
$file = base_path('tmp/tdc-pdc-reproduction-state.json');
$out = base_path('docs/screenshots/tdc-pdc-reproduction');
if (!is_dir($out)) mkdir($out, 0755, true);
$email = 'tdc-pdc-reproduction@example.invalid';
$mode = $argv[1] ?? 'inspect';
$snapshot = function (): array {
    $result = [];
    foreach (['students', 'enrollment_requests', 'session_completions', 'enrollments', 'bookings', 'progresses', 'student_lesson_completions', 'phase_progression_requests', 'notifications', 'system_logs'] as $table) {
        $rows = DB::table($table)->orderBy('id')->get();
        $result[$table] = ['count' => $rows->count(), 'sha256' => hash('sha256', $rows->toJson())];
    }
    $combo = EnrollmentRequest::whereHas('course', fn ($query) => $query->where('course_type', 'combo'))->orderBy('id')->get();
    $result['combo_enrollments'] = ['count' => $combo->count(), 'sha256' => hash('sha256', $combo->toJson())];
    return $result;
};

if ($mode === 'prepare') {
    if (is_file($file) || Student::where('email', $email)->exists()) throw new RuntimeException('Clean up the previous reproduction first.');
    $baseline = $snapshot();
    DB::transaction(function () use ($file, $email, $baseline) {
        $school = School::where('slug', 'drived-hub')->firstOrFail();
        $tdcCourse = Course::where('school_id', $school->id)->where('course_type', 'theoretical')->firstOrFail();
        $pdcCourse = Course::where('school_id', $school->id)->where('course_type', 'practical')->where('status', 'active')->firstOrFail();
        $admin = Admin::where('school_id', $school->id)->firstOrFail();
        $instructor = Instructor::where('school_id', $school->id)->firstOrFail();
        $branch = $school->branches()->where('is_active', true)->firstOrFail();
        $password = Str::random(24).'9a!';
        $student = new Student;
        $student->forceFill([
            'school_id' => $school->id, 'branch_id' => $branch->id,
            'name' => 'TDC PDC Reproduction Student', 'email' => $email, 'password' => $password,
            'email_verified_at' => now(), 'role' => 'student', 'status' => 'active',
            'experience_level' => 'new_driver', 'has_passed_theoretical' => true,
            'theoretical_passed_at' => now()->subDay(), 'student_license_status' => 'verified',
            'student_license_verified_at' => now(), 'must_reset_password' => false,
        ])->save();
        $tdc = EnrollmentRequest::create([
            'school_id' => $school->id, 'branch_id' => $branch->id, 'learner_id' => $student->id,
            'course_id' => $tdcCourse->id, 'status' => 'approved', 'payment_status' => 'paid',
            'approved_by' => $admin->id, 'approved_at' => now()->subDays(4),
            'theoretical_passed' => true, 'theoretical_passed_at' => now()->subDay(),
            'theoretical_passed_by' => $admin->id, 'price' => $tdcCourse->price,
            'remarks' => 'Temporary reproduction of a legacy passed TDC enrollment left approved.',
        ]);
        $student->lockToCourse($tdc);
        $limit = (float) $tdc->tdc_hours_limit;
        if ($limit <= 0 || $limit > 24) throw new RuntimeException('Unexpected TDC hour limit.');
        $remaining = $limit;
        for ($day = 3; $day >= 1; $day--) {
            $hours = $day === 1 ? $remaining : round($limit / 3, 2);
            SessionCompletion::create([
                'school_id' => $school->id, 'enrollment_id' => $tdc->id,
                'instructor_id' => $instructor->id, 'session_type' => 'theoretical',
                'hours_completed' => $hours, 'session_date' => now()->subDays($day)->toDateString(),
                'session_time' => '08:00', 'status' => 'completed',
                'notes' => 'Temporary TDC reproduction training log; must remain unchanged during repair.',
            ]);
            $remaining = round($remaining - $hours, 2);
        }
        $sessionsHash = hash('sha256', $tdc->sessionCompletions()->orderBy('id')->get()->toJson());
        $state = ['student_id' => $student->id, 'tdc_id' => $tdc->id, 'pdc_course_id' => $pdcCourse->id,
            'school_slug' => $school->slug, 'branch_id' => $branch->id, 'email' => $email,
            'password' => $password, 'baseline' => $baseline, 'sessions_hash' => $sessionsHash];
        if (file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT)) === false) throw new RuntimeException('Cannot save cleanup metadata.');
    });
}

if (!is_file($file)) throw new RuntimeException('Missing reproduction metadata.');
$state = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
if ($mode === 'cleanup') {
    DB::transaction(function () use ($state, $email) {
        $student = Student::find($state['student_id']);
        if ($student && $student->email !== $email) throw new RuntimeException('Cleanup ownership check failed.');
        Notification::where('notifiable_type', Student::class)->where('notifiable_id', $state['student_id'])->delete();
        Notification::where('type', 'new_enrollment_request')->where('message', 'like', '%Active Student TDC PDC Reproduction Student has requested enrollment%')->delete();
        SystemLog::where('context->student_id', $state['student_id'])->where('context->actor_type', 'student')->delete();
        if ($student) {
            $student->unlockFromCourse();
            $ids = EnrollmentRequest::where('learner_id', $student->id)->pluck('id');
            SessionCompletion::whereIn('enrollment_id', $ids)->delete();
            EnrollmentRequest::where('learner_id', $student->id)->delete();
            $student->delete();
        }
    });
    $current = $snapshot();
    $unchanged = [];
    foreach ($state['baseline'] as $table => $before) {
        if ($current[$table] !== $before) throw new RuntimeException("Original {$table} records changed; inspect cleanup metadata.");
        $unchanged[$table] = $before['count'];
    }
    file_put_contents($out.'/cleanup.json', json_encode(['fixture_removed' => true, 'original_tables_unchanged' => $unchanged, 'mail_driver' => config('mail.default')], JSON_PRETTY_PRINT));
    unlink($file);
    echo "PASS: Temporary student, enrollments, training logs, notifications, and authentication log removed. All original table hashes unchanged.\n";
    exit(0);
}
$student = Student::findOrFail($state['student_id']);
if ($student->email !== $email) throw new RuntimeException('Fixture ownership check failed.');
$tdc = EnrollmentRequest::where('learner_id', $student->id)->findOrFail($state['tdc_id']);

if ($mode === 'repair') {
    DB::transaction(function () use ($tdc) {
        $tdc = EnrollmentRequest::whereKey($tdc->id)->lockForUpdate()->firstOrFail();
        $tdc->markTheoreticalPassed($tdc->theoretical_passed_by, 'Temporary reproduction of the corrected TDC passing flow.');
    });
    $student->refresh();
    $tdc->refresh();
    if ($tdc->status !== 'completed' || $student->is_course_locked || $student->active_enrollment_id !== null) throw new RuntimeException('Repair did not complete and unlock standalone TDC.');
}

$sessions = $tdc->sessionCompletions()->orderBy('id')->get();
if (hash('sha256', $sessions->toJson()) !== $state['sessions_hash']) throw new RuntimeException('Fixture training records changed.');
$pdc = EnrollmentRequest::where('learner_id', $student->id)->where('course_id', $state['pdc_course_id'])->first();
$proof = ['mode' => $mode, 'student_role' => $student->role, 'student_passed_tdc' => $student->has_passed_theoretical,
    'tdc_id' => $tdc->id, 'tdc_status' => $tdc->status, 'is_course_locked' => $student->is_course_locked,
    'active_enrollment_id' => $student->active_enrollment_id, 'tdc_sessions' => $sessions->count(),
    'tdc_hours' => (float) $sessions->sum('hours_completed'), 'training_hash_unchanged' => true,
    'pdc_id' => $pdc?->id, 'pdc_status' => $pdc?->status, 'mail_driver' => config('mail.default')];
if (in_array($mode, ['prepare', 'before', 'repair', 'after'], true)) file_put_contents($out.'/'.$mode.'.json', json_encode($proof, JSON_PRETTY_PRINT));
if ($mode === 'before' && $pdc) throw new RuntimeException('Blocked request unexpectedly created a PDC enrollment.');
if ($mode === 'after' && (!$pdc || $pdc->status !== 'pending' || $tdc->status !== 'completed')) throw new RuntimeException('Successful separate PDC application was not found.');

echo json_encode($proof, JSON_PRETTY_PRINT)."\n";
