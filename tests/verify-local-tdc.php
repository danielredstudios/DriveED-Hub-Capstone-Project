<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EnrollmentRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

$output = base_path('docs/screenshots/tdc-local-enrollment');
if (!is_dir($output)) {
    mkdir($output, 0755, true);
}
$baselineFile = base_path('tmp/tdc-local-baseline.json');
$snapshot = function (): array {
    $tables = ['enrollments', 'session_completions', 'student_lesson_completions', 'progresses', 'bookings', 'logs', 'phase_progression_requests'];
    $result = [];
    foreach ($tables as $table) {
        $rows = DB::table($table)->orderBy('id')->get();
        $result[$table] = ['count' => $rows->count(), 'sha256' => hash('sha256', $rows->toJson())];
    }
    $comboIds = DB::table('courses')->where('course_type', 'combo')->pluck('id');
    $comboRows = DB::table('enrollment_requests')->whereIn('course_id', $comboIds)->orderBy('id')->get();
    $comboLocks = DB::table('students')->whereIn('id', $comboRows->pluck('learner_id'))
        ->orderBy('id')->get(['id', 'is_course_locked', 'active_enrollment_id']);
    $result['combo_enrollments'] = ['count' => $comboRows->count(), 'sha256' => hash('sha256', $comboRows->toJson())];
    $result['combo_student_locks'] = ['count' => $comboLocks->count(), 'sha256' => hash('sha256', $comboLocks->toJson())];
    return $result;
};

$mode = $argv[1] ?? 'verify';
if ($mode === 'migrate') {
    if (is_file($baselineFile)) {
        throw new RuntimeException('Baseline already exists; refusing to overwrite evidence.');
    }
    file_put_contents($baselineFile, json_encode($snapshot(), JSON_PRETTY_PRINT));
    $exit = Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_10_31_000001_repair_passed_standalone_tdc_enrollments.php',
        '--force' => true,
    ]);
    echo Artisan::output();
    if ($exit !== 0) {
        throw new RuntimeException('Local migration failed.');
    }
}

$baseline = json_decode(file_get_contents($baselineFile), true, flags: JSON_THROW_ON_ERROR);
$current = $snapshot();
foreach ($baseline as $table => $before) {
    if ($current[$table] !== $before) {
        throw new RuntimeException("{$table} changed since the local migration baseline.");
    }
    echo "PASS: {$table}: {$before['count']} rows unchanged (SHA-256 comparison).\n";
}
$student = App\Models\Student::findOrFail(10);
$tdc = EnrollmentRequest::findOrFail(10);
$pdc = EnrollmentRequest::where('learner_id', 10)->where('course_id', 2)->orderByDesc('id')->first();
if ($mode === 'enrolled') {
    if ($tdc->status !== 'completed' || !$tdc->theoretical_passed || $tdc->sessionCompletions()->count() !== 2) {
        throw new RuntimeException('Luna must retain her completed TDC enrollment and two training sessions.');
    }
    if (!$pdc || $pdc->status !== 'pending' || $pdc->id === $tdc->id || $pdc->school_id !== $tdc->school_id) {
        throw new RuntimeException('A separate pending PDC enrollment must exist in the same school.');
    }
    echo "PASS: Luna completed TDC #{$tdc->id} and submitted separate pending PDC #{$pdc->id}.\n";
}
$summary = [
    'mode' => $mode,
    'migration_applied' => DB::table('migrations')->where('migration', '2026_10_31_000001_repair_passed_standalone_tdc_enrollments')->exists(),
    'unchanged' => $current,
    'student' => ['id' => $student->id, 'name' => $student->name, 'has_passed_theoretical' => $student->has_passed_theoretical, 'is_course_locked' => $student->is_course_locked, 'active_enrollment_id' => $student->active_enrollment_id],
    'tdc' => ['id' => $tdc->id, 'status' => $tdc->status, 'theoretical_passed' => $tdc->theoretical_passed, 'session_count' => $tdc->sessionCompletions()->count()],
    'pdc' => $pdc ? ['id' => $pdc->id, 'status' => $pdc->status, 'course_id' => $pdc->course_id] : null,
];
file_put_contents($output.'/verification.json', json_encode($summary, JSON_PRETTY_PRINT));
echo json_encode(array_diff_key($summary, ['unchanged' => true]), JSON_PRETTY_PRINT)."\n";
