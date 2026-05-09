<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AdminInterface;
use App\DTO\AdminSearchDTO;
use App\DTO\AdminStoreDTO;
use App\DTO\AdminUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Requests\AdminSearchRequest;
use App\Http\Controllers\Admin\Requests\AdminStoreRequest;
use App\Http\Controllers\Admin\Requests\AdminUpdateRequest;
use App\Models\Permissions\Role;

class AdminController extends Controller
{
    public function __construct(
        private AdminInterface $adminService
    ) {
    }

    public function index(AdminSearchRequest $request)
    {
        $data = $request->toDTOArray();

        $dto = new AdminSearchDTO(
            name: $data['name'],
            email: $data['email'],
            perPage: $data['perPage'],
        );

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
        $data = $request->toDTOArray();

        $dto = new AdminStoreDTO(
            name: $data['name'],
            email: $data['email'],
            roles: $data['roles'],
        );

        $isSuccess = $this->adminService->storeRow($dto);

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь уже существует'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Администратор успешно создан'
        ]);
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

    public function update(AdminUpdateRequest $request, int $id)
    {
        $data = $request->toDTOArray();

        $dto = new AdminUpdateDTO(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            roles: $data['roles'],
        );

        $isSuccess = $this->adminService->updateRow($dto);

        if ($isSuccess === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Пользователь успешно обновлён'
        ]);
    }

    public function resetPassword(int $id)
    {
        $password = $this->adminService->resetPassword($id);

        if ($password === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => "Новый пароль: {$password}"
        ]);
    }

    public function destroy(int $id)
    {
        $isDeleted = $this->adminService->deleteRow($id);

        if ($isDeleted === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Пользователь не найден'
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Пользователь удалён'
        ]);
    }
}

