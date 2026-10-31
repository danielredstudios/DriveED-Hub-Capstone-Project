<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        $exitCode = Artisan::call('enrollments:repair-passed-tdc');
        if ($exitCode !== 0) {
            throw new RuntimeException('Passed TDC enrollment repair failed: '.Artisan::output());
        }
    }

    public function down(): void
    {
        // Completed training must not become active again when application code is rolled back.
    }
};
