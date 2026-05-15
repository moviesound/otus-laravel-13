<?php

namespace Tests\Feature\Models\Bot;

use App\Models\Bot\Step;
use App\Models\Bot\UserSocial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StepTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_step()
    {
        Step::factory()->create([
            'user_social_id' => 1,
            'scenario' => 'test_scenario',
            'step' => 'step_1',
            'message' => 'Hello',
        ]);

        $this->assertDatabaseHas('steps', [
            'user_social_id' => 1,
            'scenario' => 'test_scenario',
        ]);
    }

    public function test_filter_by_user_social_id()
    {
        Step::factory()->create(['user_social_id' => 1]);
        Step::factory()->create(['user_social_id' => 2]);

        $result = Step::query()
            ->byUserSocialId(1)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(1, $result->first()->user_social_id);
    }

    public function test_filter_by_scenario()
    {
        Step::factory()->create(['scenario' => 'a']);
        Step::factory()->create(['scenario' => 'b']);

        $result = Step::query()
            ->byScenario('a')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('a', $result->first()->scenario);
    }

    public function test_filter_by_step()
    {
        Step::factory()->create(['step' => 'step_1']);
        Step::factory()->create(['step' => 'step_2']);

        $result = Step::query()
            ->byStep('step_1')
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_filter_by_common_entity_id()
    {
        Step::factory()->create(['common_entity_id' => 1]);
        Step::factory()->create(['common_entity_id' => 2]);

        $result = Step::query()
            ->byCommonEntityId(1)
            ->get();

        $this->assertCount(1, $result);
    }

    public function test_it_returns_all_when_common_entity_id_is_null()
    {
        Step::factory()->count(3)->create();

        $result = Step::query()
            ->byCommonEntityId(null)
            ->get();

        $this->assertCount(3, $result);
    }

    public function test_it_belongs_to_user_social()
    {
        $userSocial = UserSocial::factory()->create();

        $step = Step::factory()->create([
            'user_social_id' => $userSocial->id,
        ]);

        $this->assertInstanceOf(UserSocial::class, $step->userSocial);
        $this->assertEquals($userSocial->id, $step->userSocial->id);
    }
}
