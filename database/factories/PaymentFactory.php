<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\School;
use App\Models\Booking;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'amount' => fake()->randomFloat(2, 500, 10000),
            'method' => fake()->randomElement(['cash', 'gcash', 'bank_transfer', 'card']),
            'reference' => strtoupper(fake()->bothify('PAY-####-????')),
            'status' => 'completed',
            'paid_on' => fake()->dateTimeBetween('-30 days', 'now'),
            // Identity and Linkage will be handled in configure() if not provided
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function (Payment $payment) {
            // Ensure Linkage XOR is satisfied
            if (!$payment->booking_id && !$payment->enrollment_request_id) {
                $booking = Booking::factory()->create(['school_id' => $payment->school_id]);
                $payment->booking_id = $booking->id;
            }

            // Ensure Identity XOR is satisfied
            if (!$payment->payer_user_id && !$payment->guest_identity_token) {
                // If we have a booking, use that student. Otherwise create new student.
                if ($payment->booking_id) {
                    $studentId = Booking::find($payment->booking_id)->student_id;
                    $payment->payer_user_id = $studentId;
                } else {
                    $student = Student::factory()->create(['school_id' => $payment->school_id]);
                    $payment->payer_user_id = $student->id;
                }
            }
        });
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'cash',
        ]);
    }

    public function gcash(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'gcash',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
