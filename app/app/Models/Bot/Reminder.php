<?php

namespace App\Models\Bot;

use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function reminderQueues(): HasMany
    {
        return $this->hasMany(ReminderQueue::class, 'reminder_id');
    }

    /* Scopes */

    #[Scope]
    protected function byTplId(Builder $query, int $templateId): Builder
    {
        return $query->where('template_id', $templateId);
    }

    #[Scope]
    protected function byStatus(Builder $query, ?string $status): Builder
    {
        return $status
            ? $query->where('status', $status)
            : $query;
    }

    #[Scope]
    protected function whereDateRemindAfter(Builder $query, mixed $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('date_remind', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereDateRemindBefore(Builder $query, mixed $date): Builder
    {
        if (!$date) {
            return $query;
        }

        return $query->where('date_remind', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereNotNull('date_remind')
            ->where('date_remind', '>=', now());
    }

    #[Scope]
    protected function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotNull('date_remind')
            ->where('date_remind', '<', now())
            ->where('status', '!=', 'done');
    }

}
