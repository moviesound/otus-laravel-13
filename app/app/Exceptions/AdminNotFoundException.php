<?php

namespace App\Exceptions;

use App\Exceptions\Helper\DontReport;

class AdminNotFoundException extends DontReport
{
    public function __construct()
    {
        parent::__construct('Пользователь не найден.');
    }
}
