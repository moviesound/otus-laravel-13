<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\TelegramSysMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramSysMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_telegram_sys_message(): void
    {
        $message = TelegramSysMessage::factory()->create();

        $this->assertDatabaseHas('telegram_sys_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_filter_by_chat_id(): void
    {
        $message = TelegramSysMessage::factory()->create([
            'chat_id' => 'chat_1',
        ]);

        TelegramSysMessage::factory()->create([
            'chat_id' => 'chat_2',
        ]);

        $result = TelegramSysMessage::query()
            ->byChatId('chat_1')
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $message->id)
        );
    }

    public function test_filter_by_message_id(): void
    {
        TelegramSysMessage::factory()->create([
            'message_id' => 'msg_1',
        ]);

        TelegramSysMessage::factory()->create([
            'message_id' => 'msg_2',
        ]);

        $result = TelegramSysMessage::query()
            ->byMessageId('msg_1')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            'msg_1',
            $result->first()->message_id
        );
    }

    public function test_filter_by_queue_id(): void
    {
        TelegramSysMessage::factory()->create([
            'queue_id' => 111,
        ]);

        TelegramSysMessage::factory()->create([
            'queue_id' => 222,
        ]);

        $result = TelegramSysMessage::query()
            ->byQueueId(111)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            111,
            $result->first()->queue_id
        );
    }

    public function test_filter_temporary_messages(): void
    {
        TelegramSysMessage::factory()->temporary()->create();

        TelegramSysMessage::factory()
            ->notTemporary()
            ->create();

        $result = TelegramSysMessage::query()
            ->isTemporary()
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->first()->is_temporary
        );
    }

    public function test_filter_not_temporary_messages(): void
    {
        TelegramSysMessage::factory()
            ->notTemporary()
            ->create();

        TelegramSysMessage::factory()
            ->temporary()
            ->create();

        $result = TelegramSysMessage::query()
            ->isNotTemporary()
            ->get();

        $this->assertCount(1, $result);

        $this->assertFalse(
            $result->first()->is_temporary
        );
    }

    public function test_filter_created_after_date(): void
    {
        $message = TelegramSysMessage::factory()->create([
            'created_at' => now()->subHour(),
        ]);

        TelegramSysMessage::factory()->create([
            'created_at' => now()->subDays(3),
        ]);

        $result = TelegramSysMessage::query()
            ->createdAfter(now()->subDay())
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $message->id)
        );
    }

    public function test_filter_created_before_date(): void
    {
        $message = TelegramSysMessage::factory()->create([
            'created_at' => now()->subDays(3),
        ]);

        TelegramSysMessage::factory()->create([
            'created_at' => now()->subHour(),
        ]);

        $result = TelegramSysMessage::query()
            ->createdBefore(now()->subDay())
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $message->id)
        );
    }
}
