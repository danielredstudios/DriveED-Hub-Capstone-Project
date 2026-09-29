<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $name = fake()->company() . ' Driving School';
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'timezone' => 'Asia/Manila',
            'branding' => [
                'logo' => null,
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
            ],
            'settings' => [],
            'instructor_removal_notice_days' => 7,
        ];
    }
}
