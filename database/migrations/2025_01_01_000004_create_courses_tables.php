<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('title');
            $table->enum('course_type', ['theoretical', 'practical'])->default('theoretical');
            $table->foreignId('prerequisite_course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->enum('license_type', ['non_professional', 'professional'])->default('non_professional');
            $table->decimal('hours_required', 5, 2)->default(8.00);
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('features')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('duration_hours', 5, 1)->default(0);
            $table->integer('max_students')->nullable();
            $table->string('type')->default('standard');
            $table->string('vehicle_type')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
            $table->index('status');
            $table->index('title');
            $table->index('type');
        });

        Schema::create('course_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('name');
            $table->string('transmission_type');
            $table->string('vehicle_type')->nullable();
            $table->decimal('price', 10, 2);
            $table->text('features')->nullable();
            $table->text('description')->nullable();
            $table->integer('training_hours')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('course_id');
        });

        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('module_type', ['theoretical', 'practical_prep', 'reference'])->default('theoretical');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
            $table->index(['course_id', 'sort_order']);
        });

        Schema::create('module_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->json('attachments')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('school_id');
            $table->index(['module_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_lessons');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('course_packages');
        Schema::dropIfExists('courses');
    }
};
