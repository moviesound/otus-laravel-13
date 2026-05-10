<?php

namespace App\Repositories;

use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
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

    public static function getRow(int $id): ?Admin
    {
        return Admin::with('roles')->find($id);
    }

    public static function storeRow(AdminStoreDTO $dto): bool
    {
        if (Admin::where('email', $dto->email)->exists()) {
            return false;
        }

        $password = Str::random(12);

        $admin = Admin::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $password,
        ]);

        $admin->syncRoles($dto->roles);

        return true;
    }

    public static function updateRow(AdminUpdateDTO $dto): bool
    {
        $admin = Admin::find($dto->id);

        if (!$admin) {
            return false;
        }

        $exists = Admin::where('email', $dto->email)
            ->whereKeyNot($dto->id)
            ->exists();

        if ($exists) {
            return false;
        }

        $admin->update([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        $admin->syncRoles($dto->roles);

        return true;
    }

    public static function deleteRow(int $id): bool
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return false;
        }

        $admin->delete();

        return true;
    }

    public static function resetPassword(int $id): string|bool
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return false;
        }

        $password = Str::random(12);

        $admin->update([
            'password' => $password,
        ]);

        return $password;
    }
}
