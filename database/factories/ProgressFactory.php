<?php

namespace Database\Factories;

use App\Models\Progress;
use App\Models\School;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Progress>
 */
class ProgressFactory extends Factory
{
    protected $model = Progress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'notes' => $this->faker->paragraph(),
            'completion_percent' => $this->faker->randomFloat(2, 0, 100),
            'last_updated' => now(),
        ];
    }

    /**
     * Indicate the progress is complete.
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_percent' => 100.00,
        ]);
    }

    /**
     * Indicate the progress is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_percent' => $this->faker->randomFloat(2, 20, 80),
        ]);
    }

    /**
     * Indicate the progress just started.
     */
    public function started(): static
    {
        return $this->state(fn (array $attributes) => [
            'completion_percent' => $this->faker->randomFloat(2, 0, 20),
        ]);
    }
}
