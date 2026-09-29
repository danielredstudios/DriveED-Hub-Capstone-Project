<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            // File & Contact Settings (From previous turn)
            $table->integer('max_file_size_mb')->default(5)->after('additional_settings');
            $table->boolean('enforce_ph_contact')->default(true)->after('max_file_size_mb');

            // Scheduling & Privacy Settings (New)
            $table->integer('min_tdc_duration_minutes')->default(60)->after('enforce_ph_contact');
            $table->integer('min_pdc_duration_minutes')->default(60)->after('min_tdc_duration_minutes');
            $table->boolean('enable_pii_masking')->default(false)->after('min_pdc_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'max_file_size_mb',
                'enforce_ph_contact',
                'min_tdc_duration_minutes',
                'min_pdc_duration_minutes',
                'enable_pii_masking'
            ]);
        });
    }
};
