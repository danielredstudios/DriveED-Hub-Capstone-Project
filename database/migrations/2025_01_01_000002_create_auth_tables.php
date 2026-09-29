<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        // Admins table (school admins + system admins)
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('contact', 20)->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('role')->default('school_admin');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
        });

        // Students table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->string('password');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('contact')->nullable();
            $table->string('address')->nullable();
            $table->string('branch')->nullable();
            $table->string('location')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('status')->default('active');
            $table->enum('experience_level', ['new_driver', 'experienced'])->default('new_driver');
            $table->boolean('has_passed_theoretical')->default(false);
            $table->timestamp('theoretical_passed_at')->nullable();
            $table->unsignedBigInteger('active_enrollment_id')->nullable();
            $table->boolean('is_course_locked')->default(false);
            $table->enum('role', ['guest', 'student'])->default('guest');
            $table->date('enrollment_date')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['school_id', 'email']);
            $table->index('status');
            $table->index('role');
            $table->index('created_at');
        });

        // Instructors table
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('address')->nullable();
            $table->string('contact')->nullable();
            $table->string('license_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('status')->default('active');
            $table->json('course_specializations')->nullable();
            $table->enum('availability', ['available', 'unavailable'])->default('available');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['school_id', 'email']);
            $table->index('status');
            $table->index('availability');
            $table->index('created_at');
            $table->index(['school_id', 'status', 'availability'], 'instructors_status_availability_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructors');
        Schema::dropIfExists('students');
        Schema::dropIfExists('admins');
    }
};
