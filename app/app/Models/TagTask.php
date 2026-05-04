<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_template_id', 'tag_id', 'created_at',
])]
class TagTask extends Model
{
    protected $table = 'tag_task';

    public $timestamps = false;

    protected $casts = [
        'task_template_id' => 'integer',
        'tag_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /* Scopes */

    #[Scope]
    protected function byTaskTplId(Builder $query, int $taskId)
    {
        return $query->where('task_template_id', $taskId);
    }

    #[Scope]
    protected function byTagId(Builder $query, int $tagId)
    {
        return $query->where('tag_id', $tagId);
    }

    /* Relations */
    public function taskTemplate(): BelongsTo
    {
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
