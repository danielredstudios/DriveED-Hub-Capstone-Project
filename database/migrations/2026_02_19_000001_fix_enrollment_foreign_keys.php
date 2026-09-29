<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix foreign key constraints that reference the legacy 'enrollments' table.
 * 
 * The system has fully transitioned to using 'enrollment_requests' as the
 * primary enrollment entity. This migration updates all FK constraints
 * to point to the correct table.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fix session_completions.enrollment_id FK
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollment_requests')
                ->onDelete('cascade');
        });

        // Fix phase_progression_requests.enrollment_id FK
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollment_requests')
                ->onDelete('cascade');
        });

        // Fix students.active_enrollment_id FK
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['active_enrollment_id']);
            $table->foreign('active_enrollment_id')
                ->references('id')
                ->on('enrollment_requests')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Revert session_completions FK back to enrollments
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });

        // Revert phase_progression_requests FK back to enrollments
        Schema::table('phase_progression_requests', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('cascade');
        });

        // Revert students.active_enrollment_id FK back to enrollments
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['active_enrollment_id']);
            $table->foreign('active_enrollment_id')
                ->references('id')
                ->on('enrollments')
                ->onDelete('set null');
        });
    }
};
