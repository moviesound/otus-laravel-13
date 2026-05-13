<?php

namespace Database\Factories\Bot;

use App\Models\Bot\ReminderQueue;
use App\Models\Bot\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderQueueFactory extends Factory
{
    protected $model = ReminderQueue::class;

    public function definition(): array
    {
        return [
            'reminder_id' => null,
            'user_id' => User::factory(),

            'channel' => $this->faker->randomElement([
                'telegram',
                'vk',
                'max',
                'sms',
                'call',
            ]),

            'status' => $this->faker->randomElement([
                'pending',
                'processing',
                'done',
                'failed',
                'call',
            ]),

            'sent_times' => $this->faker->numberBetween(0, 5),

            'last_sent_at' => $this->faker->optional()->dateTimeBetween('-10 days', 'now'),

            'date_remind' => $this->faker->optional()->dateTimeBetween('now', '+10 days'),

            'process_name' => $this->faker->optional()->lexify('process_??????'),

            'locked_by' => null,
            'locked_at' => null,
        ];
    }

    /**
     * status: pending
     */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
        ]);
    }

    /**
     * status: processing
     */
    public function processing(): static
    {
        return $this->state(fn () => [
            'status' => 'processing',
            'locked_by' => 'test-worker',
            'locked_at' => now(),
        ]);
    }

    /**
     * status: done
     */
    public function done(): static
    {
        return $this->state(fn () => [
            'status' => 'done',
        ]);
    }

    /**
     * status: failed
     */
    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }

    /**
     * Telegram channel
     */
    public function telegram(): static
    {
        return $this->state(fn () => [
            'channel' => 'telegram',
        ]);
    }

    /**
     * VK channel
     */
    public function vk(): static
    {
        return $this->state(fn () => [
            'channel' => 'vk',
        ]);
    }

    /**
     * max channel
     */
    public function max(): static
    {
        return $this->state(fn () => [
            'channel' => 'max',
        ]);
    }
}
