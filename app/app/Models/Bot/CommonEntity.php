<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommonEntity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'common_entities';

    /* Relations */

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(
            TaskTemplate::class,
            'common_entity_task',
            'entity_id',
            'child_id'
        );
    }

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            EventTemplate::class,
            'common_entity_event',
            'entity_id',
            'child_id'
        );
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class, 'common_entity_id');
    }

    public function relationType(string $type): BelongsToMany
    {
        return match ($type) {
            'task' => $this->tasks(),
            'event' => $this->events(),
            default => throw new \InvalidArgumentException("Unknown type: {$type}")
        };
    }

    /* Helpers */

    public function attach(string $type, int $childId): void
    {
        $this->relationType($type)
            ->attach($childId);
    }

    public function detach(string $type, int $childId): void
    {
        $this->relationType($type)
            ->detach($childId);
    }
}
