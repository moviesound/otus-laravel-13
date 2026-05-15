<?php

namespace Database\Factories\Bot;

use App\Models\Bot\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tariff>
 */
class TariffFactory extends Factory
{
    protected $model = Tariff::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),

            'period_days' => $this->faker->numberBetween(7, 365),

            'date_start' => now(),
            'date_stop' => now()->addMonth(),

            'status' => 1,

            'cost' => $this->faker->randomFloat(4, 10, 1000),
            'currency' => 'RUB',

            'calls_tokens' => 1000,
            'events_per_day' => 10,
            'events_per_month' => 300,
            'notes_per_month' => 200,

            'total_notes' => 1000,
            'total_lists' => 100,
            'items_in_list' => 50,

            'ai_tokens' => 5000,
            'files_volume' => 1024,

            'shared_space' => false,
            'default' => false,
            'autoprolong' => false,
            'prolongation' => false,

            'action_placeholder' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 1]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 0]);
    }

    public function default(): static
    {
        return $this->state(fn () => ['default' => 1]);
    }

    public function autoprolong(): static
    {
        return $this->state(fn () => ['autoprolong' => 1]);
    }

    public function prolongation(): static
    {
        return $this->state(fn () => ['prolongation' => 1]);
    }
}
