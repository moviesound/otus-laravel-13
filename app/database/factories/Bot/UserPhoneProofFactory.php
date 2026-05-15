<?php

namespace Database\Factories\Bot;

use App\Models\Bot\User;
use App\Models\Bot\UserPhoneProof;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPhoneProofFactory extends Factory
{
    protected $model = UserPhoneProof::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'phone' => $this->faker->numerify('+79#########'),

            'code' => (string) $this->faker->numberBetween(1000, 9999),

            'status' => $this->faker->randomElement([
                'sent',
                'success',
                'failed',
                'wrong-code',
                'success-code',
            ]),

            'times' => $this->faker->numberBetween(1, 5),

            'call_id' => $this->faker->optional()->uuid(),

            'campaign_id' => $this->faker->optional()->numberBetween(1, 999999),

            'cost' => $this->faker->optional()->randomFloat(6, 0, 100),

            'sender' => $this->faker->optional()->company(),

            'call_status' => $this->faker->optional()->randomElement([
                'new',
                'ringing',
                'answered',
                'busy',
                'failed',
            ]),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
