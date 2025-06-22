<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name,
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(), 
            'phone_number' => $this->faker->phoneNumber,
            'service_type' => Arr::random(['Consultation', 'Follow-up', 'Emergency', 'Routine']),
            'appointment_date' => $this->faker->dateTimeBetween('+1 days', '+1 month')->format('Y-m-d'),
            'appointment_slot' => Arr::random(['09:00 AM', '10:30 AM', '01:00 PM', '03:00 PM']),
        ];
    }
}
