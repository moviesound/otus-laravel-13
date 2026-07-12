<?php

namespace App\Services;

use App\Contracts\SysTextInterface;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\DTO\SysTextSearchDTO;
use App\Exceptions\SysTextNotFoundException;
use App\Models\Bot\SysText;
use App\Repositories\SysTextRepository;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;

class SysTextService implements SysTextInterface
{
    private const TAG = 'sys_text';

    private function key(string $alias, string $lang): string
    {
        return "sys_text:$lang:$alias";
    }

    public function getList(SysTextSearchDTO $object): AbstractPaginator
    {
        return SysTextRepository::getListWithPagination($object);
    }

    public function getRow(int $id): ?SysText
    {
        return SysTextRepository::getRow($id);
    }

    public function updateRow(SysTextUpdateDTO $object): SysText
    {
        $row = SysTextRepository::updateRow($object);

        $this->putCache($row->alias, $row->lang, $row);

        return $row;
    }

    public function deleteRow(int $id): void
    {
        $row = SysTextRepository::getRow($id);

        if (!$row) {
            throw new SysTextNotFoundException();
        }

        SysTextRepository::deleteRow($id);

        Cache::tags([self::TAG])->forget(
            $this->key($row->alias, $row->lang)
        );
    }

    public function storeRow(SysTextStoreDTO $object): SysText
    {
        $row = SysTextRepository::storeRow($object);

        $this->putCache($row->alias, $row->lang, $row);

        return $row;
    }

    public function get(string $alias, string $lang = 'ru', array $replace = []): string
    {
        $cacheKey = $this->key($alias, $lang);

        $data = Cache::tags([self::TAG])->rememberForever(
            $cacheKey,
            function () use ($alias, $lang) {
                $row = SysTextRepository::getByAliasAndLang($alias, $lang);

                if (!$row && $lang !== 'ru') {
                    $row = SysTextRepository::getByAliasAndLang($alias, 'ru');
                }

                return Arr::only($row ?? [], [
                    'id',
                    'alias',
                    'lang',
                    'context'
                ]);
            }
        );

        $text = $data['context'] ?? $alias;

        return $this->replace($text, $replace);
    }

    private function replace(string $text, array $replace): string
    {
        if (!empty($replace)) {
            foreach ($replace as $key => $value) {
                $text = str_replace(
                    "{#" . strtoupper($key) . "#}",
                    $value,
                    $text
                );
            }
        }
        return $text;
    }

    private function putCache(string $alias, string $lang, array|SysText $row): array
    {
        $key = $this->key($alias, $lang);

        Cache::tags([self::TAG])->forget($key);

        $payload = $this->toCache($row);

        Cache::tags([self::TAG])->forever($key, $payload);

        return $payload;
    }

    private function toCache(array|SysText $row): array
    {
        if ($row instanceof SysText) {
            return [
                'id' => $row->id,
                'alias' => $row->alias,
                'lang' => $row->lang,
                'context' => $row->context,
            ];
        }

        return $row;
    }
}
