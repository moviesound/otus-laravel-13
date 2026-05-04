<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'tag', 'user_id', 'count', 'created_at',
])]
class Tag extends Model
{
    use HasFactory;
    protected $table = 'tags';
    public $timestamps = false;//нет updated_at

    protected $casts = [
        'user_id' => 'integer',
        'count' => 'integer',
        'created_at' => 'datetime',
    ];

    /* relations */
    public function taskTemplates(): BelongsToMany
    {
        return $this->belongsToMany(
            TaskTemplate::class,
            'tag_task',
            'tag_id',
            'task_template_id'
        );
    }

    public function eventTemplates(): BelongsToMany
    {
        return $this->belongsToMany(
            EventTemplate::class,
            'event_tag',
            'tag_id',
            'event_template_id'
        );
    }

    /* scopes */

    #[Scope]
    protected function searchByTag(Builder $query, string $text)
    {
        return $query->whereRaw(
            "MATCH(tag) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$text]
        );
    }

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function isPopular(Builder $query)
    {
        return $query->orderByDesc('count');
    }

    /* helpers */

    public function increaseCount(): void
    {
        $this->increment('count');
    }

    public function decreaseCount(): void
    {
        if ($this->count > 0) {
            $this->decrement('count');
        }
    }
}
