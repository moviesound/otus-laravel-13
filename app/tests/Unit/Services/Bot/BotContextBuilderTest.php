<?php

namespace Tests\Unit\Services\Bot;

use App\DTO\Bot\BotInput;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;
use App\Services\Bot\BotContext;
use App\Services\Bot\BotContextBuilder;
use App\Services\Bot\Scenario\Planning\Steps\SelectPlanTypeStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BotContextBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = ['main'];

    public function test_build_bot_context(): void
    {
        /*
         * Создаём пользователя Telegram
         */
        $user = User::factory()->create();

        $chatId = 223578088;

        $userSocial = UserSocial::factory()
            ->telegram($chatId)
            ->create([
                'user_id' => $user->id,
            ]);

        /*
         * Входные данные
         */
        $input = new BotInput(
            chatId: $chatId,
            messenger: 'telegram',
            text: '/plan',
            files: [],
        );

        /*
         * Собираем контекст
         */
        $builder = $this->app->make(
            BotContextBuilder::class
        );

        $context = $builder->build($input);

        /*
         * Проверяем результат
         */
        $this->assertInstanceOf(
            BotContext::class,
            $context
        );

        $this->assertSame(
            $user->id,
            $context->userDTO->id
        );

        $this->assertSame(
            $userSocial->id,
            $context->userSocialId
        );

        $this->assertSame(
            '/plan',
            $context->messageDTO->text
        );

        $this->assertSame(
            'onPlanning',
            $context->scenarioDTO->scenario
        );

        $this->assertSame(
            SelectPlanTypeStep::STEP_KEY,
            $context->scenarioDTO->step
        );

        $this->assertNotNull(
            $context->messenger
        );
    }
}
