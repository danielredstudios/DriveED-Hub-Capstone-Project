<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('action');
            $table->text('details')->nullable();
            $table->timestamps();
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable();
            $table->enum('level', ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'])->default('error');
            $table->enum('category', [
                'database',
                'validation',
                'authentication',
                'authorization',
                'file_upload',
                'email',
                'payment',
                'api',
                'system',
                'booking',
                'schedule',
                'user_management',
                'course',
                'other'
            ])->default('other');
            $table->string('action')->nullable();
            $table->text('message');
            $table->text('exception_class')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->boolean('notified_admin')->default(false);
            $table->boolean('notified_system_admin')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
            $table->index(['level', 'created_at']);
            $table->index(['category', 'created_at']);
            $table->index('user_type');
        });

        Schema::create('instructor_removal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();
            $table->unsignedBigInteger('schedule_instructor_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('schedule_instructor_id')->references('id')->on('schedule_instructors')->nullOnDelete();
            $table->index(['school_id', 'status']);
            $table->index(['instructor_id', 'status']);
        });

        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('contact', 20);
            $table->boolean('is_new_driver')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
        Schema::dropIfExists('instructor_removal_requests');
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('logs');
    }
};
