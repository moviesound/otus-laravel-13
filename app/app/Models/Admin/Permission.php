<?php

namespace App\Models\Admin;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    protected $connection = 'admin';
    protected string $guard_name = 'admin';
}
