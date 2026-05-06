<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id', 'text', 'remind_type', 'remind_value',
    'is_sub_task', 'entity_type', 'entity_id',
    'has_call', 'has_sms', 'created_at',
])]
class ReminderTemplate extends Model
{
    use HasFactory;

    protected $table = 'reminder_templates';

    protected $casts = [
        'created_at' => 'datetime',
        'is_sub_task' => 'integer',
        'has_call' => 'integer',
        'has_sms' => 'integer',
    ];

    /* Relations */

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'template_id');
    }

    /**
     * relation with TaskTemplate EventTemplate
     * @return MorphTo
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /* Scopes */

    #[Scope]
    public function byUserId(Builder $query, ?int $userId): Builder
    {
        return $userId
            ? $query->where('user_id', $userId)
            : $query;
    }

    #[Scope]
    public function byEntityId(Builder $query, string $type, ?int $id = null): Builder
    {
        return $query
            ->where('entity_type', $type)
            ->when($id, fn ($q) => $q->where('entity_id', $id));
    }

    #[Scope]
    public function searchText(Builder $query, string $text): Builder
    {
        return $query->whereFullText('text', $text);
    }

    #[Scope]
    public function isSubTask(Builder $query): Builder
    {
        return $query->where('is_sub_task', 1);
    }
}
