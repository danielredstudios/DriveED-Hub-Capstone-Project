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
        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('status');
        });

        Schema::table('session_completions', function (Blueprint $table) {
            // Drop existing logged_by foreign key pointing to admins or users
            $table->dropForeign(['logged_by']);
            // Create new logged_by pointing to instructors
            $table->foreign('logged_by')
                ->references('id')->on('instructors')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_completions', function (Blueprint $table) {
            $table->dropForeign(['logged_by']);
            $table->foreign('logged_by')
                ->references('id')->on('admins')
                ->nullOnDelete();
        });

        Schema::table('enrollment_requests', function (Blueprint $table) {
            $table->dropColumn('rejected_at');
        });
    }
};
