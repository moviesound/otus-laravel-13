<?php

namespace App\Exceptions;

use App\Exceptions\Helper\DontReport;

class DuplicateAdminException extends DontReport
{
    public function __construct( string $email)
    {
        parent::__construct("Пользователь c email: {$email} уже существует.");
    }
}
