<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'title' => fake()->randomElement([
                'Basic Driving Course',
                'Advanced Driving Course',
                'Defensive Driving',
                'Manual Transmission Course',
                'Automatic Transmission Course',
            ]) . ' ' . fake()->numberBetween(1, 100),
            'description' => fake()->paragraph(),
            'banner_image' => null,
            'features' => json_encode(['Feature 1', 'Feature 2', 'Feature 3']),
            'price' => fake()->randomFloat(2, 3000, 15000),
            'duration_hours' => fake()->randomFloat(1, 8, 40),
            'max_students' => fake()->numberBetween(5, 20),
            'type' => fake()->randomElement(['standard', 'intensive', 'refresher']),
            'vehicle_type' => fake()->randomElement(['manual', 'automatic', 'sedan', 'suv']),
            'status' => 'active',
            'is_featured' => fake()->boolean(30),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'standard',
        ]);
    }

    public function intensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'intensive',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
