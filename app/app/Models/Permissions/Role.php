<?php

namespace App\Models\Permissions;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $connection = 'admin';
}

