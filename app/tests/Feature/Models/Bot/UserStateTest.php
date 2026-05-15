<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\User;
use App\Models\Bot\UserState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_user_state(): void
    {
        $state = UserState::factory()->create();

        $this->assertDatabaseHas('user_states', [
            'user_id' => $state->user_id,
        ]);
    }

    public function test_filter_by_user_id(): void
    {
        $user = User::factory()->create();

        $state = UserState::factory()->create([
            'user_id' => $user->id,
        ]);

        UserState::factory()->create();

        $result = UserState::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $state->user_id,
            $result->first()->user_id
        );
    }

    public function test_filter_next_morning_digest_after(): void
    {
        $state = UserState::factory()->create([
            'next_morning_digest' => now()->addDay(),
        ]);

        UserState::factory()->create([
            'next_morning_digest' => now()->subDays(3),
        ]);

        $result = UserState::query()
            ->whereNextMorningDigestAfter(now())
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_filter_next_morning_digest_before(): void
    {
        $state = UserState::factory()->create([
            'next_morning_digest' => now()->subDays(3),
        ]);

        UserState::factory()->create([
            'next_morning_digest' => now()->addDay(),
        ]);

        $result = UserState::query()
            ->whereNextMorningDigestBefore(now())
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_filter_next_morning_digest_between(): void
    {
        $state = UserState::factory()->create([
            'next_morning_digest' => now()->addHours(5),
        ]);

        UserState::factory()->create([
            'next_morning_digest' => now()->addDays(10),
        ]);

        $result = UserState::query()
            ->whereNextMorningDigestBetween(
                now(),
                now()->addDay()
            )
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_filter_next_evening_digest_after(): void
    {
        $state = UserState::factory()->create([
            'next_evening_digest' => now()->addDay(),
        ]);

        UserState::factory()->create([
            'next_evening_digest' => now()->subDays(2),
        ]);

        $result = UserState::query()
            ->whereNextEveningDigestAfter(now())
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_filter_next_evening_digest_before(): void
    {
        $state = UserState::factory()->create([
            'next_evening_digest' => now()->subDays(2),
        ]);

        UserState::factory()->create([
            'next_evening_digest' => now()->addDay(),
        ]);

        $result = UserState::query()
            ->whereNextEveningDigestBefore(now())
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_filter_next_evening_digest_between(): void
    {
        $state = UserState::factory()->create([
            'next_evening_digest' => now()->addHours(10),
        ]);

        UserState::factory()->create([
            'next_evening_digest' => now()->addDays(5),
        ]);

        $result = UserState::query()
            ->whereNextEveningDigestBetween(
                now(),
                now()->addDay()
            )
            ->get();

        $this->assertTrue(
            $result->contains('user_id', $state->user_id)
        );
    }

    public function test_increase_balance(): void
    {
        $state = UserState::factory()->create([
            'balance' => 100,
        ]);

        $state->increaseBalance(50);

        $this->assertEquals(
            150,
            $state->fresh()->balance
        );
    }

    public function test_decrease_balance(): void
    {
        $state = UserState::factory()->create([
            'balance' => 100,
        ]);

        $state->decreaseBalance(30);

        $this->assertEquals(
            70,
            $state->fresh()->balance
        );
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $state = UserState::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(
            User::class,
            $state->user
        );

        $this->assertEquals(
            $user->id,
            $state->user->id
        );
    }
}
