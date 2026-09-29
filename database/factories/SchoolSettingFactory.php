<?php

namespace Database\Factories;

use App\Models\SchoolSetting;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolSettingFactory extends Factory
{
    protected $model = SchoolSetting::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'primary_color' => '#667eea',
            'secondary_color' => '#764ba2',
            'accent_color' => '#4f46e5',
            'background_type' => 'color',
            'background_color' => '#f3f4f6',
            'sidebar_bg_color' => '#1f2937',
            'sidebar_text_color' => '#ffffff',
            'instructor_selection_mode' => 'student_chooses',
            'enable_booking_queue' => true,
            'booking_queue_days' => 3,
            'advance_booking_days' => 0,
        ];
    }
}
