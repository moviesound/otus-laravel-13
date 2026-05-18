<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // reset cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // texts
            'texts.view',
            'texts.create',
            'texts.update',
            'texts.delete',

            // users
            'users.view',
            'users.create',
            'users.update',
            'users.password_change',
            'users.delete',

            // logs
            'logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $translator = Role::firstOrCreate(['name' => 'translator']);
        $support = Role::firstOrCreate(['name' => 'support']);

        // ADMIN — всё
        $admin->syncPermissions(Permission::all());

        // TRANSLATOR — работа с текстами (без удаления)
        $translator->syncPermissions([
            'texts.view',
            'texts.create',
            'texts.update',
            'texts.delete',
        ]);

        // SUPPORT — только просмотр
        $support->syncPermissions([
            'texts.view',
        ]);


        Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ])->assignRole('admin');

        Admin::factory()->create([
            'name' => 'Translator',
            'email' => 'translator@example.com',
            'password' => Hash::make('translator'),
        ])->assignRole('translator');

        Admin::factory()->create([
            'name' => 'Support',
            'email' => 'support@example.com',
            'password' => Hash::make('support'),
        ])->assignRole('support');
    }
}
