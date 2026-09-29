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
            $table->string('role_student_bg')->nullable()->after('badge_cancelled_text');
            $table->string('role_student_text')->nullable()->after('role_student_bg');
            $table->string('role_instructor_bg')->nullable()->after('role_student_text');
            $table->string('role_instructor_text')->nullable()->after('role_instructor_bg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['role_student_bg', 'role_student_text', 'role_instructor_bg', 'role_instructor_text']);
        });
    }
};
