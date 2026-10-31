<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Course;
use App\Models\EnrollmentRequest;
use App\Models\Student;

class TdcCompletionEnrollment extends EnrollmentRequest
{
    public array $transitions = [];

    public function update(array $attributes = [], array $options = [])
    {
        $this->forceFill($attributes);
        return true;
    }

    public function resolvePendingPhaseRequest(string $toPhase, int $adminId, string $notes = null): void
    {
        $this->transitions[] = [$toPhase, $adminId, $notes];
    }
}

class TdcCompletionStudent extends Student
{
    public int $unlocks = 0;

    public function hasPassedTheoretical(): bool
    {
        return (bool) $this->has_passed_theoretical;
    }

    public function update(array $attributes = [], array $options = [])
    {
        $this->forceFill($attributes);
        return true;
    }

    public function unlockFromCourse(): void
    {
        $this->unlocks++;
        parent::unlockFromCourse();
    }
}

$checks = 0;
$check = function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
};

foreach (['theoretical', 'combo', 'practical'] as $type) {
    foreach ([false, true] as $alreadyPassed) {
        $student = new TdcCompletionStudent;
        $student->forceFill([
            'has_passed_theoretical' => $alreadyPassed,
            'is_course_locked' => true,
            'active_enrollment_id' => 42,
        ]);
        $enrollment = new TdcCompletionEnrollment(['status' => 'approved']);
        $enrollment->id = 42;
        $enrollment->setRelation('course', new Course(['course_type' => $type]));
        $enrollment->setRelation('learner', $student);
        $sessions = collect([(object) ['id' => 123, 'hours_completed' => 15]]);
        $enrollment->setRelation('sessionCompletions', $sessions);

        $enrollment->markTheoreticalPassed(7, 'Passed TDC');

        $check($enrollment->theoretical_passed && $student->hasPassedTheoretical(), 'Both passing flags must be set.');
        $check($enrollment->theoretical_passed_by === 7 && $enrollment->theoretical_passed_at !== null, 'Passing audit must be recorded.');
        $check($enrollment->transitions === [['practical', 7, 'Passed TDC']], 'Existing phase transition handling must remain intact.');
        $check($enrollment->id === 42 && $enrollment->sessionCompletions === $sessions, 'Original enrollment and loaded sessions must be retained.');

        if ($type === 'theoretical') {
            $check($enrollment->status === 'completed' && $enrollment->completed_at !== null, 'Standalone TDC must be completed.');
            $check(!$student->is_course_locked && $student->active_enrollment_id === null, 'Standalone TDC must release the course lock.');
            $enrollment->markTheoreticalPassed(7, 'Passed TDC');
            $check($student->unlocks === 1, 'Repeated passing must not unlock a later enrollment.');
        } else {
            $check($enrollment->status === 'approved' && $enrollment->completed_at === null, 'Combo and practical enrollments must stay active.');
            $check($student->is_course_locked && $student->active_enrollment_id === 42 && $student->unlocks === 0, 'Combo and practical locks must remain intact.');
        }
    }
}

echo "PASS: {$checks} TDC completion regression checks.\n";
