<?php

namespace App\Console\Commands;

use App\Models\EnrollmentRequest;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairPassedTdcEnrollments extends Command
{
    protected $signature = 'enrollments:repair-passed-tdc
        {--dry-run : Preview repairs without saving changes}
        {--school= : Limit repairs to a school ID}';

    protected $description = 'Complete approved standalone TDC enrollments already marked passed and release their stale locks';

    public function handle(): int
    {
        $school = $this->option('school');
        if ($school !== null && (!ctype_digit((string) $school) || (int) $school < 1)) {
            $this->error('--school must be a positive school ID.');
            return self::INVALID;
        }

        $dryRun = (bool) $this->option('dry-run');
        $completed = $unlocked = $failed = 0;

        $this->candidates()
            ->when($school !== null, fn ($query) => $query->where('school_id', $school))
            ->chunkById(100, function ($enrollments) use ($dryRun, &$completed, &$unlocked, &$failed) {
                foreach ($enrollments as $candidate) {
                    try {
                        $result = DB::transaction(function () use ($candidate, $dryRun) {
                            $student = Student::whereKey($candidate->learner_id)->lockForUpdate()->first();
                            $enrollment = $this->candidates()->whereKey($candidate->id)->lockForUpdate()->first();
                            if (!$enrollment) {
                                return null;
                            }

                            // A newer course, including a Combo, may now own the student's lock.
                            $unlock = $student && (
                                (string) $student->active_enrollment_id === (string) $enrollment->id
                                || ($student->active_enrollment_id === null
                                    && $student->is_course_locked
                                    && !EnrollmentRequest::where('learner_id', $student->id)
                                        ->whereKeyNot($enrollment->id)
                                        ->whereIn('status', ['pending', 'approved'])->exists())
                            );

                            if (!$dryRun) {
                                $enrollment->update([
                                    'status' => 'completed',
                                    'completed_at' => $enrollment->completed_at ?? $enrollment->theoretical_passed_at ?? now(),
                                ]);
                                if ($unlock) {
                                    $student->unlockFromCourse();
                                }
                            }

                            return [$enrollment->id, (bool) $unlock];
                        });

                        if ($result !== null) {
                            $completed++;
                            $unlocked += (int) $result[1];
                            $this->line(($dryRun ? 'Would complete' : 'Completed')." enrollment {$result[0]}; "
                                .($result[1] ? 'release course lock.' : 'preserve current lock.'));
                        }
                    } catch (\Throwable $error) {
                        $failed++;
                        $this->error("Enrollment {$candidate->id}: {$error->getMessage()}");
                    }
                }
            });

        $this->info(($dryRun ? 'Preview' : 'Repair').": {$completed} enrollments, {$unlocked} locks, {$failed} failures.");
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function candidates()
    {
        return EnrollmentRequest::where('status', 'approved')
            ->where('theoretical_passed', true)
            ->whereHas('course', fn ($query) => $query->where('course_type', 'theoretical'));
    }
}
