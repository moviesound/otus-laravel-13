<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable([
    'user_id', 'type', 'social_id',
    'is_main', 'keyboard', 'current_folder_s3',
])]
class UserSocial extends Model
{
    use HasFactory;
    protected $table = 'user_socials';

    protected $casts = [
        'user_id' => 'integer',
        'is_main' => 'integer',
        'keyboard' => 'integer',
        'current_folder_s3' => 'integer',
    ];

    /* Scopes  */

    #[Scope]
    protected function byUserId(Builder $query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    #[Scope]
    protected function byType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    #[Scope]
    protected function bySocialId(Builder $query, string|int $id)
    {
        return $query->where('social_id', $id);
    }

    #[Scope]
    protected function isMain(Builder $query)
    {
        return $query->where('is_main', 1);
    }

    /* Relations */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
