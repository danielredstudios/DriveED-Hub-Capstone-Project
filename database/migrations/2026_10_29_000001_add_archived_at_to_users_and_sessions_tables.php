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
        if (Schema::hasTable('session_completions') && !Schema::hasColumn('session_completions', 'archived_at')) {
            Schema::table('session_completions', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('status');
                $table->index(['instructor_id', 'archived_at']);
            });
        }

        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'archived_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('status');
                $table->index(['status', 'archived_at']);
            });
        }

        if (Schema::hasTable('instructors') && !Schema::hasColumn('instructors', 'archived_at')) {
            Schema::table('instructors', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('status');
                $table->index(['status', 'archived_at']);
            });
        }

        if (Schema::hasTable('admins') && !Schema::hasColumn('admins', 'archived_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('session_completions') && Schema::hasColumn('session_completions', 'archived_at')) {
            Schema::table('session_completions', function (Blueprint $table) {
                $table->dropIndex(['instructor_id', 'archived_at']);
                $table->dropColumn('archived_at');
            });
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'archived_at')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex(['status', 'archived_at']);
                $table->dropColumn('archived_at');
            });
        }

        if (Schema::hasTable('instructors') && Schema::hasColumn('instructors', 'archived_at')) {
            Schema::table('instructors', function (Blueprint $table) {
                $table->dropIndex(['status', 'archived_at']);
                $table->dropColumn('archived_at');
            });
        }

        if (Schema::hasTable('admins') && Schema::hasColumn('admins', 'archived_at')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
