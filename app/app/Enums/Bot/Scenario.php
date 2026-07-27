<?php

namespace App\Enums\Bot;

enum Scenario: string
{
    case Planning = 'onPlanning';
    case Menu = 'onMenu';
    case Politics = 'onPolitics';
    case OnBoarding = 'onBoarding';
}
