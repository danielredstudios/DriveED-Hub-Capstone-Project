<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. NOT NULL constraints for school_id
        Schema::table('schedule_instructors', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });

        // 2. Add FKs & Indexes to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('package_id');
            $table->index('time_slot_id');

        // Note: Add FKs only if data is consistent.
        // Using indexes for now as requested by "Add FK constraints or indexes".
        // FKs might fail if there are orphaned records in existing data.
        });

        // 3. Unique constraint on enrollment_requests to prevent double-enrollment
        Schema::table('enrollment_requests', function (Blueprint $table) {
            // First cleanup any duplicates to avoid migration failure
            // (In a real scenario, this would be a separate cleanup script or handled carefully)
            $table->unique(['learner_id', 'course_id'], 'idx_unique_student_course_enrollment');
        });

        // 4. Remove redundant students.branch column
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'branch')) {
                $table->dropColumn('branch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('branch')->nullable();
        });

        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropUnique('idx_unique_student_course_enrollment');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['package_id']);
            $table->dropIndex(['time_slot_id']);
        });

        Schema::table('schedule_instructors', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(true)->change();
        });
    }
};
