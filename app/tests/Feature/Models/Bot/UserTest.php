<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\ReminderQueue;
use App\Models\Bot\User;
use App\Models\Bot\UserPhoneProof;
use App\Models\Bot\UserSocial;
use App\Models\Bot\UserState;
use App\Models\Bot\UserTariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_user(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_filter_active_users(): void
    {
        $active = User::factory()->create([
            'status' => 1,
        ]);

        User::factory()->create([
            'status' => 0,
        ]);

        $result = User::query()
            ->isActive()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($active->id, $result->first()->id);
    }

    public function test_filter_setted_users(): void
    {
        $user = User::factory()->create([
            'is_setted' => 1,
        ]);

        User::factory()->create([
            'is_setted' => 0,
        ]);

        $result = User::query()
            ->isSetted()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_politics_agreed_users(): void
    {
        $user = User::factory()->create([
            'politics_agreed' => 1,
        ]);

        User::factory()->create([
            'politics_agreed' => 0,
        ]);

        $result = User::query()
            ->isPoliticsAgreed()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_phone_proved_users(): void
    {
        $user = User::factory()->create([
            'phone_proved' => 1,
        ]);

        User::factory()->create([
            'phone_proved' => 0,
        ]);

        $result = User::query()
            ->isPhoneProved()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_morning_digest_enabled_users(): void
    {
        $user = User::factory()->create([
            'morning_digest_status' => 1,
        ]);

        User::factory()->create([
            'morning_digest_status' => 0,
        ]);

        $result = User::query()
            ->isMorningDigestEnabled()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_evening_digest_enabled_users(): void
    {
        $user = User::factory()->create([
            'evening_digest_status' => 1,
        ]);

        User::factory()->create([
            'evening_digest_status' => 0,
        ]);

        $result = User::query()
            ->isEveningDigestEnabled()
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'email' => 'other@example.com',
        ]);

        $result = User::query()
            ->byEmail('test@example.com')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_by_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+79999999999',
        ]);

        User::factory()->create([
            'phone' => '+78888888888',
        ]);

        $result = User::query()
            ->byPhone('+79999999999')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_filter_by_user_id(): void
    {
        $user = User::factory()->create();

        User::factory()->create();

        $result = User::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_user_state_relation(): void
    {
        $user = User::factory()->create();

        UserState::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(
            UserState::class,
            $user->state
        );
    }

    public function test_user_socials_relation(): void
    {
        $user = User::factory()->create();

        UserSocial::factory()->create([
            'user_id' => $user->id,
            'type' => 'telegram',
        ]);

        UserSocial::factory()->create([
            'user_id' => $user->id,
            'type' => 'vk',
        ]);

        $this->assertCount(2, $user->socials);
    }

    public function test_user_tariffs_relation(): void
    {
        $user = User::factory()->create();

        UserTariff::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->tariffs);
    }

    public function test_user_phone_proofs_relation(): void
    {
        $user = User::factory()->create();

        UserPhoneProof::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->phoneProofs);
    }

    public function test_user_reminder_queues_relation(): void
    {
        $user = User::factory()->create();

        ReminderQueue::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->reminderQueues);
    }
}
