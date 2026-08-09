<?php

namespace Tests\Feature\Services\Bot\Scenario\Planning;

use Tests\BotTestCase;
use App\Models\Bot\Step;
use App\Services\Bot\Messengers\TelegramWebhook\TelegramGateway;
use Mockery;

class CreateTaskTest extends BotTestCase
{
    public function test_user_can_start_task_creation()
    {
        config(['queue.default' => 'sync']);
        $user = $this->createBotUser();

        // пользователь пишет /plan
        $this->mock(TelegramGateway::class, function ($mock) {
            $mock->shouldReceive('post')
                ->andReturn([
                    'ok' => true,
                    'result' => [
                        'message_id' => 1,
                    ],
                ]);
        });
        $response = $this->sendMessage('/plan');

        $response->assertStatus(200);

        // проверяем состояние автомата
        $state = Step::where(
            'user_social_id',
            $user['social']->id
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
