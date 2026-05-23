<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AdminInterface;
use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Http\Controllers\Admin\Requests\AdminSearchRequest;
use App\Http\Controllers\Admin\Requests\AdminStoreRequest;
use App\Http\Controllers\Admin\Requests\AdminUpdateRequest;
use App\Http\Controllers\Controller;
use App\Models\Admin\Role;

class AdminController extends Controller
{
    public function __construct(
        private AdminInterface $adminService
    ) {
    }

    public function index(AdminSearchRequest $request)
    {
        $dto = $request->toDTO();

        $admins = $this->adminService->getList($dto)
            ->appends($request->query());

        return view('users.index', compact('admins'));
    }

    public function create()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'ok',
            'html' => view('users.partials.create', compact('roles'))->render()
        ]);
    }

    public function store(AdminStoreRequest $request)
    {
        $dto = $request->toDTO();

        $admin = $this->adminService->storeRow($dto);

        return response()->json([
            'status' => 'ok',
            'message' => 'Администратор успешно создан'
        ])->header('X-ITEM-ID', $admin->id);
    }

    public function edit(int $id)
    {
        $admin = $this->adminService->getRow($id);

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'
            ], 404);
        }

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'ok',
            'html' => view(
                'users.partials.edit',
                compact('admin', 'roles')
            )->render()
        ]);
    }

    public function update(AdminUpdateRequest $request)
    {
        $dto = $request->toDTO();

        $this->adminService->updateRow($dto);

        return response()->json([
            'status' => 'ok',
            'message' => 'Пользователь успешно обновлён'
        ])->header('X-ITEM-ID', $dto->id);
    }

    public function resetPassword(int $id)
    {
        $password = $this->adminService->resetPassword($id);

        return response()->json([
            'status' => 'ok',
            'message' => "Новый пароль: {$password}"
        ])->header('X-ITEM-ID', $id);
    }

    public function destroy(int $id)
    {
        $this->adminService->deleteRow($id);

        return response()->json([
            'status' => 'ok',
            'message' => 'Пользователь удалён'
        ])->header('X-ITEM-ID', $id);
    }
}

