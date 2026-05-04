<?php

namespace App\Contracts;

use App\Objects\SysTextSearchObject;
use App\Objects\SysTextStoreObject;
use App\Objects\SysTextUpdateObject;

interface SysTextInterface
{
    public function getList(SysTextSearchObject $object);

    public function getRow(int $id);

    public function updateRow(SysTextUpdateObject $object);

    public function deleteRow(int $id);

    public function storeRow(SysTextStoreObject $object);
}
