<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'entity_id',
    'entity_type',
    'reminder_id',
    'user_id',
    'channel',
    'status',
    'sent_times',
    'last_sent_at',
    'date_remind',
    'process_name',
    'locked_by',
    'locked_at',
    'created_at',
])]
class ReminderQueue extends Model
{
    use HasFactory;

    protected $table = 'reminder_queues';

    public $timestamps = true;

    protected $casts = [
        'last_sent_at' => 'datetime',
        'date_remind'  => 'datetime',
        'locked_at'    => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',

        'sent_times'   => 'integer',
    ];

    /* Relations */

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class, 'reminder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* Scopes */

    #[Scope]
    protected function byStatus(Builder $query, ?string $status): Builder
    {
        return $status
            ? $query->where('status', $status)
            : $query;
    }

    #[Scope]
    protected function isPending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    #[Scope]
    protected function isProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    #[Scope]
    protected function isFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    #[Scope]
    protected function byChannel(Builder $query, ?string $channel): Builder
    {
        return $channel
            ? $query->where('channel', $channel)
            : $query;
    }

    #[Scope]
    protected function byUserId(Builder $query, ?int $userId): Builder
    {
        return $userId
            ? $query->where('user_id', $userId)
            : $query;
    }


    /* Actions */

    public function markProcessing(?string $worker = null): void
    {
        $this->update([
            'status'     => 'processing',
            'locked_by'  => $worker,
            'locked_at'  => now(),
        ]);
    }

    public function markDone(): void
    {
        $this->update([
            'status' => 'done',
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    public function markFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    public function setSent(): void
    {
        $this->update([
            'last_sent_at' => now(),
            'sent_times' => $this->sent_times + 1,
        ]);
    }
}
