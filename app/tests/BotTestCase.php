<?php

namespace Tests;

use App\DTO\Bot\Message\MessageDTO;
use App\DTO\Bot\State\StepStateDTO;
use App\DTO\Bot\User\UserDTO;
use App\Enums\Bot\MessageType;
use App\Models\Bot\User;
use App\Models\Bot\UserSocial;
use App\Services\Bot\Contexts\BotContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\Bot\FakeMessenger;

abstract class BotTestCase extends TestCase
{
    use RefreshDatabase;

    protected $connectionsToTransact = [
        'main',
    ];

    protected function createBotUser(): array
    {
        $user = User::factory()->create([
            'name' => 'Тестовый пользователь',
            'language' => 'ru',
            'timezone' => 'Europe/Moscow',
        ]);

        $social = UserSocial::factory()->create([
            'user_id' => $user->id,
            'type' => 'telegram',
            'social_id' => '123456789',
            'is_main' => 1,
        ]);

        return compact('user', 'social');
    }

    protected function makeBotContext(
        string $message = '/plan',
        ?FakeMessenger $messenger = null,
        array $scenarioData = [],
        ?string $additionalInfo = null,
        string $scenario = 'onPlanning',
        string $step = 'selectPlanType',
        int $politicsAgreed = 1,
    ): BotContext {

        $userDTO = new UserDTO(
            id: 1,
            name: 'Тест',
            sex: null,
            email: null,
            phone: null,
            phoneProved: 0,
            speaker: 'marina',
            tariffId: 1,
            language: 'ru',
            timezone: 'Europe/Moscow',
            locationId: null,
            birthDay: null,
            birthMonth: null,
            birthYear: null,
            politicsAgreed: $politicsAgreed,
            morningTimeWorkdays: '08:00',
            morningTimeHolidays: '10:00',
            eveningTimeWorkdays: '21:00',
            eveningTimeHolidays: '22:00',
            morningDigestStatus: 1,
            eveningDigestStatus: 1,
            digestCurrencies: 0,
            digestWeather: 1,
            userSocials: [],
        );


        return new BotContext(
            userDTO: $userDTO,

            userSocialId: 1,

            scenarioDTO: new StepStateDTO(
                userSocialId: 1,
                scenario: $scenario,
                step: $step,
                message: null,
                data: json_encode($scenarioData),
                additionalInfo: $additionalInfo,
                commonEntityId: null,
            ),

            messageDTO: new MessageDTO(
                type: MessageType::Message,
                text: $message,
                raw: [],
            ),

            messenger: $messenger ?? new FakeMessenger()
        );
    }

    protected function sendMessage(
        string $text,
        int $chatId = 123456789
    ) {
        return $this->postJson(
            route('telegram.webhook'),
            [
                'message' => [
                    'chat' => [
                        'id' => $chatId,
                    ],
                    'text' => $text,
                ]
            ]
        );
    }
}
