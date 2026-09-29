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
        Schema::table('instructors', function (Blueprint $table) {
            $table->enum('license_status', ['none', 'pending', 'verified', 'rejected'])->default('none')->after('license_number');
            $table->json('restriction_codes')->nullable()->after('license_status');
            $table->text('license_rejection_reason')->nullable()->after('restriction_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn(['license_status', 'restriction_codes', 'license_rejection_reason']);
        });
    }
};
