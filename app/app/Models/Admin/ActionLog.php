<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use MongoDB\Laravel\Eloquent\Model;

#[Fillable(['user_id', 'action', 'ip',
    'user_agent', 'created_at',])]
class ActionLog extends Model
{
    protected $connection = 'mongodb_admin';

    protected $collection = 'actions_logs';

    public $timestamps = false;
}
