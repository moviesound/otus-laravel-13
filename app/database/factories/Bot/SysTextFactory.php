<?php

namespace Database\Factories\Bot;

use App\Models\Bot\SysText;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SysText>
 */
class SysTextFactory extends Factory
{
    protected $model = SysText::class;

    public function definition(): array
    {
        return [
            'alias' => Str::snake($this->faker->unique()->words(2, true)),

            'lang' => $this->faker->randomElement([
                'ru',
                'en',
                'de',
                'fr',
            ]),

            'context' => $this->faker->sentence(),
        ];
    }
}
