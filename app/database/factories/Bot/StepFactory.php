<?php

namespace Database\Factories\Bot;

use App\Models\Bot\Step;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Step>
 */
class StepFactory extends Factory
{
    protected $model = Step::class;

    public function definition(): array
    {
        static $id = 1;

        return [
            'user_social_id' => $id++,

            'scenario' => $this->faker->randomElement([
                'registration',
                'onboarding',
                'payment',
                'support',
            ]),

            'step' => $this->faker->randomElement([
                'start',
                'middle',
                'end',
                'retry',
            ]),

            'message' => $this->faker->sentence(),

            'data' => json_encode([
                'foo' => $this->faker->word(),
                'bar' => $this->faker->numberBetween(1, 100),
            ]),

            'additional_info' => $this->faker->optional()->text(),

            'common_entity_id' => $this->faker->optional()->numberBetween(1, 100),
        ];
    }
}
