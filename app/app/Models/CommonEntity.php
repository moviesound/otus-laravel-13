<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommonEntity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'common_entities';

    /* Relations */

    public function tasks(): HasMany
    {
        return $this->hasMany(CommonEntityTask::class, 'entity_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommonEntityEvent::class, 'entity_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(CommonEntityReminder::class, 'entity_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class, 'common_entity_id');
    }

    public function relationType(string $type): HasMany
    {
        return match ($type) {
            'task' => $this->tasks(),
            'event' => $this->events(),
            'reminder' => $this->reminders(),
            default => throw new \InvalidArgumentException("Unknown type: {$type}")
        };
    }

    /* Helpers */

    public function attach(string $type, int $childId): void
    {
        $this->relationType($type)
            ->firstOrCreate([
                'child_id' => $childId,
            ]);
    }

    public function detach(string $type, int $childId): void
    {
        $this->relationType($type)
            ->where('child_id', $childId)
            ->delete();
    }
}
