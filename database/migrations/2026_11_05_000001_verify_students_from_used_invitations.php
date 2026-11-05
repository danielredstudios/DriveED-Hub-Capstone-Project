<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activationPrefix = 'User activated account via invitation: ';

        DB::table('system_logs')
            ->where('category', 'authentication')
            ->where('message', 'like', $activationPrefix.'%')
            ->where('context->role', 'student')
            ->select('id', 'school_id', 'context', 'created_at')
            ->chunkById(500, function ($activations) {
                foreach ($activations as $activation) {
                    $studentId = json_decode($activation->context, true)['user_id'] ?? null;
                    if (!$studentId || !$activation->school_id) {
                        continue;
                    }

                    DB::table('students')
                        ->where('id', $studentId)
                        ->where('school_id', $activation->school_id)
                        ->where('role', 'student')
                        ->whereNull('email_verified_at')
                        ->whereExists(function ($invitation) use ($activation) {
                            $invitation->selectRaw('1')
                                ->from('invitations')
                                ->whereColumn('invitations.school_id', 'students.school_id')
                                ->whereColumn('invitations.email', 'students.email')
                                ->where('invitations.role', 'student')
                                ->whereColumn('invitations.used_at', '>=', 'students.created_at')
                                ->where('invitations.used_at', '<=', $activation->created_at);
                        })
                        ->update([
                            'email_verified_at' => $activation->created_at,
                            'verification_code' => null,
                            'verification_code_expires_at' => null,
                            'verification_attempts' => 0,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Keep email verification established by completed invitations.
    }
};
