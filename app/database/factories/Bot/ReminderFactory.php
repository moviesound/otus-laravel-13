<?php

namespace Database\Factories\Bot;

use App\Models\Bot\Reminder;
use App\Models\Bot\ReminderTemplate;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            'pending',
            'done',
            'processing',
            'sent',
            'failed',
            'call'
        ]);

        $now = now();

        return [
            'template_id' => ReminderTemplate::factory(),

            'date_remind' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),

            'status' => $status,

            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    //States

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => 'sent',
            'date_remind' => now()->subDays(rand(1, 3)),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }

    public function call(): static
    {
        return $this->state(fn () => [
            'status' => 'call',
        ]);
    }

    public function scheduled(?DateTimeInterface $date = null): static
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'date_remind' => $date ?: now()->addDays(rand(1, 5)),
                'status' => 'pending',
            ];
        });
    }
}
