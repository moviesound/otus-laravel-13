<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'user_id', 'balance', 'currency', 'next_morning_digest',
    'next_evening_digest'
])]
class UserState extends Model
{
    use HasFactory;
    protected $table = 'user_states';

    public $timestamps = false;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $casts = [
        'user_id' => 'integer',
        'balance' => 'decimal:4',
        'next_morning_digest' => 'datetime',
        'next_evening_digest' => 'datetime',
    ];

    /* Scopes */

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function whereNextMorningDigestAfter(Builder $query, mixed $date)
    {
        return $query->where('next_morning_digest', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereNextMorningDigestBefore(Builder $query, mixed $date)
    {
        return $query->where('next_morning_digest', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereNextMorningDigestBetween(Builder $query, mixed $from, mixed $to)
    {
        return $query->whereBetween('next_morning_digest', [
            CustomHelper::normalizeDate($from),
            CustomHelper::normalizeDate($to)
        ]);
    }

    #[Scope]
    protected function whereNextEveningDigestAfter(Builder $query, mixed $date)
    {
        return $query->where('next_evening_digest', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereNextEveningDigestBefore(Builder $query, mixed $date)
    {
        return $query->where('next_evening_digest', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereNextEveningDigestBetween(Builder $query, mixed $from, mixed $to)
    {
        return $query->whereBetween('next_evening_digest', [
            CustomHelper::normalizeDate($from),
            CustomHelper::normalizeDate($to)
        ]);
    }


    /* Helpers */

    public function increaseBalance(float $amount): void
    {
        $this->balance += $amount;
        $this->save();
    }

    public function decreaseBalance(float $amount): void
    {
        $this->balance -= $amount;
        $this->save();
    }

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
