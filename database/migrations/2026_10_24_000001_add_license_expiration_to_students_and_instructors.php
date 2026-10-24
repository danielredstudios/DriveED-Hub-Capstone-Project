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
            if (!Schema::hasColumn('students', 'license_number')) {
                $table->string('license_number', 50)->nullable()->after('dl_code');
            }
            if (!Schema::hasColumn('students', 'license_expiration_date')) {
                $table->date('license_expiration_date')->nullable()->after('license_number');
            }
        });

        Schema::table('instructors', function (Blueprint $table) {
            if (!Schema::hasColumn('instructors', 'license_expiration_date')) {
                $table->date('license_expiration_date')->nullable()->after('license_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'license_expiration_date')) {
                $table->dropColumn('license_expiration_date');
            }
            if (Schema::hasColumn('students', 'license_number')) {
                $table->dropColumn('license_number');
            }
        });

        Schema::table('instructors', function (Blueprint $table) {
            if (Schema::hasColumn('instructors', 'license_expiration_date')) {
                $table->dropColumn('license_expiration_date');
            }
        });
    }
};
