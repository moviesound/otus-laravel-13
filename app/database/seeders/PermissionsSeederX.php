<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeederX extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'texts.view',
            'texts.create',
            'texts.update',
            'texts.delete',
            'users.view',
            'users.create',
            'users.update',
            'users.password_change',
            'users.delete',
            'logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $translator = Role::firstOrCreate(['name' => 'translator']);
        $support = Role::firstOrCreate(['name' => 'support']);


        $admin->syncPermissions(Permission::all());

        $translator->syncPermissions([
            'texts.view',
            'texts.create',
            'texts.update',
            'texts.delete',
        ]);

        $support->syncPermissions([
            'texts.view',
        ]);

        Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'moviesound@ya.ru',
            'password' => Hash::make('admin'),
        ])->assignRole('admin');
    }
}
