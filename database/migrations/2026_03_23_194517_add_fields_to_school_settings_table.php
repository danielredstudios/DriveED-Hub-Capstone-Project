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
            $table->integer('booking_cutoff_hours')->default(0)->after('school_id');
            $table->integer('alert_threshold_pending')->default(999)->after('booking_cutoff_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['booking_cutoff_hours', 'alert_threshold_pending']);
        });
    }
};
