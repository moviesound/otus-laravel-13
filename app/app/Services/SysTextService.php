<?php

namespace App\Services;

use App\Contracts\SysTextInterface;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\DTO\SysTextSearchDTO;
use App\Repositories\SysTextRepository;
use Illuminate\Pagination\AbstractPaginator;

class SysTextService implements SysTextInterface
{
    public function getList(SysTextSearchDTO $object): AbstractPaginator
    {
        return SysTextRepository::getListWithPagination($object);
    }

    public function getRow(int $id)
    {
        return SysTextRepository::getRow($id);
    }

    public function updateRow(SysTextUpdateDTO $object)
    {
        return SysTextRepository::updateRow($object);
    }

    public function deleteRow(int $id): bool
    {
        return SysTextRepository::deleteRow($id);
    }

    public function storeRow(SysTextStoreDTO $object)
    {
        return SysTextRepository::storeRow($object);
    }
}
