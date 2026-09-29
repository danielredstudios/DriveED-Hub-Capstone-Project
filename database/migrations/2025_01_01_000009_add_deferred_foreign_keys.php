<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add foreign key constraints that reference tables created in later migrations.
     */
    public function up(): void
    {
        // Add FK for students.active_enrollment_id -> enrollments.id
        Schema::table('students', function (Blueprint $table) {
            $table->foreign('active_enrollment_id')
                  ->references('id')
                  ->on('enrollments')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['active_enrollment_id']);
        });
    }
};
