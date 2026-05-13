<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use App\Rules\AllowedLang;

class AllowedLangTest extends TestCase
{
    public function test_allowed_lang_validation_passes()
    {
        $validator = Validator::make(
            [
                'lang' => 'en',
            ],
            [
                'lang' => [new AllowedLang(['en', 'ru'])],
            ]
        );

        $this->assertFalse($validator->fails());
    }

    public function test_allowed_lang_validation_fails()
    {
        $validator = Validator::make(
            [
                'lang' => 'de',
            ],
            [
                'lang' => [new AllowedLang(['en', 'ru'])],
            ]
        );

        $this->assertTrue($validator->fails());

        $this->assertEquals(
            'язык должен быть из списка: en, ru',
            $validator->errors()->first('lang')
        );
    }
}
