<?php

namespace Database\Factories\Bot;

use App\Models\Bot\CommonEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommonEntity>
 */
class CommonEntityFactory extends Factory
{
    protected $model = CommonEntity::class;

    public function definition(): array
    {
        return [
            'created_at' => now(),
        ];
    }
}
