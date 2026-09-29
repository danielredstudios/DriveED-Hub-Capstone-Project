<?php

namespace Database\Factories;

use App\Models\CoursePackage;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoursePackage>
 */
class CoursePackageFactory extends Factory
{
    protected $model = CoursePackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => $this->faker->randomElement(['Basic Package', 'Standard Package', 'Premium Package', 'Intensive Package']),
            'transmission_type' => $this->faker->randomElement(['manual', 'automatic', 'both']),
            'vehicle_type' => $this->faker->randomElement(['sedan', 'suv', 'motorcycle']),
            'price' => $this->faker->randomFloat(2, 3000, 15000),
            'features' => [
                $this->faker->sentence(3),
                $this->faker->sentence(3),
                $this->faker->sentence(3),
            ],
            'description' => $this->faker->paragraph(),
            'training_hours' => $this->faker->numberBetween(10, 40),
            'is_popular' => $this->faker->boolean(20),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
