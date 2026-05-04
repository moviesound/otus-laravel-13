<?php

namespace App\Models;

use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'template_id',
    'date_remind',
    'status',
])]
class Reminder extends Model
{
    use HasFactory;

    protected $casts = [
        'date_remind' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /* Relations */

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReminderTemplate::class, 'template_id');
    }

    public function commonEntityReminders(): HasMany
    {
        return $this->hasMany(CommonEntityReminder::class, 'child_id');
    }

    public function reminderQueues(): HasMany
    {
        return $this->hasMany(ReminderQueue::class, 'reminder_id');
    }

    /* Scopes */

    public function byTplId(Builder $query, int $templateId): Builder
    {
        return $query->where('template_id', $templateId);
    }

    public function byStatus(Builder $query, ?string $status): Builder
    {
        return $status
            ? $query->where('status', $status)
            : $query;
    }

    public function whereDateRemindAfter(Builder $query, mixed $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('date_remind', '>=', CustomHelper::normalizeDate($date));
    }

    public function whereDateRemindBefore(Builder $query, mixed $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('date_remind', '<=', CustomHelper::normalizeDate($date));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereNotNull('date_remind')
            ->where('date_remind', '>=', now());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotNull('date_remind')
            ->where('date_remind', '<', now())
            ->where('status', '!=', 'done');
    }

}
