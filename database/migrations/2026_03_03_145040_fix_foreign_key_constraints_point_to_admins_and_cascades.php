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
        // Fix enrollments table
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['theoretical_passed_by']);
            $table->foreign('theoretical_passed_by')
                ->references('id')->on('admins')
                ->nullOnDelete();
        });

        // Fix session_completions table
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['logged_by']);
            $table->foreign('logged_by')
                ->references('id')->on('admins')
                ->nullOnDelete();

            // Safer cascade
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')->on('enrollment_requests')
                ->restrictOnDelete();
        });

        // Fix bookings table - prevent data loss on student delete
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->restrictOnDelete();
        });

        // Fix payments table - prevent record loss on booking delete
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->foreign('booking_id')
                ->references('id')->on('bookings')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->foreign('booking_id')
                ->references('id')->on('bookings')
                ->cascadeOnDelete();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                ->references('id')->on('students')
                ->cascadeOnDelete();
        });

        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
            $table->foreign('enrollment_id')
                ->references('id')->on('enrollment_requests')
                ->cascadeOnDelete();

            $table->dropForeign(['logged_by']);
            $table->foreign('logged_by')
                ->references('id')->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['theoretical_passed_by']);
            $table->foreign('theoretical_passed_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }
};
