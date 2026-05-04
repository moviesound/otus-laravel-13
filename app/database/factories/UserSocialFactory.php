<?php

namespace Database\Factories;

use App\Models\UserSocial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSocial>
 */
class UserSocialFactory extends Factory
{
    protected $model = UserSocial::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'telegram',
            'vk',
            'max'
        ]);

        return [
            'user_id' => User::factory(),

            'type' => $type,

            'social_id' => $this->faker->bothify('#######'),

            'is_main' => rand(0, 1),
            'keyboard' => rand(0, 1),

            'current_folder_s3' => $this->faker->optional()->numberBetween(1, 1000),

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
