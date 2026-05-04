<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'user_id',
    'title',
    'description',
    'repeat_type',
    'repeat_interval',
    'week_days',
    'weekly_common_time',
    'weekly_different_time',
    'month_days',
    'monthly_common_time',
    'monthly_different_time',
    'quarter_type',
    'month_in_quarter',
    'day_in_quarter',
    'start_month_in_quarter',
    'start_day_in_quarter',
    'end_month_in_quarter',
    'end_day_in_quarter',
    'year_type',
    'month_in_year',
    'day_in_year',
    'start_month_in_year',
    'start_day_in_year',
    'end_month_in_year',
    'end_day_in_year',
    'month_start',
    'day_start',
    'hour_start',
    'minute_start',
    'month_end',
    'day_end',
    'hour_end',
    'minute_end',
    'time_set_by_user',
    'event_type',
    'status',
    'has_call',
    'has_sms',
])]
class EventTemplate extends Model
{
    use HasFactory;

    protected $table = 'event_templates';

    protected $casts = [
        'user_id' => 'integer',
        'repeat_interval' => 'integer',

        'month_start' => 'integer',
        'day_start' => 'integer',
        'hour_start' => 'integer',
        'minute_start' => 'integer',

        'month_end' => 'integer',
        'day_end' => 'integer',
        'hour_end' => 'integer',
        'minute_end' => 'integer',

        'time_set_by_user' => 'integer',
        'status' => 'integer',
        'has_call' => 'integer',
        'has_sms' => 'integer',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'template_id');
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(ReminderTemplate::class, 'entity');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'event_tag',
            'event_template_id',
            'tag_id'
        );
    }

    /* Scopes  */

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function isActive(Builder $query)
    {
        return $query->where('status', 1);
    }

    #[Scope]
    protected function isRepeated(Builder $query)
    {
        return $query->where('repeat_type', '!=', 'none');
    }

    #[Scope]
    protected function byStatus(Builder $query, int $status)
    {
        return $query->where('status', $status);
    }

    #[Scope]
    protected function byEventType(Builder $query, string $type)
    {
        return $query->where('event_type', $type);
    }

    #[Scope]
    protected function withCall(Builder $query)
    {
        return $query->where('has_call', 1);
    }

    #[Scope]
    protected function withSms(Builder $query)
    {
        return $query->where('has_sms', 1);
    }

    #[Scope]
    protected function search(Builder $query, string $text)
    {
        return $query->whereRaw(
            "MATCH(title, description) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$text]
        );
    }


    /* Helpers */

    public function getRepeated(): bool
    {
        return $this->repeat_type !== 'none';
    }
}
