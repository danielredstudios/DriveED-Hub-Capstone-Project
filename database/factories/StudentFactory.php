<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->userName() . '@gmail.com',
            'password' => Hash::make('password'),
            'contact' => '09' . fake()->numerify('#########'),
            'address' => fake()->address(),
            'location' => fake()->optional()->city(),
            'status' => 'active',
            'role' => 'student',
            'enrollment_date' => fake()->optional()->date(),
            'profile_picture' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'guest',
        ]);
    }

    public function enrolled(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'student',
            'enrollment_date' => now(),
        ]);
    }
}
