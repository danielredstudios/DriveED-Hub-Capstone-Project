<?php

namespace Database\Factories;

use App\Models\EnrollmentRequest;
use App\Models\School;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentRequestFactory extends Factory
{
    protected $model = EnrollmentRequest::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'learner_id' => Student::factory(),
            'course_id' => Course::factory(),
            'status' => 'pending',
            'payment_status' => 'pending',
            'remarks' => fake()->optional()->sentence(),
            'branch' => fake()->optional()->city(),
            'location' => fake()->optional()->city(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }
}
