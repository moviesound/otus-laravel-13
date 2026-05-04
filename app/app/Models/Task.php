<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Support\CustomHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'template_id',
    'status',
    'period_start',
    'period_end',
    'deadline',
    'check_remind_next_time',
    'next_system_remind_at',
    'last_shown_in_digest_at',
])]
class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    public $timestamps = false; // есть только created_at

    protected $casts = [
        'template_id' => 'integer',
        'status' => 'string',

        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'deadline' => 'datetime',

        'check_remind_next_time' => 'datetime',
        'next_system_remind_at' => 'datetime',
        'last_shown_in_digest_at' => 'datetime',

        'created_at' => 'datetime',
    ];

    /* Relations  */

    public function template(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'template_id');
    }

    /* Scopes */

    #[Scope]
    protected function byTaskTplId(Builder $query, int $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    #[Scope]
    protected function byStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    #[Scope]
    protected function isDone(Builder $query)
    {
        return $query->where('status', 'done');
    }

    #[Scope]
    protected function isOverdue(Builder $query)
    {
        return $query->where('status', 'overdue');
    }

    #[Scope]
    protected function isCanceled(Builder $query)
    {
        return $query->where('status', 'canceled');
    }

    #[Scope]
    protected function whereDeadlineAfter(Builder $query, mixed $date)
    {
        return $query->where('deadline', '>=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereDeadlineBefore(Builder $query, mixed $date)
    {
        return $query->where('deadline', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereDeadlineBetween(Builder $query, mixed $from, mixed $to) {
        return $query->whereBetween('deadline', [
            CustomHelper::normalizeDate($from),
            CustomHelper::normalizeDate($to)
        ]);
    }

    #[Scope]
    protected function wherePeriodOverlap(Builder $query, mixed $from, mixed $to) {
        return $query->where(function ($q) use ($from, $to) {
            $q->where('period_start', '<=', CustomHelper::normalizeDate($to))
                ->where('period_end', '>=', CustomHelper::normalizeDate($from));
        });
    }

    #[Scope]
    protected function whereNextRemindBefore(Builder $query, mixed $date)
    {
        return $query->where('next_system_remind_at', '<=', CustomHelper::normalizeDate($date));
    }

    #[Scope]
    protected function whereNextRemindAfter(Builder $query, mixed $date)
    {
        return $query->where('next_system_remind_at', '>=', CustomHelper::normalizeDate($date));
    }


    /* Helpers */

    public function getDone(): bool
    {
        return $this->status === 'done';
    }

    public function getOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function getActive(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function getCanceled(): bool
    {
        return $this->status === 'canceled';
    }
}
