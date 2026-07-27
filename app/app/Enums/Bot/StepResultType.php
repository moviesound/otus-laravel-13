<?php

namespace App\Enums\Bot;

enum StepResultType: string
{
    case SWITCH = 'switch';
    case REPEAT = 'repeat';
    case FINISH = 'finish';
}
