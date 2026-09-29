<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\School;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'student_id' => Student::factory(),
            'instructor_id' => Instructor::factory(),
            'course_id' => Course::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'booking_date' => fake()->dateTimeBetween('now', '+30 days'),
            'scheduled_at' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => 'scheduled',
            'attendance_status' => null,
            'payment_status' => 'pending',
            'total_amount' => fake()->randomFloat(2, 500, 5000),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
            'booking_date' => fake()->dateTimeBetween('-30 days', 'yesterday'),
            'attendance_status' => 'present',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_status' => 'paid',
        ]);
    }
}
