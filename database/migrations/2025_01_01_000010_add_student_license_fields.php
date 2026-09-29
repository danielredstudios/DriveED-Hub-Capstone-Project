<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds student driver's license upload & verification fields.
     * Students upload their license document; admins verify or reject it.
     * Practical course enrollment is blocked until license is verified.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_license_path')->nullable()->after('profile_picture');
            $table->enum('student_license_status', ['none', 'pending', 'verified', 'rejected'])
                  ->default('none')->after('student_license_path');
            $table->timestamp('student_license_verified_at')->nullable()->after('student_license_status');
            $table->unsignedBigInteger('student_license_verified_by')->nullable()->after('student_license_verified_at');
            $table->text('student_license_rejection_reason')->nullable()->after('student_license_verified_by');

            $table->foreign('student_license_verified_by')
                  ->references('id')->on('admins')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['student_license_verified_by']);
            $table->dropColumn([
                'student_license_path',
                'student_license_status',
                'student_license_verified_at',
                'student_license_verified_by',
                'student_license_rejection_reason',
            ]);
        });
    }
};
