<?php

namespace App\Models\Admin;

use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    protected $connection = 'admin';
    protected string $guard_name = 'admin';
}

