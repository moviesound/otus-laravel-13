<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_template_id', 'tag_id', 'created_at',
])]
class EventTag extends Model
{
    protected $table = 'event_tag';

    public $timestamps = false;

    protected $casts = [
        'event_template_id' => 'integer',
        'tag_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /* Scopes */

    #[Scope]
    protected function byEventTplId(Builder $query, int $eventId)
    {
        return $query->where('event_template_id', $eventId);
    }

    #[Scope]
    protected function byTagId(Builder $query, int $tagId)
    {
        return $query->where('tag_id', $tagId);
    }

    /* Relations  */

    public function eventTemplate(): BelongsTo
    {
        return $this->belongsTo(EventTemplate::class, 'event_template_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}
