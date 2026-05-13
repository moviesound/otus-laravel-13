<?php

namespace Database\Factories\Bot;

use App\Models\Bot\EventTag;
use App\Models\Bot\EventTemplate;
use App\Models\Bot\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventTagFactory extends Factory
{
    protected $model = EventTag::class;

    public function definition(): array
    {
        return [
            'event_template_id' => EventTemplate::factory(),
            'tag_id' => Tag::factory(),
        ];
    }
}
