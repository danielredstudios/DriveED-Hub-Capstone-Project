<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->dateTime('paid_on')->nullable();
            $table->string('method')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index('method');
        });

        Schema::create('progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->decimal('completion_percent', 5, 2)->default(0);
            $table->dateTime('last_updated')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('student_id');
            $table->index('course_id');
            $table->index('completion_percent');
            $table->unique(['student_id', 'course_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('generated_by')->constrained('admins')->onDelete('cascade');
            $table->string('report_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('data')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'report_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('progresses');
        Schema::dropIfExists('payments');
    }
};
