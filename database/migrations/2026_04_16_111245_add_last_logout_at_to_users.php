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
        Schema::table('admins', function (Blueprint $table) {
            $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('last_logout_at');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn('last_logout_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('last_logout_at');
        });
    }
};
