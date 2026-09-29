<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained('schools')->onDelete('cascade');

            // Brand Color Settings
            $table->string('primary_color', 7)->default('#2563eb');
            $table->string('secondary_color', 7)->default('#fbbf24');
            $table->string('accent_color', 7)->default('#1e40af');

            // Background Settings
            $table->string('background_type')->default('color');
            $table->string('background_color')->default('#f5f5f5');
            $table->string('background_image')->nullable();
            $table->integer('background_opacity')->default(100);

            // Sidebar Settings
            $table->string('sidebar_bg_color', 7)->default('#ffffff');
            $table->string('sidebar_text_color', 7)->default('#333333');
            $table->string('sidebar_hover_color', 7)->default('#f5f5f5');

            // Header Settings
            $table->boolean('use_gradient_header')->default(true);
            $table->string('header_text_color', 7)->default('#ffffff');

            // Login/Signup Header Customization
            $table->string('login_header_layout')->default('horizontal');
            $table->string('login_logo_image')->nullable();
            $table->string('login_logo_position')->default('left');
            $table->integer('login_logo_size')->default(40);
            $table->string('login_school_name_text')->nullable();
            $table->boolean('login_show_school_name')->default(true);
            $table->string('login_school_name_position')->default('left');
            $table->integer('login_school_name_size')->default(24);
            $table->string('login_welcome_text')->default('Welcome!');
            $table->boolean('login_show_welcome_text')->default(true);
            $table->string('login_welcome_position')->default('right');
            $table->integer('login_welcome_size')->default(16);
            $table->string('login_header_bg_type')->default('gradient');
            $table->string('login_header_bg_color')->nullable();
            $table->string('login_header_bg_image')->nullable();
            $table->integer('login_header_height')->default(60);
            $table->string('login_header_text_color')->default('#ffffff');
            $table->boolean('login_header_shadow')->default(true);
            $table->string('register_welcome_text')->default('Student Registration');
            $table->string('register_subtitle_text')->nullable();

            // Login Page Background Settings
            $table->string('login_page_bg_type')->default('color');
            $table->string('login_page_bg_color')->default('#f5f5f5');
            $table->string('login_page_bg_image')->nullable();
            $table->integer('login_page_bg_opacity')->default(100);

            // Calendar Customization
            $table->string('calendar_day_border', 7)->default('#dee2e6');
            $table->string('calendar_day_hover', 7)->default('#667eea');
            $table->string('calendar_today_color', 7)->default('#667eea');

            // Button Customization
            $table->string('button_primary_bg', 7)->default('#667eea');
            $table->string('button_primary_text', 7)->default('#ffffff');
            $table->string('button_secondary_bg', 7)->default('#6c757d');
            $table->string('button_secondary_text', 7)->default('#ffffff');
            $table->string('button_success_bg', 7)->default('#28a745');
            $table->string('button_success_text', 7)->default('#ffffff');
            $table->string('button_danger_bg', 7)->default('#dc3545');
            $table->string('button_danger_text', 7)->default('#ffffff');

            // Border & Shape Customization
            $table->integer('border_radius')->default(8);
            $table->integer('button_border_radius')->default(8);
            $table->string('button_style')->default('solid');

            // Modal Customization
            $table->string('modal_header_bg', 7)->default('#667eea');
            $table->string('modal_header_text', 7)->default('#ffffff');
            $table->string('modal_border_color', 7)->default('#667eea');

            // Card/Panel Customization
            $table->string('card_border_color', 7)->default('#e5e7eb');
            $table->string('card_header_bg', 7)->default('#f9fafb');
            $table->string('page_header_border', 7)->default('#667eea');

            // Badge Customization
            $table->string('badge_pending_bg', 7)->default('#fbbf24');
            $table->string('badge_pending_text', 7)->default('#78350f');
            $table->string('badge_approved_bg', 7)->default('#10b981');
            $table->string('badge_approved_text', 7)->default('#065f46');
            $table->string('badge_cancelled_bg', 7)->default('#ef4444');
            $table->string('badge_cancelled_text', 7)->default('#7f1d1d');

            // Booking & Scheduling Settings
            $table->string('instructor_selection_mode')->default('auto_assign');
            $table->boolean('enable_booking_queue')->default(true);
            $table->integer('booking_queue_days')->default(3);
            $table->integer('advance_booking_days')->default(0);

            // Additional Settings
            $table->json('custom_css')->nullable();
            $table->json('additional_settings')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
