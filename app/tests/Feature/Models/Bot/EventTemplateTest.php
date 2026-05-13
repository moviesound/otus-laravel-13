<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\EventTemplate;
use App\Models\Bot\Event;
use App\Models\Bot\User;
use App\Models\Bot\CommonEntity;
use App\Models\Bot\Tag;
use App\Models\Bot\ReminderTemplate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\TestCase;

class EventTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_by_user_id(): void
    {
        EventTemplate::factory()->create(['user_id' => 1]);
        EventTemplate::factory()->create(['user_id' => 2]);

        $result = EventTemplate::byUserId(1)->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->user_id);
    }

    public function test_scope_is_active(): void
    {
        EventTemplate::factory()->create(['status' => 0]);
        $active = EventTemplate::factory()->create(['status' => 1]);

        $result = EventTemplate::isActive()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($active->id, $result->first()->id);
    }

    public function test_scope_is_repeated(): void
    {
        EventTemplate::factory()->create(['repeat_type' => 'none']);
        $repeated = EventTemplate::factory()->create(['repeat_type' => 'daily']);

        $result = EventTemplate::isRepeated()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($repeated->id, $result->first()->id);
    }

    public function test_scope_by_status(): void
    {
        EventTemplate::factory()->create(['status' => 0]);
        $tpl = EventTemplate::factory()->create(['status' => 1]);

        $result = EventTemplate::byStatus(1)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tpl->id, $result->first()->id);
    }

    public function test_scope_by_event_type(): void
    {
        EventTemplate::factory()->create(['event_type' => 'birthday']);
        $tpl = EventTemplate::factory()->create(['event_type' => 'meeting']);

        $result = EventTemplate::byEventType('meeting')->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tpl->id, $result->first()->id);
    }

    public function test_scope_with_call(): void
    {
        EventTemplate::factory()->create(['has_call' => 0]);
        $tpl = EventTemplate::factory()->create(['has_call' => 1]);

        $result = EventTemplate::withCall()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tpl->id, $result->first()->id);
    }

    public function test_scope_with_sms(): void
    {
        EventTemplate::factory()->create(['has_sms' => 0]);
        $tpl = EventTemplate::factory()->create(['has_sms' => 1]);

        $result = EventTemplate::withSms()->get();

        $this->assertCount(1, $result);
        $this->assertEquals($tpl->id, $result->first()->id);
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $tpl = EventTemplate::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(
            User::class,
            $tpl->user
        );

        $this->assertEquals($user->id, $tpl->user->id);
    }

    public function test_it_has_many_events(): void
    {
        $tpl = EventTemplate::factory()->create();

        $event = Event::factory()->create([
            'template_id' => $tpl->id,
        ]);

        $this->assertCount(1, $tpl->events);
        $this->assertEquals($event->id, $tpl->events->first()->id);
    }

    public function test_it_belongs_to_many_tags(): void
    {
        $tpl = EventTemplate::factory()->create();

        $tags = Tag::factory()->count(2)->create();

        $tpl->tags()->attach($tags->pluck('id'));

        $this->assertCount(2, $tpl->tags);
        $this->assertEquals(
            $tags->first()->id,
            $tpl->tags->first()->id
        );
    }

    public function test_it_belongs_to_many_entities(): void
    {
        $tpl = EventTemplate::factory()->create();

        $entity = CommonEntity::factory()->create();

        $tpl->entities()->attach($entity->id);

        $this->assertCount(1, $tpl->entities);
        $this->assertEquals($entity->id, $tpl->entities->first()->id);
    }

    public function test_it_has_morph_many_reminders(): void
    {
        $tpl = EventTemplate::factory()->create();

        $reminder = ReminderTemplate::factory()->create([
            'entity_id' => $tpl->id,
            'entity_type' => 'event',
        ]);

        $this->assertCount(1, $tpl->reminders);
        $this->assertEquals($reminder->id, $tpl->reminders->first()->id);
    }

}
