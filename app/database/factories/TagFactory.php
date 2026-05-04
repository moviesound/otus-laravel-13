<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tag;

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
