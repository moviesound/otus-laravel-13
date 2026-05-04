<?php

namespace Database\Factories;

use App\Models\ReminderTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReminderTemplate>
 */
class ReminderTemplateFactory extends Factory
{
    protected $model = ReminderTemplate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),

            'text' => $this->faker->optional()->sentence(),

            'remind_type' => $this->faker->randomElement([
                'hours', 'days', 'weeks', 'months'
            ]),

            'remind_value' => $this->faker->numberBetween(1, 100),

            'is_sub_task' => 0,

            'entity_type' => $this->faker->randomElement(['task', 'event']),// ⚠️ используем morphMap ключи
            'entity_id' => $this->faker->numberBetween(1,20),

            'has_call' => $this->faker->randomNumber([0,1]),
            'has_sms' => $this->faker->randomNumber([0,1]),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /* States */

    public function forTaskTemplate($template): static
    {
        return $this->state(fn () => [
            'entity_type' => $template->getMorphClass(),
            'entity_id' => $template->id,
            'user_id' => $template->user_id,
        ]);
    }

    public function forEventTemplate($template): static
    {
        return $this->state(fn () => [
            'entity_type' => $template->getMorphClass(),
            'entity_id' => $template->id,
            'user_id' => $template->user_id,
        ]);
    }
}
