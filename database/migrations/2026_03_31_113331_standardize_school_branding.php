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
        // 1. Update all existing school settings
        DB::table('school_settings')->update([
            'primary_color' => '#2563eb', // Blue 600
            'secondary_color' => '#60a5fa', // Blue 400
            'use_gradient_header' => false,
            'button_style' => 'solid',
            'login_header_bg_type' => 'color',
            'login_header_bg_color' => '#2563eb',
            'login_page_bg_color' => '#f8fafc',
            'button_primary_bg' => '#2563eb',
            'modal_header_bg' => '#2563eb',
            'modal_border_color' => '#2563eb',
            'page_header_border' => '#2563eb',
            'calendar_today_color' => '#2563eb',
            'calendar_day_hover' => '#2563eb',
        ]);

        // 2. Change defaults for future creations
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->default('#2563eb')->change();
            $table->string('secondary_color', 7)->default('#60a5fa')->change();
            $table->boolean('use_gradient_header')->default(false)->change();
            $table->string('button_style')->default('solid')->change();
            $table->string('login_header_bg_type')->default('color')->change();
            $table->string('login_header_bg_color')->default('#2563eb')->change();
            $table->string('login_page_bg_color')->default('#f8fafc')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't revert data changes as it's a one-way standardization
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->default('#2563eb')->change();
            $table->string('secondary_color', 7)->default('#fbbf24')->change();
            $table->boolean('use_gradient_header')->default(true)->change();
            $table->string('button_style')->default('solid')->change();
            $table->string('login_header_bg_type')->default('gradient')->change();
        });
    }
};
