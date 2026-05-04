<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use App\Support\CustomHelper;

#[Fillable([
    'chat_id',
    'message_id',
    'message',
    'query',
    'is_temporary',
    'created_at',
    'queue_id',
])]
class TelegramSysMessage extends Model
{
    protected $table = 'telegram_sys_messages';

    public $timestamps = false;

    protected $casts = [
        'is_temporary' => 'boolean',
        'created_at' => 'datetime',
        'queue_id' => 'integer',
    ];

    /* Scopes */

    #[Scope]
    protected function byChatId(Builder $query, string $chatId): Builder
    {
        return $query->where('chat_id', $chatId);
    }

    #[Scope]
    protected function byMessageId(Builder $query, string $messageId): Builder
    {
        return $query->where('message_id', $messageId);
    }

    #[Scope]
    protected function byQueueId(Builder $query, ?int $queueId): Builder
    {
        return $query->where('queue_id', $queueId);
    }

    #[Scope]
    protected function isTemporary(Builder $query): Builder
    {
        return $query->where('is_temporary', true);
    }

    #[Scope]
    protected function isNotTemporary(Builder $query): Builder
    {
        return $query->where('is_temporary', false);
    }

    #[Scope]
    protected function createdAfter(Builder $query, mixed $date): Builder
    {
        return $query->where(
            'created_at',
            '>=',
            CustomHelper::normalizeDate($date)
        );
    }

    #[Scope]
    protected function createdBefore(Builder $query, mixed $date): Builder
    {
        return $query->where(
            'created_at',
            '<=',
            CustomHelper::normalizeDate($date)
        );
    }
}
