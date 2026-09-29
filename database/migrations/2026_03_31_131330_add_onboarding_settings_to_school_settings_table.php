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
            $table->integer('invitation_expiry_days')->default(7)->after('alert_threshold_pending');
            $table->boolean('require_instructor_license')->default(true)->after('invitation_expiry_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['invitation_expiry_days', 'require_instructor_license']);
        });
    }
};
