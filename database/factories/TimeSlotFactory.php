<?php

namespace Database\Factories;

use App\Models\TimeSlot;
use App\Models\School;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        
        return [
            'school_id' => School::factory(),
            'course_id' => Course::factory(),
            'session_type' => fake()->randomElement(['theoretical', 'practical']),
            'date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + 1),
            'status' => 'open',
            'max_instructors' => fake()->randomElement([1, 2, 3]),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
