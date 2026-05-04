<?php

namespace App\Services;

use App\Contracts\SysTextInterface;
use App\Objects\SysTextSearchObject;
use App\Objects\SysTextStoreObject;
use App\Objects\SysTextUpdateObject;
use App\Repositories\SysTextRepository;
use Illuminate\Pagination\AbstractPaginator;

class SysTextService implements SysTextInterface
{
    public function getList(SysTextSearchObject $object): AbstractPaginator
    {
        return SysTextRepository::getListWithPagination($object);
    }

    public function getRow(int $id)
    {
        return SysTextRepository::getRow($id);
    }

    public function updateRow(SysTextUpdateObject $object)
    {
        return SysTextRepository::updateRow($object);
    }

    public function deleteRow(int $id): bool
    {
        return SysTextRepository::deleteRow($id);
    }

    public function storeRow(SysTextStoreObject $object)
    {
        return SysTextRepository::storeRow($object);
    }
}
