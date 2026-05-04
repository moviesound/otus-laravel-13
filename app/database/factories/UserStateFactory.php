<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserState>
 */
class UserStateFactory extends Factory
{
    protected $model = UserState::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'balance' => $this->faker->randomFloat(4, 0, 10000),
            'currency' => 'RUB',

            'next_morning_digest' => now()->addDay()->setTime(9, 0, 0),
            'next_evening_digest' => now()->addDay()->setTime(20, 0, 0),
        ];
    }
}
