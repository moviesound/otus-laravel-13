<?php

namespace App\Repositories;

use App\Models\Bot\SysText;
use App\Objects\SysTextSearchObject;
use App\Objects\SysTextStoreObject;
use App\Objects\SysTextUpdateObject;
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

    static public function getListWithPagination(SysTextSearchObject $object): AbstractPaginator
    {
        return self::getAllOrByAliasQuery($object->alias())
            ->paginate($object->perPage())
            ->onEachSide(2);
    }

    static public function getRow(int $id)
    {
        return SysText::find($id);
    }

    static public function updateRow(SysTextUpdateObject $object): bool
    {
        $text = SysText::find($object->id());

        if (!$text) {
            return false;
        }

        $text->update([
            'alias' => $object->alias(),
            'context' => $object->context(),
        ]);

        return true;
    }

    static public function storeRow(SysTextStoreObject $object): bool
    {
        $row = SysText::query()->byAlias($object->alias())->byLang($object->lang())->first();

        if (isset($row->context, $row->alias)) {
            return false;
        }

        SysText::query()->create([
            'lang' => $object->lang(),
            'alias' => $object->alias(),
            'context' => $object->context()
        ]);

        return true;
    }

    static public function deleteRow(int $id): bool
    {
        $text = SysText::find($id);

        if (!$text) {
            return false;
        }

        $text->delete();

        return true;
    }
}
