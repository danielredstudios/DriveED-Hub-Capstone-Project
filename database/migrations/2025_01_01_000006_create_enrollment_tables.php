<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('learner_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->enum('requested_license_type', ['non_professional', 'professional'])->default('non_professional');
            $table->string('requested_dl_code')->nullable();
            $table->enum('experience_level', ['new_driver', 'experienced'])->default('new_driver');
            $table->string('credentials_file_path')->nullable();
            $table->text('verification_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->enum('payment_status', ['pending', 'on_hold', 'paid'])->default('pending');
            $table->text('remarks')->nullable();
            $table->string('branch')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('theoretical_passed')->default(false);
            $table->timestamp('theoretical_passed_at')->nullable();
            $table->foreignId('theoretical_passed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('theoretical_pass_notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('learner_id');
            $table->index(['school_id', 'status'], 'enrollment_requests_school_status_index');
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('enrollment_request_id')->nullable()->constrained('enrollment_requests')->onDelete('set null');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->decimal('hours_completed', 5, 2)->default(0);
            $table->timestamp('enrolled_at');
            $table->timestamp('completed_at')->nullable();
            $table->boolean('theoretical_passed')->default(false);
            $table->timestamp('theoretical_passed_at')->nullable();
            $table->foreignId('theoretical_passed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->text('theoretical_pass_notes')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['student_id', 'status']);
            $table->index(['course_id', 'status']);
        });

        Schema::create('session_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('instructors')->onDelete('cascade');
            $table->enum('session_type', ['theoretical', 'practical']);
            $table->decimal('hours_completed', 5, 2);
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->foreignId('logged_by')->nullable()->constrained('instructors')->onDelete('cascade');
            $table->timestamps();

            $table->index('school_id');
            $table->index(['enrollment_id', 'session_date']);
            $table->index(['instructor_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_completions');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('enrollment_requests');
    }
};
