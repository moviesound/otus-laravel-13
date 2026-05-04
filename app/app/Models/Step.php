<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_socials_id',
    'socials_type',
    'scenario',
    'step',
    'message',
    'data',
    'additional_info',
    'common_entity_id',
    'updated_at',
])]
class Step extends Model
{
    protected $table = 'steps';
    protected $primaryKey = 'user_social_id';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false; // ❗ у тебя нет created_at

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /* Scopes */

    #[Scope]
    protected function byUserSocialId(Builder $query, int $userSocialId): Builder
    {
        return $query->where('user_social_id', $userSocialId);
    }

    #[Scope]
    public function byScenario(Builder $query, string $scenario): Builder
    {
        return $query->where('scenario', $scenario);
    }

    #[Scope]
    public function byStep(Builder $query, string $step): Builder
    {
        return $query->where('step', $step);
    }

    #[Scope]
    public function byCommonEntityId(Builder $query, ?int $entityId): Builder
    {
        return $entityId
            ? $query->where('common_entity_id', $entityId)
            : $query;
    }

    /* Relations */

    public function userSocial(): BelongsTo
    {
        return $this->belongsTo(UserSocial::class, 'user_social_id');
    }

    public function commonEntity(): BelongsTo
    {
        return $this->belongsTo(CommonEntity::class, 'common_entity_id')->withDefault();
    }
}
