<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Use an isolated database; never migrate or modify the configured application database.
config(['database.default' => 'tdc_repair_test', 'database.connections.tdc_repair_test' => [
    'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
]]);

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->string('course_type');
});
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->boolean('is_course_locked')->default(true);
    $table->unsignedBigInteger('active_enrollment_id')->nullable();
    $table->timestamps();
});
Schema::create('enrollment_requests', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('school_id');
    $table->unsignedBigInteger('learner_id');
    $table->unsignedBigInteger('course_id');
    $table->string('status');
    $table->boolean('theoretical_passed');
    $table->timestamp('theoretical_passed_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
Schema::create('session_completions', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('enrollment_id');
    $table->text('notes');
});

DB::table('courses')->insert([
    ['id' => 1, 'course_type' => 'theoretical'],
    ['id' => 2, 'course_type' => 'combo'],
]);
foreach (range(1, 7) as $id) {
    DB::table('students')->insert(['id' => $id, 'active_enrollment_id' => $id === 4 ? 8 : ($id === 6 ? null : $id)]);
    DB::table('enrollment_requests')->insert([
        'id' => $id, 'school_id' => $id === 5 ? 2 : 1,
        'learner_id' => $id, 'course_id' => $id === 2 ? 2 : 1,
        'status' => $id === 7 ? 'cancelled' : 'approved',
        'theoretical_passed' => $id !== 3,
        'theoretical_passed_at' => '2026-10-01 10:00:00',
    ]);
    DB::table('session_completions')->insert(['enrollment_id' => $id, 'notes' => 'Original training log']);
}
DB::table('enrollment_requests')->insert([
    'id' => 8, 'school_id' => 1, 'learner_id' => 4, 'course_id' => 2,
    'status' => 'approved', 'theoretical_passed' => false,
]);

$check = function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
$snapshot = fn () => json_encode([
    DB::table('enrollment_requests')->orderBy('id')->get(),
    DB::table('students')->orderBy('id')->get(),
    DB::table('session_completions')->orderBy('id')->get(),
]);
$before = $snapshot();
$logs = DB::table('session_completions')->get()->toJson();
$check(Artisan::call('enrollments:repair-passed-tdc', ['--dry-run' => true]) === 0, 'Preview must succeed.');
$check($snapshot() === $before, 'Preview must not write changes.');
$check(Artisan::call('enrollments:repair-passed-tdc', ['--school' => '1']) === 0, 'Repair must succeed.');
foreach ([1, 4, 6] as $id) {
    $row = DB::table('enrollment_requests')->find($id);
    $check($row->status === 'completed' && $row->completed_at === '2026-10-01 10:00:00', 'Passed standalone TDC must complete using its passing date.');
}
foreach ([2, 3, 5, 8] as $id) {
    $check(DB::table('enrollment_requests')->find($id)->status === 'approved', 'Combo, unpassed, and other-school enrollments must remain approved.');
}
$check(DB::table('enrollment_requests')->find(7)->status === 'cancelled', 'Cancelled enrollments must remain cancelled.');
foreach ([1, 6] as $id) {
    $student = DB::table('students')->find($id);
    $check(!$student->is_course_locked && $student->active_enrollment_id === null, 'Stale TDC locks must clear.');
}
$student = DB::table('students')->find(4);
$check($student->is_course_locked && $student->active_enrollment_id === 8, 'A newer Combo lock must remain intact.');
$check(DB::table('session_completions')->get()->toJson() === $logs, 'Training logs must remain unchanged.');
$after = $snapshot();
Artisan::call('enrollments:repair-passed-tdc', ['--school' => '1']);
$check($snapshot() === $after, 'Repeated repairs must be idempotent.');
echo "PASS: TDC repair preview, school scope, completion, lock ownership, Combo exclusion, training logs, and repeat runs.\n";

$migration = require __DIR__.'/../database/migrations/2026_10_31_000001_repair_passed_standalone_tdc_enrollments.php';
$migrationOptions = [
    '--database' => 'tdc_repair_test',
    '--path' => 'database/migrations/2026_10_31_000001_repair_passed_standalone_tdc_enrollments.php',
    '--force' => true,
];
$check(Artisan::call('migrate', $migrationOptions) === 0, 'Deployment migration runner must succeed.');
$check(DB::table('enrollment_requests')->find(5)->status === 'completed', 'Deployment migration must repair remaining schools.');
$check(!DB::table('students')->find(5)->is_course_locked, 'Deployment migration must release stale locks.');
$afterMigration = $snapshot();
$check(Artisan::call('migrate', $migrationOptions) === 0, 'Later deployments must succeed without repeating the migration.');
$migration->up();
$migration->down();
$check($snapshot() === $afterMigration, 'Reruns and rollback must preserve repaired data.');
$check(DB::table('session_completions')->get()->toJson() === $logs, 'Deployment repair must preserve training logs.');
echo "PASS: deployment migration runs repair across schools; reruns and rollback preserve repaired data and training logs.\n";
