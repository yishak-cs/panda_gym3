<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Members>
 */
class MembersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phone_number' => $this->faker->phoneNumber,
            'sex' => $this->faker->randomElement(['Male', 'Female']),
            'goal' => $this->faker->randomElement(['Lose Weight', 'Gain Weight', 'Maintain Weight']),
            'current_weight' => $this->faker->numberBetween(50, 100),
            'target_weight' => $this->faker->numberBetween(50, 100),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($member) {
            $plan = \App\Models\MembershipPlan::inRandomOrder()->first();
            \App\Models\Subscription::unguard();
            if ($plan) {
                $randomDate = \Carbon\Carbon::now()->subDays(rand(0, 90));
                \App\Models\Subscription::create([
                    'member_id' => $member->id,
                    'membership_plan_id' => $plan->id,
                    'startDate' => $randomDate,
                    'created_at' => $randomDate,
                ]);
            }
        });
    }
}
