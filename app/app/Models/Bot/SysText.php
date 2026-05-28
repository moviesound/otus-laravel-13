<?php

namespace App\Models\Bot;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Как проверить работу кэша в классе через консоль?
 * Проверяется после накатывания миграций, иначе база пустая
 *
 * 1. Заходим в tinker:
 * php artisan tinker
 *
 * 2. Выполняем там запрос в БД через эту модель:
 * App\Models\SysText::get('free', 'ru');
 *
 * 3. Проверяем кэш:
 * Cache::tags(['sys_text'])->get('sys_text:ru:free');
 *
 */
#[Fillable([
    'alias',
    'lang',
    'context',
])]
class SysText extends Model
{
    use HasFactory;
    protected $table = 'sys_texts';

    const TAG = 'sys_text';

    public $timestamps = true;

    protected $casts = [
        'alias' => 'string',
        'lang' => 'string',
        'context' => 'string',
    ];

    /* Scopes */

    #[Scope]
    protected function byAlias(Builder $query, string $alias)
    {
        if (str_starts_with($alias, '%') || str_ends_with($alias, '%')) {
            return $query->where('alias', 'like', $alias);
        }

        return $query->where('alias', $alias);
    }

    #[Scope]
    protected function byLang(Builder $query, string $lang)
    {
        return $query->where('lang', $lang);
    }

    #[Scope]
    protected function whereAliasLike(Builder $query, string $alias)
    {
        return $query->where('alias', 'like', "%{$alias}%");
    }

    #[Scope]
    protected function whereContextLike(Builder $query, string $value)
    {
        return $query->where('context', 'like', $value);
    }
}
