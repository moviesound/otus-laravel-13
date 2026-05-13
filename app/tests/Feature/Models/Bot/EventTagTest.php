<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\EventTag;
use App\Models\Bot\Tag;
use App\Models\Bot\EventTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EventTagTest extends TestCase
{
    use RefreshDatabase;
    public function test_scope_by_event_template_id(): void
    {
        $event = EventTemplate::factory()->create();

        EventTag::factory()->create([
            'event_template_id' => $event->id,
            'tag_id' => Tag::factory()->create()->id,
        ]);

        EventTag::factory()->create([
            'event_template_id' => EventTemplate::factory()->create()->id,
            'tag_id' => Tag::factory()->create()->id,
        ]);

        $result = EventTag::byEventTplId($event->id)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($event->id, $result->first()->event_template_id);
    }

    public function test_scope_by_tag_id(): void
    {
        $tag = Tag::factory()->create();

        EventTag::factory()->create([
            'tag_id' => $tag->id,
            'event_template_id' => EventTemplate::factory()->create()->id,
        ]);

        EventTag::factory()->create([
            'tag_id' => Tag::factory()->create()->id,
            'event_template_id' => EventTemplate::factory()->create()->id,
        ]);

        $result = EventTag::byTagId($tag->id)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tag->id, $result->first()->tag_id);
    }

    public function test_event_tag_belongs_to_event_template(): void
    {
        $event = EventTemplate::factory()->create();

        $eventTag = EventTag::factory()->create([
            'event_template_id' => $event->id,
            'tag_id' => Tag::factory()->create()->id,
        ]);

        $this->assertEquals(
            $event->id,
            $eventTag->eventTemplate->id
        );
    }

    public function test_event_tag_belongs_to_tag(): void
    {
        $tag = Tag::factory()->create();

        $eventTag = EventTag::factory()->create([
            'tag_id' => $tag->id,
            'event_template_id' => EventTemplate::factory()->create()->id,
        ]);

        $this->assertEquals(
            $tag->id,
            $eventTag->tag->id
        );
    }

}
