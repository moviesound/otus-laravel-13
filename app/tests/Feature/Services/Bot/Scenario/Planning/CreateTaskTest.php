<?php

namespace Tests\Feature\Services\Bot\Scenario\Planning;

use Tests\BotTestCase;
use App\Models\Bot\Step;

class CreateTaskTest extends BotTestCase
{
    public function test_user_can_start_task_creation()
    {
        $user = $this->createBotUser();

        // пользователь пишет /plan
        $response = $this->sendMessage('/plan');

        $response->assertStatus(200);

        // проверяем состояние автомата
        $state = Step::where(
            'user_social_id',
            $user->social->id
        )->first();

        $this->assertNotNull($state);

        $this->assertEquals(
            'onPlanning',
            $state->scenario
        );

        $this->assertEquals(
            'selectPlanType',
            $state->step
        );
    }
}
