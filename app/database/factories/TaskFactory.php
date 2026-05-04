<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement([
            'pending',
            'processing',
            'done',
            'overdue',
            'canceled'
        ]);

        $isDeadline = rand(0, 1);

        $now = now();

        return [
            'template_id' => TaskTemplate::factory(),
            'status' => $status,

            'period_start' => $isDeadline
                ? null
                : $now->copy()->addDays(rand(0, 3)),

            'period_end' => $isDeadline
                ? null
                : $now->copy()->addDays(rand(4, 10)),

            'deadline' => $isDeadline
                ? $now->copy()->addDays(rand(1, 10))
                : null,

            'check_remind_next_time' => $this->faker->optional()->dateTimeBetween('now', '+3 days'),
            'next_system_remind_at' => $this->faker->optional()->dateTimeBetween('now', '+5 days'),
            'last_shown_in_digest_at' => $this->faker->optional()->dateTimeBetween('-5 days', 'now'),

        ];
    }

    // States

    public function deadline(): static
    {
        return $this->state(fn () => [
            'period_start' => null,
            'period_end' => null,
            'deadline' => now()->addDays(rand(1, 10)),
        ]);
    }

    public function period(): static
    {
        return $this->state(fn () => [
            'period_start' => now(),
            'period_end' => now()->addDays(rand(2, 7)),
            'deadline' => null,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn () => [
            'status' => 'done',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => 'overdue',
            'deadline' => now()->subDays(rand(1, 5)),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }
}
