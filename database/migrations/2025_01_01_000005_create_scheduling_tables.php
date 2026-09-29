<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->integer('max_instructors')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('status');
            $table->index(['school_id', 'date', 'status'], 'time_slots_school_date_status_index');
        });

        Schema::create('schedule_instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $table->enum('assignment_type', ['admin_assigned', 'self_selected'])->default('admin_assigned');
            $table->boolean('has_pending_removal_request')->default(false);
            $table->timestamps();

            $table->unique(['time_slot_id', 'instructor_id'], 'schedule_instructors_unique_assignment');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->onDelete('set null');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('time_slot_id')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->string('status')->default('scheduled');

            // Cancellation Details
            $table->string('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Attendance & Session Details
            $table->string('attendance_status')->nullable();
            $table->string('session_status')->nullable();
            $table->timestamp('attendance_marked_at')->nullable();
            $table->decimal('session_grade', 5, 2)->nullable();
            $table->json('skills_practiced')->nullable();

            // Payment
            $table->string('payment_status')->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);

            // Feedback & Notes
            $table->text('notes')->nullable();
            $table->text('instructor_feedback')->nullable();
            $table->text('student_feedback')->nullable();

            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('instructor_id');
            $table->index('course_id');
            $table->index('status');
            $table->index('scheduled_at');
            $table->index(['student_id', 'status'], 'bookings_student_status_index');
            $table->index(['instructor_id', 'status'], 'bookings_instructor_status_index');
            $table->index(['scheduled_at', 'status'], 'bookings_scheduled_status_index');
            $table->index('payment_status', 'bookings_payment_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('schedule_instructors');
        Schema::dropIfExists('time_slots');
    }
};
