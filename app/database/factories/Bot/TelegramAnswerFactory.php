<?php

namespace Database\Factories\Bot;

use App\Models\Bot\TelegramAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegramAnswerFactory extends Factory
{
    protected $model = TelegramAnswer::class;

    public function definition(): array
    {
        return [
            'user_id' => rand(1, 100000),

            'chat_id' => (string) rand(100000000, 999999999),

            'created_at' => now(),

            'message' => $this->faker->sentence(),

            'message_id' => (string) rand(100000, 999999),

            'debug' => $this->faker->optional()->text(),

            'type' => $this->faker->randomElement([
                'user',
                'system',
                'callback',
            ]),

            'status' => $this->faker->numberBetween(0, 5),

            'is_temporary' => $this->faker->boolean(),

            'locked_by' => $this->faker->optional()->userName(),

            'locked_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function temporary(): static
    {
        return $this->state(fn () => [
            'is_temporary' => 1,
        ]);
    }

    public function notTemporary(): static
    {
        return $this->state(fn () => [
            'is_temporary' => 0,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn () => [
            'locked_by' => 'worker_1',
            'locked_at' => now(),
        ]);
    }

    public function unlocked(): static
    {
        return $this->state(fn () => [
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    public function userType(): static
    {
        return $this->state(fn () => [
            'type' => 'user',
        ]);
    }

    public function systemType(): static
    {
        return $this->state(fn () => [
            'type' => 'system',
        ]);
    }

    public function callbackType(): static
    {
        return $this->state(fn () => [
            'type' => 'callback',
        ]);
    }
}
