<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\TelegramAnswer;
use App\Models\Bot\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramAnswerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_telegram_answer(): void
    {
        $answer = TelegramAnswer::factory()->create();

        $this->assertDatabaseHas('telegram_answers', [
            'id' => $answer->id,
        ]);
    }

    public function test_filter_by_user_id(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'user_id' => 111,
        ]);

        TelegramAnswer::factory()->create([
            'user_id' => 222,
        ]);

        $result = TelegramAnswer::query()
            ->byUserId(111)
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $answer->id)
        );
    }

    public function test_filter_by_chat_id(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'chat_id' => 'chat_1',
        ]);

        TelegramAnswer::factory()->create([
            'chat_id' => 'chat_2',
        ]);

        $result = TelegramAnswer::query()
            ->byChatId('chat_1')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            'chat_1',
            $result->first()->chat_id
        );
    }

    public function test_filter_by_type(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'type' => 'callback',
        ]);

        TelegramAnswer::factory()->create([
            'type' => 'user',
        ]);

        $result = TelegramAnswer::query()
            ->byType('callback')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            'callback',
            $result->first()->type
        );
    }

    public function test_filter_by_status(): void
    {
        TelegramAnswer::factory()->create([
            'status' => 1,
        ]);

        TelegramAnswer::factory()->create([
            'status' => 0,
        ]);

        $result = TelegramAnswer::query()
            ->byStatus(1)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            1,
            $result->first()->status
        );
    }

    public function test_filter_temporary_answers(): void
    {
        TelegramAnswer::factory()->create([
            'is_temporary' => true,
        ]);

        TelegramAnswer::factory()->create([
            'is_temporary' => false,
        ]);

        $result = TelegramAnswer::query()
            ->isTemporary()
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->first()->is_temporary
        );
    }

    public function test_filter_not_temporary_answers(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'is_temporary' => false,
        ]);

        TelegramAnswer::factory()->create([
            'is_temporary' => true,
        ]);

        $result = TelegramAnswer::query()
            ->isNotTemporary()
            ->get();

        $this->assertCount(1, $result);

        $this->assertFalse(
            $result->first()->is_temporary
        );
    }

    public function test_filter_locked_after_date(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'locked_at' => now()->subHour(),
        ]);

        TelegramAnswer::factory()->create([
            'locked_at' => now()->subDays(2),
        ]);

        $result = TelegramAnswer::query()
            ->whereLockedAfter(now()->subDay())
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $answer->id)
        );
    }

    public function test_filter_locked_before_date(): void
    {
        $answer = TelegramAnswer::factory()->create([
            'locked_at' => now()->subDays(3),
        ]);

        TelegramAnswer::factory()->create([
            'locked_at' => now()->subHour(),
        ]);

        $result = TelegramAnswer::query()
            ->whereLockedBefore(now()->subDay())
            ->get();

        $this->assertCount(1, $result);

        $this->assertTrue(
            $result->contains('id', $answer->id)
        );
    }

    public function test_user_relation(): void
    {
        $user = User::factory()->create();

        $answer = TelegramAnswer::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(
            User::class,
            $answer->user
        );

        $this->assertEquals(
            $user->id,
            $answer->user->id
        );
    }
}
