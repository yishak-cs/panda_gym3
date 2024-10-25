<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MembershipPlan>
 */
class MembershipPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->firstName,
            'duration' => $this->faker->randomElement([30, 60, 90]),
            'price' => $this->faker->randomElement([1000, 800, 400, 200]),
            'description' => $this->faker->randomElement(['Lose Weight', 'Gain Weight', 'Maintain Weight']),
        ];
    }
}
