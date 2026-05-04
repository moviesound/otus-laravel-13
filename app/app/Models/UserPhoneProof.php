<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'phone', 'code', 'status',
    'times', 'call_id', 'campaign_id',
    'cost', 'sender', 'call_status',
])]
class UserPhoneProof extends Model
{
    protected $table = 'user_phone_proofs';

    public $timestamps = true;

    protected $casts = [
        'user_id' => 'integer',
        'times' => 'integer',
        'cost' => 'decimal:6',
    ];

    /* Scopes */

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function byPhone(Builder $query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    #[Scope]
    protected function isStatusSent(Builder $query)
    {
        return $query->where('status', 'sent');
    }

    #[Scope]
    protected function isStatusSuccess(Builder $query)
    {
        return $query->where('status', 'success');
    }

    #[Scope]
    protected function isStatusFailed(Builder $query)
    {
        return $query->where('status', 'failed');
    }

    #[Scope]
    protected function isStatusWrongCode(Builder $query)
    {
        return $query->where('status', 'wrong-code');
    }

    #[Scope]
    protected function isStatusSuccessCode(Builder $query)
    {
        return $query->where('status', 'success-code');
    }

    #[Scope]
    protected function byCode(Builder $query, string $code)
    {
        return $query->where('code', $code);
    }

    #[Scope]
    protected function whereTimesGreaterThan(Builder $query, int $value)
    {
        return $query->where('times', '>', $value);
    }

    #[Scope]
    protected function whereTimesLessThan(Builder $query, int $value)
    {
        return $query->where('times', '<', $value);
    }

    #[Scope]
    protected function whereCreatedAfter(Builder $query, mixed $date)
    {
        return $query->where('created_at', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereCreatedBefore(Builder $query, mixed $date)
    {
        return $query->where('created_at', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereCreatedBetween(Builder $query, mixed $from, mixed $to)
    {
        return $query->whereBetween('created_at', [
            CustomHelper::normalizeDate($from),
                CustomHelper::normalizeDate($to)]);
    }

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
