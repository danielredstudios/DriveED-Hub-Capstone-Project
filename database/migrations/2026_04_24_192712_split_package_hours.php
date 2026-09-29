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
        Schema::table('course_packages', function (Blueprint $table) {
            $table->decimal('tdc_hours', 8, 2)->default(0)->after('training_hours');
            $table->decimal('pdc_hours', 8, 2)->default(0)->after('tdc_hours');
        });

        // Optional: Move data from training_hours to pdc_hours for practical/combo courses
        // This is a heuristic, better to let admin verify later
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_packages', function (Blueprint $table) {
            $table->dropColumn(['tdc_hours', 'pdc_hours']);
        });
    }
};
