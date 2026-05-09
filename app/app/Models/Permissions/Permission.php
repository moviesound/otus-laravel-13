<?php

namespace App\Models\Permissions;

use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    protected $connection = 'admin';
}
