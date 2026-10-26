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
        Schema::table('enrollment_requests', function (Blueprint $table) {
            // Drop unconditional unique constraint so completed courses do not block re-enrollment.
            // Active duplicate enrollments are guarded at the application layer with status checks and row locks.
            $table->dropUnique('idx_unique_student_course_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->unique(['learner_id', 'course_id'], 'idx_unique_student_course_enrollment');
        });
    }
};
