<?php

namespace App\Services;

use App\Contracts\AdminInterface;
use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Models\Admin\Admin;
use App\Repositories\AdminRepository;
use Illuminate\Pagination\AbstractPaginator;

class AdminService implements AdminInterface
{
    public function getList(AdminSearchDTO $object): AbstractPaginator
    {
        return AdminRepository::getListWithPagination($object);
    }

    public function getRow(int $id): Admin
    {
        return AdminRepository::getRow($id);
    }

    public function updateRow(AdminUpdateDTO $object): Admin
    {
        return AdminRepository::updateRow($object);
    }

    public function deleteRow(int $id): void
    {
        AdminRepository::deleteRow($id);
    }

    public function storeRow(AdminStoreDTO $object): Admin
    {
        return AdminRepository::storeRow($object);
    }

    public function resetPassword(int $id): string
    {
        return AdminRepository::resetPassword($id);
    }
}
