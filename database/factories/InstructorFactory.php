<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class InstructorFactory extends Factory
{
    protected $model = Instructor::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->userName() . '@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '09' . fake()->numerify('#########'),
            'license_number' => strtoupper(fake()->bothify('N##-##-######')),
            'bio' => fake()->optional()->paragraph(),
            'status' => 'active',
            'availability' => 'available',
            'course_specializations' => [],
            'profile_picture' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability' => 'unavailable',
        ]);
    }
}
