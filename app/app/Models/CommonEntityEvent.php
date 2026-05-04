<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'entity_id', 'child_id',
])]
class CommonEntityEvent extends Model
{
    public $timestamps = false;

    protected $table = 'common_entity_event';

    public function entity(): BelongsTo
    {
        return $this->belongsTo(CommonEntity::class, 'entity_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'child_id');
    }
}
