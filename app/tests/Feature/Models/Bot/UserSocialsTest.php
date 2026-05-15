<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\User;
use App\Models\Bot\UserSocial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSocialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_user_social(): void
    {
        $social = UserSocial::factory()->create();

        $this->assertDatabaseHas('user_socials', [
            'id' => $social->id,
        ]);
    }

    public function test_filter_by_user_id(): void
    {
        $user = User::factory()->create();

        $social = UserSocial::factory()->create([
            'user_id' => $user->id,
            'type' => 'vk',
        ]);

        UserSocial::factory()->create([
            'type' => 'telegram',
        ]);

        $result = UserSocial::query()
            ->byUserId($user->id)
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $social->id,
            $result->first()->id
        );
    }

    public function test_filter_by_type(): void
    {
        $social = UserSocial::factory()->create([
            'type' => 'telegram',
        ]);

        UserSocial::factory()->create([
            'type' => 'vk',
        ]);

        $result = UserSocial::query()
            ->byType('telegram')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $social->id,
            $result->first()->id
        );
    }

    public function test_filter_by_social_id(): void
    {
        $social = UserSocial::factory()->create([
            'social_id' => '123456',
            'type' => 'telegram',
        ]);

        UserSocial::factory()->create([
            'social_id' => '999999',
            'type' => 'vk',
        ]);

        $result = UserSocial::query()
            ->bySocialId('123456')
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $social->id,
            $result->first()->id
        );
    }

    public function test_filter_main_socials(): void
    {
        $social = UserSocial::factory()->create([
            'is_main' => 1,
            'type' => 'telegram',
        ]);

        UserSocial::factory()->create([
            'is_main' => 0,
            'type' => 'vk',
        ]);

        $result = UserSocial::query()
            ->isMain()
            ->get();

        $this->assertCount(1, $result);

        $this->assertEquals(
            $social->id,
            $result->first()->id
        );
    }

    public function test_it_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $social = UserSocial::factory()->create([
            'user_id' => $user->id,
            'type' => 'telegram',
        ]);

        $this->assertInstanceOf(
            User::class,
            $social->user
        );

        $this->assertEquals(
            $user->id,
            $social->user->id
        );
    }
}
