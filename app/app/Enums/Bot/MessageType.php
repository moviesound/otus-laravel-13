<?php

namespace App\Enums\Bot;

enum MessageType: string {
    case Message = 'message';
    case Callback = 'callback';
}
