<?php

namespace Tests\Unit\Services\Bot\Helpers\Scenarios\Messages;

use App\Contracts\SysTextInterface;
use App\Services\Bot\Helpers\Scenarios\Messages\MessageButtons;
use App\Services\Bot\Messengers\MessengerTextResolver;
use Tests\TestCase;

class MessageButtonsTest extends TestCase
{
    private function makeResolver(): MessengerTextResolver
    {
        $sysText = $this->mock(
            SysTextInterface::class
        );

        $sysText
            ->shouldReceive('get')
            ->andReturnUsing(
                function (
                    string $alias,
                    string $lang,
                    array $replace = []
                ) {
                    return "{$alias}_{$lang}";
                }
            );


        return new MessengerTextResolver(
            $sysText
        );
    }


    public function test_builds_keyboard_from_callback_keys(): void
    {
        $keyboard = MessageButtons::make(
            [
                'save',
                'cancel',
            ],
            $this->makeResolver(),
            'telegram',
            'ru',
        );


        $this->assertEquals(
            [
                [
                    [
                        'text' => 'telegram_save_ru',
                        'callback_data' => 'save',
                    ],
                    [
                        'text' => 'telegram_cancel_ru',
                        'callback_data' => 'cancel',
                    ],
                ],
            ],
            $keyboard
        );
    }


    public function test_splits_buttons_by_rows(): void
    {
        $keyboard = MessageButtons::make(
            [
                'one',
                'two',
                'three',
                'four',
            ],
            $this->makeResolver(),
            'telegram',
            'ru',
            3,
        );


        $this->assertCount(2, $keyboard);

        $this->assertCount(
            3,
            $keyboard[0]
        );

        $this->assertCount(
            1,
            $keyboard[1]
        );
    }


    public function test_supports_custom_button_array(): void
    {
        $keyboard = MessageButtons::make(
            [
                [
                    'name' => 'My button',
                    'callback' => 'my_callback',
                ],
            ],
            $this->makeResolver(),
            'telegram',
            'ru',
        );


        $this->assertEquals(
            [
                [
                    [
                        'text' => 'My button',
                        'callback_data' => 'my_callback',
                    ],
                ],
            ],
            $keyboard
        );
    }


    public function test_uses_unknown_values_for_invalid_custom_button(): void
    {
        $keyboard = MessageButtons::make(
            [
                [],
            ],
            $this->makeResolver(),
            'telegram',
            'ru',
        );


        $this->assertEquals(
            [
                [
                    [
                        'text' => 'unknown',
                        'callback_data' => 'unknown',
                    ],
                ],
            ],
            $keyboard
        );
    }


    public function test_default_actions_returns_empty_array_when_disabled(): void
    {
        $result = MessageButtons::defaultActions(
            $this->makeResolver(),
            'telegram',
            'ru',
        );


        $this->assertEquals(
            [],
            $result
        );
    }


    public function test_default_actions_builds_requested_buttons(): void
    {
        $result = MessageButtons::defaultActions(
            $this->makeResolver(),
            'telegram',
            'ru',
            backBtn: true,
            skipBtn: true,
            cancelBtn: true,
        );


        $this->assertEquals(
            [
                [
                    'text' => 'telegram_back_ru',
                    'callback_data' => 'back',
                ],
                [
                    'text' => 'telegram_skip_ru',
                    'callback_data' => 'skip',
                ],
                [
                    'text' => 'telegram_cancel_ru',
                    'callback_data' => 'cancel',
                ],
            ],
            $result
        );
    }


    public function test_default_actions_returns_only_enabled_buttons(): void
    {
        $result = MessageButtons::defaultActions(
            $this->makeResolver(),
            'telegram',
            'en',
            skipBtn: true,
        );


        $this->assertEquals(
            [
                [
                    'text' => 'telegram_skip_en',
                    'callback_data' => 'skip',
                ],
            ],
            $result
        );
    }
}
