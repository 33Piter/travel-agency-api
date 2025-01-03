<?php

namespace Database\Factories;

use App\Enums\TravelOrderStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelOrder>
 */
class TravelOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('now', '+1 year');
        $returnDate = $this->faker->dateTimeBetween($departureDate, '+1 year');

        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'applicant_name' => $this->faker->name,
            'applicant_email' => $this->faker->email,
            'destination' => $this->faker->country,
            'departure_date' => $departureDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'status' => fake()->randomElement(TravelOrderStatusEnum::cases()),
        ];
    }
}
