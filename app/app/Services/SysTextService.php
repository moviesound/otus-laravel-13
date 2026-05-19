<?php

namespace App\Services;

use App\Contracts\SysTextInterface;
use App\DTO\SysTextStoreDTO;
use App\DTO\SysTextUpdateDTO;
use App\DTO\SysTextSearchDTO;
use App\Models\Bot\SysText;
use App\Repositories\SysTextRepository;
use Illuminate\Pagination\AbstractPaginator;

class SysTextService implements SysTextInterface
{
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
        return SysTextRepository::updateRow($object);
    }

    public function deleteRow(int $id): void
    {
        SysTextRepository::deleteRow($id);
    }

    public function storeRow(SysTextStoreDTO $object): SysText
    {
        return SysTextRepository::storeRow($object);
    }
}
