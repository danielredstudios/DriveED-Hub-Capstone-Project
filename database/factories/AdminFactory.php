<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->userName() . '@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'school_admin',
            'is_active' => true,
            'contact' => '09' . fake()->numerify('#########'),
            'profile_picture' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'school_id' => null,
        ]);
    }
}
