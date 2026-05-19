<?php

namespace App\Exceptions;

use App\Exceptions\Helper\DontReport;

class SysTextNotFoundException extends DontReport
{
    public function __construct()
    {
        parent::__construct('Такой текст не найден.');
    }
}
