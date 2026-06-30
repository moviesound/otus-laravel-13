<?php

namespace App\Enums\Bot;

enum MessageIntent: string
{
    case Back = 'back';
    case Continue = 'continue';
    case Stop = 'stop';
    case Deny = 'deny';
    case Edit = 'edit';
    case Delete = 'delete';
    case Add = 'add';
    case Unknown = 'unknown';
}
