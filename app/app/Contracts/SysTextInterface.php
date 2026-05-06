<?php

namespace App\Contracts;

use App\DTO\SysTextSearchDTO;
use App\DTO\SysTextUpdateDTO;
use App\DTO\SysTextStoreDTO;

interface SysTextInterface
{
    public function getList(SysTextSearchDTO $object);

    public function getRow(int $id);

    public function updateRow(SysTextUpdateDTO $object);

    public function deleteRow(int $id);

    public function storeRow(SysTextStoreDTO $object);
}
