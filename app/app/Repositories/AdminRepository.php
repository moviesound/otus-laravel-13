<?php

namespace App\Repositories;

use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Models\Admin\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminRepository
{
    /**
     * Base Query
     */
    private static function getAllQuery(
        ?string $name = null,
        ?string $email = null
    ): Builder {
        return Admin::query()
            ->with('roles')
            ->when(
                $name,
                fn ($q) => $q->where('name', 'like', "%{$name}%")
            )
            ->when(
                $email,
                fn ($q) => $q->where('email', 'like', "%{$email}%")
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public static function getListWithPagination(
        AdminSearchDTO $object
    ): AbstractPaginator {
        return self::getAllQuery(
            $object->name,
            $object->email
        )
            ->paginate($object->perPage)
            ->onEachSide(2);
    }

    public static function getRow(int $id)
    {
        return Admin::query()
            ->with('roles')
            ->find($id);
    }

    public static function storeRow(AdminStoreDTO $object): bool
    {
        $exists = Admin::query()
            ->where('email', $object->email)
            ->exists();

        if ($exists) {
            return false;
        }

        $password = Str::random(12);

        $admin = Admin::query()->create([
            'name' => $object->name,
            'email' => $object->email,
            'password' => Hash::make($password),
        ]);

        $admin->syncRoles($object->roles);

        return true;
    }

    public static function updateRow(AdminUpdateDTO $object): bool
    {
        $admin = Admin::find($object->id);

        if (!$admin) {
            return false;
        }

        $emailExists = Admin::query()
            ->where('email', $object->email)
            ->where('id', '!=', $object->id)
            ->exists();

        if ($emailExists) {
            return false;
        }

        $admin->update([
            'name' => $object->name,
            'email' => $object->email,
        ]);

        $admin->syncRoles($object->roles);

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
            'password' => Hash::make($password),
        ]);

        return $password;
    }
}
