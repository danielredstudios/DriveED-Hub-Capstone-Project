<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\{EnrollmentRequest, Instructor, SessionCompletion};
use App\Support\TrainingHours;
use Illuminate\Support\Facades\DB;

$file = base_path('tmp/oct31-hours-fixture.json');
$marker = 'October 31 training-hour browser regression fixture';
if (($argv[1] ?? '') === 'cleanup') {
    if (is_file($file)) {
        $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        SessionCompletion::whereIn('id', $data['session_ids'])->where('notes', $marker)->delete();
        unlink($file);
    }
    echo "PASS: Temporary training-hour fixture removed.\n";
    exit(0);
}
if (is_file($file)) throw new RuntimeException('Clean up the previous hours fixture first.');
$data = DB::transaction(function () use ($marker, $file) {
    $instructor = Instructor::where('email', 'instructor1@driveedhub.test')->firstOrFail();
    $enrollment = EnrollmentRequest::where('school_id', $instructor->school_id)->where('status', 'approved')
        ->whereHas('course', fn ($query) => $query->where('course_type', 'theoretical'))
        ->get()->first(fn ($item) => TrainingHours::remaining($item, 'theoretical') > 0);
    if (!$enrollment) throw new RuntimeException('No eligible browser test enrollment.');
    $remaining = TrainingHours::remaining($enrollment, 'theoretical');
    $ids = [];
    while ($remaining > 0) {
        $hours = min(8, $remaining);
        $session = SessionCompletion::create([
            'school_id' => $instructor->school_id, 'enrollment_id' => $enrollment->id,
            'instructor_id' => $instructor->id, 'session_type' => 'theoretical',
            'hours_completed' => $hours, 'session_date' => now()->toDateString(),
            'session_time' => '01:00', 'status' => 'completed', 'notes' => $marker,
        ]);
        $ids[] = $session->id;
        $remaining = round($remaining - $hours, 2);
    }
    $data = ['enrollment_id' => $enrollment->id, 'session_ids' => $ids];
    if (file_put_contents($file, json_encode($data)) === false) throw new RuntimeException('Cannot save cleanup metadata.');
    return $data;
});
echo json_encode($data)."\n";
