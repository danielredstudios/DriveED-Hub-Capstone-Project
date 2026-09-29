<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'slug']);
        });

        // Add branch_id to students
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Add branch_id to instructors
        Schema::table('instructors', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Add branch_id to time_slots
        Schema::table('time_slots', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Add branch_id to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Add branch_id to enrollment_requests
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('school_id')->constrained('branches')->nullOnDelete();
        });

        // Add enable_branches toggle to school_settings
        Schema::table('school_settings', function (Blueprint $table) {
            $table->boolean('enable_branches')->default(false)->after('advance_booking_days');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn('enable_branches');
        });

        Schema::dropIfExists('branches');
    }
};
