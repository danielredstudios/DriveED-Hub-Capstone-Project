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
        // Add unique constraint for branches (school_id + name)
        Schema::table('branches', function (Blueprint $table) {
            $table->unique(['school_id', 'name'], 'branches_school_id_name_unique');
        });

        // Add unique constraint for course packages (course_id + name)
        Schema::table('course_packages', function (Blueprint $table) {
            $table->unique(['course_id', 'name'], 'course_packages_course_id_name_unique');
        });

        // Add unique constraint for courses (school_id + title)
        Schema::table('courses', function (Blueprint $table) {
            $table->unique(['school_id', 'title'], 'courses_school_id_title_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique('branches_school_id_name_unique');
        });

        Schema::table('course_packages', function (Blueprint $table) {
            $table->dropUnique('course_packages_course_id_name_unique');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique('courses_school_id_title_unique');
        });
    }
};
