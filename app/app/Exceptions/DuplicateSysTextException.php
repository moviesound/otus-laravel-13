<?php

namespace App\Exceptions;

use App\Exceptions\Helper\DontReport;

class DuplicateSysTextException extends DontReport
{
    public function __construct(
        string $alias,
        string $lang
    ) {
        parent::__construct("Alias '{$alias}' уже существует для языка {$lang}");
    }
}
