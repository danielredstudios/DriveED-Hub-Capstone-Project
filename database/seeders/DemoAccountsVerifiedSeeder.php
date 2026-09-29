<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DemoAccountsVerifiedSeeder extends Seeder
{
    public function run(): void
    {
        $demoSchoolSlugs = ['lyspeed-driving', 'drived-hub'];

        $schoolIds = School::whereIn('slug', $demoSchoolSlugs)->pluck('id');

        if ($schoolIds->isEmpty()) {
            $this->command->warn('No demo schools found. Skipping demo account verification backfill.');
            return;
        }

        $updated = Student::whereIn('school_id', $schoolIds)
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => now(),
                'verification_code' => null,
                'verification_code_expires_at' => null,
                'verification_attempts' => 0,
                'last_verification_attempt_at' => null,
            ]);

        $this->command->info("   ✓ Demo account verification backfill complete ({$updated} account(s) updated)");
    }
}