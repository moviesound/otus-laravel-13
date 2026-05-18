<?php

namespace App\Repositories;

use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Exceptions\AdminNotFoundException;
use App\Exceptions\DuplicateAdminException;
use App\Models\Admin\Admin;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class AdminRepository
{
    public static function getListWithPagination(
        AdminSearchDTO $dto
    ): LengthAwarePaginator {
        return Admin::with('roles')
            ->when(
                $dto->name,
                fn ($query) => $query->whereLike('name', "%{$dto->name}%")
            )
            ->when(
                $dto->email,
                fn ($query) => $query->whereLike('email', "%{$dto->email}%")
            )
            ->latest('updated_at')
            ->latest('id')
            ->paginate($dto->perPage)
            ->onEachSide(2);
    }

    public static function getRow(int $id): Admin
    {
        $admin = Admin::with('roles')->find($id);

        if (!$admin) {
            throw new AdminNotFoundException();
        }

        return $admin;
    }

    public static function storeRow(AdminStoreDTO $dto): Admin
    {
        $exists = Admin::where('email', $dto->email)->exists();

        if ($exists) {
            throw new DuplicateAdminException($dto->email);
        }

        $password = Str::random(12);

        $admin = Admin::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $password,
        ]);

        $admin->syncRoles($dto->roles);

        return $admin;
    }

    public static function updateRow(AdminUpdateDTO $dto): Admin
    {
        $admin = Admin::find($dto->id);

        if (!$admin) {
            throw new AdminNotFoundException();
        }

        $exists = Admin::where('email', $dto->email)
            ->whereKeyNot($dto->id)
            ->exists();

        if ($exists) {
            throw new DuplicateAdminException($dto->email);
        }

        $admin->update([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        $admin->syncRoles($dto->roles);

        return $admin->fresh();
    }

    public static function deleteRow(int $id): void
    {
        $admin = Admin::find($id);

        if (!$admin) {
            throw new AdminNotFoundException();
        }

        $admin->delete();
    }

    public static function resetPassword(int $id): string
    {
        $admin = Admin::find($id);

        if (!$admin) {
            throw new AdminNotFoundException();
        }

        $password = Str::random(12);

        $admin->update([
            'password' => $password,
        ]);

        return $password;
    }
}
