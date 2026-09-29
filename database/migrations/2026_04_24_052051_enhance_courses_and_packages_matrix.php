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
        Schema::table('courses', function (Blueprint $table) {
            $table->integer('lto_base_hours')->default(0)->after('hours_required');
        });

        Schema::table('course_packages', function (Blueprint $table) {
            $table->foreignId('vehicle_category_id')->nullable()->after('course_id')->constrained('vehicle_categories')->onDelete('set null');
            $table->string('package_level')->nullable()->after('name'); // Smart Basic, Start, All-in
            $table->string('tier')->nullable()->after('package_level'); // Special, Rush, Executive
        });
    }

    public function down(): void
    {
        Schema::table('course_packages', function (Blueprint $table) {
            $table->dropForeign(['vehicle_category_id']);
            $table->dropColumn(['vehicle_category_id', 'package_level', 'tier']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('lto_base_hours');
        });
    }
};
