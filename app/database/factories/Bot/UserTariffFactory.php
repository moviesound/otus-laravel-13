<?php

namespace Database\Factories\Bot;

use App\Models\Bot\UserTariff;
use App\Models\Bot\User;
use App\Models\Bot\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserTariff>
 */
class UserTariffFactory extends Factory
{
    protected $model = UserTariff::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tariff_id' => Tariff::factory(),

            'date_start' => now(),
            'date_stop' => now()->addMonth(),

            'cost' => $this->faker->randomFloat(4, 10, 1000),
            'currency' => 'RUB',

            'call_tokens_left' => $this->faker->numberBetween(0, 10000),
            'ai_tokens_left' => $this->faker->numberBetween(0, 10000),

            'ai_tokens_used' => $this->faker->numberBetween(0, 5000),
            'events_during_day' => $this->faker->numberBetween(0, 50),
            'events_during_month' => $this->faker->numberBetween(0, 500),
            'notes_during_month' => $this->faker->numberBetween(0, 300),

            'calls_tokens_used' => $this->faker->numberBetween(0, 5000),

            'autoprolong' => 0,
            'prolongation' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'date_stop' => now()->addMonth(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'date_stop' => now()->subDay(),
        ]);
    }

    public function autoprolong(): static
    {
        return $this->state(fn () => [
            'autoprolong' => 1,
        ]);
    }

    public function prolongation(): static
    {
        return $this->state(fn () => [
            'prolongation' => 1,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }

    public function forTariff(Tariff $tariff): static
    {
        return $this->state(fn () => [
            'tariff_id' => $tariff->id,
        ]);
    }
}
