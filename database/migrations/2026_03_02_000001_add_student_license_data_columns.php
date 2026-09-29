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
        Schema::table('students', function (Blueprint $table) {
            $table->longText('student_license_data')->nullable()->after('student_license_path');
            $table->string('student_license_mime_type', 100)->nullable()->after('student_license_data');
            $table->string('student_license_filename')->nullable()->after('student_license_mime_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'student_license_data',
                'student_license_mime_type',
                'student_license_filename',
            ]);
        });
    }
};
