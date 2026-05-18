<?php

namespace App\Repositories;

use App\Exceptions\SysTextNotFoundException;
use App\Models\Bot\SysText;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\DTO\SysTextSearchDTO;
use App\Exceptions\DuplicateSysTextException;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Database\Eloquent\Builder;

class SysTextRepository
{
    /**
     * Base Query. Private method
     * This Query is not allowed to be done without limits
     *
     * @param string|null $alias
     * @return Builder
     */
    static private function getAllOrByAliasQuery(?string $alias = null): Builder
    {
        return SysText::query()
            ->when($alias, fn ($q) => $q->byAlias($alias))
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    static public function getListWithPagination(SysTextSearchDTO $object): AbstractPaginator
    {
        return self::getAllOrByAliasQuery($object->alias)
            ->paginate($object->perPage)
            ->onEachSide(2);
    }

    static public function getRow(int $id)
    {
        return SysText::find($id);
    }

    public static function updateRow(SysTextUpdateDTO $object): SysText
    {
        $text = SysText::find($object->id);

        if (!$text) {
            throw new SysTextNotFoundException();
        }

        $text->update([
            'alias' => $object->alias,
            'context' => $object->context,
        ]);

        return $text->fresh();
    }

    static public function storeRow(SysTextStoreDTO $object): SysText
    {
        $exists = SysText::query()
            ->byAlias($object->alias)
            ->byLang($object->lang)
            ->exists();

        if ($exists) {
            throw new DuplicateSysTextException($object->alias, $object->lang);
        }

        return SysText::query()->create([
            'lang' => $object->lang,
            'alias' => $object->alias,
            'context' => $object->context,
        ]);
    }

    public static function deleteRow(int $id): void
    {
        $text = SysText::find($id);

        if (!$text) {
            throw new SysTextNotFoundException();
        }

        $text->delete();
    }
}
