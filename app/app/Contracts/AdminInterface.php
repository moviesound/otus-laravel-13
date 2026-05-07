<?php

namespace App\Contracts;

use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;

interface AdminInterface
{
    public function getList(AdminSearchDTO $object);

    public function getRow(int $id);

    public function updateRow(AdminUpdateDTO $object);

    public function deleteRow(int $id);

    public function storeRow(AdminStoreDTO $object);

    public function resetPassword(int $id);
}
