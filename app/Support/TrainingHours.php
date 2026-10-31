<?php

namespace App\Support;

use App\Models\EnrollmentRequest;
use Illuminate\Validation\ValidationException;

class TrainingHours
{
    public static function remaining(EnrollmentRequest $enrollment, string $type, ?int $exceptSession = null): float
    {
        $limit = $type === 'theoretical' ? $enrollment->tdc_hours_limit : $enrollment->pdc_hours_limit;
        $used = $enrollment->sessionCompletions()->where('session_type', $type)
            ->where('status', 'completed')
            ->when($exceptSession, fn ($query) => $query->whereKeyNot($exceptSession))
            ->sum('hours_completed');
        return max(0, round((float) $limit - (float) $used, 2));
    }

    public static function ensureCanLog(EnrollmentRequest $enrollment, string $type, float $hours, ?int $exceptSession = null): void
    {
        $remaining = self::remaining($enrollment, $type, $exceptSession);
        if ($remaining <= 0) {
            throw ValidationException::withMessages(['hours_completed' => 'This student has already completed the required '.strtoupper($type === 'theoretical' ? 'TDC' : 'PDC').' training hours. No additional session can be logged.']);
        }
        if ($hours > $remaining) {
            throw ValidationException::withMessages(['hours_completed' => "Only {$remaining} training hour(s) remain for this phase. Reduce the session duration."]);
        }
    }
}
