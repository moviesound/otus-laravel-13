<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'is_setted' => 1,
            'status' => 1,
            'name' => $this->faker->name(),
            'email' => null,
            'phone' => $this->faker->phoneNumber(),
            'phone_proved' => 1,
            'language' => 'en',
            'politics_agreed' => 1,
        ];
    }
}
