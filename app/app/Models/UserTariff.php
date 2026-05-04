<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'tariff_id',
    'status',
    'date_start',
    'date_end',
    'auto_prolong',
])]
class UserTariff extends Model
{
    protected $table = 'user_tariffs';

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'integer',
        'tariff_id' => 'integer',
        'status' => 'integer',

        'date_start' => 'datetime',
        'date_end' => 'datetime',

        'auto_prolong' => 'integer',
    ];

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class, 'tariff_id');
    }

    /* Scopes */

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function byTariffId(Builder $query, int $tariffId)
    {
        return $query->where('tariff_id', $tariffId);
    }

    #[Scope]
    protected function byStatus(Builder $query, int $status)
    {
        return $query->where('status', $status);
    }

    #[Scope]
    protected function isExpired(Builder $query)
    {
        return $query->where('date_end', '<', now());
    }

    #[Scope]
    protected function whereStartAfter(Builder $query, mixed $date)
    {
        return $query->where('date_start', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereStartBefore(Builder $query, mixed $date)
    {
        return $query->where('date_start', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereEndAfter(Builder $query, mixed $date)
    {
        return $query->where('date_end', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereEndBefore(Builder $query, mixed $date)
    {
        return $query->where('date_end', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function isCurrentTariff(Builder $query)
    {
        return $query->where('date_start', '<=', now())
            ->where('date_end', '>=', now());
    }

}
