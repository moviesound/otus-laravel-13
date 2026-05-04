<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'description', 'period_days', 'date_start', 'date_stop',
    'status', 'cost', 'currency', 'calls_tokens', 'events_per_day',
    'events_per_month', 'notes_per_month', 'total_notes', 'total_lists',
    'items_in_list', 'ai_tokens', 'files_volume', 'shared_space',
    'default', 'autoprolong', 'prolongation', 'action_placeholder',
])]
class Tariff extends Model
{
    protected $table = 'tariffs';

    public $timestamps = false;

    protected $casts = [
        'period_days' => 'integer',
        'date_start' => 'datetime',
        'date_stop' => 'datetime',
        'status' => 'integer',
        'cost' => 'decimal:4',
        'calls_tokens' => 'integer',
        'events_per_day' => 'integer',
        'events_per_month' => 'integer',
        'notes_per_month' => 'integer',
        'total_notes' => 'integer',
        'total_lists' => 'integer',
        'items_in_list' => 'integer',
        'ai_tokens' => 'integer',
        'files_volume' => 'integer',
        'shared_space' => 'integer',
        'default' => 'integer',
        'autoprolong' => 'integer',
        'prolongation' => 'integer',
    ];

    /* Scopes */

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('status', 1);
    }

    #[Scope]
    protected function isInactive(Builder $query)
    {
        return $query->where('status', 0);
    }

    #[Scope]
    protected function isDefault(Builder $query)
    {
        return $query->where('default', 1);
    }

    #[Scope]
    protected function isAutoprolong(Builder $query)
    {
        return $query->where('autoprolong', 1);
    }

    #[Scope]
    protected function isProlongationActive(Builder $query)
    {
        return $query->where('prolongation', 1);
    }

    #[Scope]
    protected function whereCostGreaterThan(Builder $query, float $cost)
    {
        return $query->where('cost', '>=', $cost);
    }

    #[Scope]
    protected function whereCostLessThan(Builder $query, float $cost)
    {
        return $query->where('cost', '<=', $cost);
    }

    /* Relations */

    public function userTariffs(): HasMany
    {
        return $this->hasMany(UserTariff::class, 'tariff_id');
    }
}
