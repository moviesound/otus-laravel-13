<?php

namespace App\Services;

use App\Contracts\AdminInterface;
use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Repositories\AdminRepository;
use Illuminate\Pagination\AbstractPaginator;

class AdminService implements AdminInterface
{
    public function getList(AdminSearchDTO $object): AbstractPaginator
    {
        return AdminRepository::getListWithPagination($object);
    }

    public function getRow(int $id)
    {
        return AdminRepository::getRow($id);
    }

    public function updateRow(AdminUpdateDTO $object)
    {
        return AdminRepository::updateRow($object);
    }

    public function deleteRow(int $id): bool
    {
        return AdminRepository::deleteRow($id);
    }

    public function storeRow(AdminStoreDTO $object)
    {
        return AdminRepository::storeRow($object);
    }

    public function resetPassword(int $id)
    {
        return AdminRepository::resetPassword($id);
    }
}
