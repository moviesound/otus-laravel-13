<?php

namespace Tests\Unit\Listeners\Bot\TelegramWebhook;

use App\Contracts\Bot\Messengers\MessageHandlerInterface;
use App\DTO\Bot\Message\MessageDTO;
use App\DTO\Bot\State\StepStateDTO;
use App\DTO\Bot\TelegramWebhook\TelegramMessageDTO;
use App\DTO\Bot\User\UserDTO;
use App\Enums\Bot\MessageType;
use App\Events\Bot\TelegramWebhook\TelegramMessageEvent;
use App\Listeners\Bot\TelegramWebhook\ProcessTelegramMessageListener;
use App\Services\Bot\BotContext;
use App\Services\Bot\BotContextBuilder;
use Mockery;
use Tests\TestCase;

class ProcessTelegramMessageListenerTest extends TestCase
{
    public function test_listener_calls_message_handler(): void
    {
        $context = new BotContext(
            userDTO: new UserDTO(
                id: 1,
                name: 'Sergey',
                sex: null,
                email: null,
                phone: null,
                phoneProved: 1,
                speaker: 'ru',
                tariffId: 1,
                language: 'ru',
                timezone: 'Europe/Moscow',
                locationId: null,
                birthDay: null,
                birthMonth: null,
                birthYear: null,
                politicsAgreed: 1,
                morningTimeWorkdays: '09:00',
                morningTimeHolidays: '10:00',
                eveningTimeWorkdays: '18:00',
                eveningTimeHolidays: '18:00',
                morningDigestStatus: 1,
                eveningDigestStatus: 1,
                digestCurrencies: 0,
                digestWeather: 0,
                userSocials: [],
            ),
            userSocialId: 10,
            scenarioDTO: new StepStateDTO(
                userSocialId: 10,
                scenario: 'onPlanning',
                step: 'selectPlanType',
                message: null,
                data: null,
                additionalInfo: null,
                commonEntityId: null,
            ),
            messageDTO: new MessageDTO(
                type: MessageType::Message,
                text: 'task',
                raw: [],
            ),
            messenger: null,
        );

        $builder = Mockery::mock(BotContextBuilder::class);

        $builder->shouldReceive('build')
            ->once()
            ->andReturn($context);

        $handler = Mockery::mock(MessageHandlerInterface::class);

        $handler->shouldReceive('handle')
            ->once()
            ->with($context);

        $listener = new ProcessTelegramMessageListener(
            $handler,
            $builder,
        );

        $dto = new TelegramMessageDTO(
            chatId: 223578088,
            type: MessageType::Message,
            payload: [
                'message' => [
                    'chat' => [
                        'id' => 223578088,
                    ],
                    'text' => 'task',
                ],
            ],
        );

        $listener->handle(
            new TelegramMessageEvent($dto)
        );
    }
}
