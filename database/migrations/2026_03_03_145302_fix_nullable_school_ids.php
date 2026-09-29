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
        Schema::table('time_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });

        Schema::table('session_completions', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });

        Schema::table('course_modules', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });

        Schema::table('module_lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('module_lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(true)->change();
        });

        Schema::table('course_modules', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(true)->change();
        });

        Schema::table('session_completions', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(true)->change();
        });

        Schema::table('time_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable(true)->change();
        });
    }
};
