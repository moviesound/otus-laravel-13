<?php

namespace Database\Factories\Bot;

use App\Models\Bot\TelegramSysMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TelegramSysMessageFactory extends Factory
{
    protected $model = TelegramSysMessage::class;

    public function definition(): array
    {
        return [
            'chat_id' => (string) rand(100000000, 999999999),

            'message_id' => (string) rand(100000, 999999),

            'message' => $this->faker->sentence(),

            'query' => $this->faker->sentence(),

            'is_temporary' => $this->faker->boolean(),

            'created_at' => now(),

            'queue_id' => rand(1, 100000),
        ];
    }

    public function temporary(): static
    {
        return $this->state(fn () => [
            'is_temporary' => true,
        ]);
    }

    public function notTemporary(): static
    {
        return $this->state(fn () => [
            'is_temporary' => false,
        ]);
    }
}
