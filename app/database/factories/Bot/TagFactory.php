<?php

namespace Database\Factories\Bot;

use App\Models\Bot\Tag;
use App\Models\Bot\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'tag' => $this->faker->unique()->word(),

            // можно 0 (системный тег) или пользовательский
            'user_id' => User::factory(),

            // имитируем реальное использование
            'count' => $this->faker->numberBetween(0, 50),

            'created_at' => now(),
        ];
    }

    //States
    public function forUser(int $userId): static
    {
        return $this->state(fn () => [
            'user_id' => $userId,
        ]);
    }
}
