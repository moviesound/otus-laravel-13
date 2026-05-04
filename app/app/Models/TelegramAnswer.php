<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\CustomHelper;

#[Fillable([
    'user_id', 'chat_id', 'created_at',
    'message', 'message_id', 'debug',
    'type', 'status', 'is_temporary',
    'locked_by', 'locked_at',
])]
class TelegramAnswer extends Model
{
    protected $table = 'telegram_answers';

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'locked_at' => 'datetime',
        'status' => 'integer',
        'is_temporary' => 'boolean',
    ];

    /* Scopes */

    #[Scope]
    protected function byUserId(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function byChatId(Builder $query, string $chatId): Builder
    {
        return $query->where('chat_id', $chatId);
    }

    #[Scope]
    protected function byType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    #[Scope]
    protected function byStatus(Builder $query, int $status): Builder
    {
        return $query->where('status', $status);
    }

    #[Scope]
    protected function isTemporary(Builder $query): Builder
    {
        return $query->where('is_temporary', 1);
    }

    #[Scope]
    protected function isNotTemporary(Builder $query): Builder
    {
        return $query->where('is_temporary', 0);
    }

    #[Scope]
    protected function whereLockedAfter(Builder $query, mixed $date): Builder
    {
        return $query->whereNotNull('locked_at')
            ->where('locked_at', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereLockedBefore(Builder $query, mixed $date): Builder
    {
        return $query->whereNotNull('locked_at')
            ->where('locked_at', '<=', CustomHelper::normalizeDate($date));
    }

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
