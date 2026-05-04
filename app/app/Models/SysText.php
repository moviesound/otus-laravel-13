<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
        return $query->where('alias', $alias);
    }

    #[Scope]
    protected function byLang(Builder $query, string $lang)
    {
        return $query->where('lang', $lang);
    }

    #[Scope]
    protected function whereAliasLike(Builder $query, string $value)
    {
        return $query->where('alias', 'like', $value);
    }

    #[Scope]
    protected function whereContextLike(Builder $query, string $value)
    {
        return $query->where('context', 'like', $value);
    }

    /* Static working method */

    /**
     * Get text by alias and language with caching.
     * Use $replace array, e.g. ['param1' => 'your text']
     * to change {#PARAM1#} in returned text to your value
     */
    public static function get(
        string $alias,
        string $lang = 'ru',
        array $replace = []
    ): string {
        $cacheKey = self::buildCacheKey($lang, $alias);

        $text = Cache::tags([self::TAG])->rememberForever(
            $cacheKey,
            function () use ($alias, $lang) {

                $text = static::query()
                    ->byAlias($alias)
                    ->byLang($lang)
                    ->value('context');

                // fallback на ru
                if (!$text && $lang !== 'ru') {
                    $text = static::query()
                        ->byAlias($alias)
                        ->byLang('ru')
                        ->value('context');
                }

                return $text ?? $alias;
            }
        );

        return static::replace($text, $replace);
    }

    /* Helpers */

    /**
     * Replace placeholders in text.
     */
    protected static function replace(string $text, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $text = str_replace(
                "{#".strtoupper($key)."#}",
                $value,
                $text
            );
        }

        return $text;
    }

    /**
     * Build cache key.
     */
    protected static function buildCacheKey(string $lang, string $alias): string
    {
        return self::TAG . ":{$lang}:{$alias}";
    }

    /* Cache */

    /**
     * Forget single cache entry.
     */
    public static function forget(string $alias, string $lang = 'ru'): void
    {
        Cache::tags([self::TAG])
            ->forget(self::buildCacheKey($lang, $alias));
    }

    /**
     * Flush all sys_texts cache.
     */
    public static function flushAll(): void
    {
        Cache::tags([self::TAG])->flush();
    }

    /* Auto cache invalidation */

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            $key = self::buildCacheKey($model->lang, $model->alias);

            $value = static::query()
                ->where('alias', $model->alias)
                ->where('lang', $model->lang)
                ->value('context');

            Cache::tags([self::TAG])
                ->put($key, $value);
        });

        static::deleted(function (self $model) {
            static::forget($model->alias, $model->lang);
        });
    }
}
