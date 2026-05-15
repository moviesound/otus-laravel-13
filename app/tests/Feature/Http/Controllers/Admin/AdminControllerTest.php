<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Contracts\AdminInterface;
use App\Models\Admin\Admin;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $connectionsToTransact = ['main', 'admin'];
    private AdminInterface $adminService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withServerVariables([
            'HTTP_HOST' => 'admin.localhost'
        ])
            ->withoutMiddleware(
                \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class
        );
    }

    private function authHelper()
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


        $adminUser = Admin::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ]);
        $adminUser->assignRole('admin');

        return $adminUser;
    }

    public function test_users_page_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->get('/users');

        $response->assertStatus(200);
    }

    public function test_users_create_admin_form_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->get('/users/create');

        $response->assertStatus(200);
    }


    public function test_new_admin_store_returns_409_when_user_exists(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->postJson('/users', [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'roles' => ['admin'],
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_new_admin_store_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->postJson('/users', [
            'name' => 'Admin2',
            'email' => 'admin2@example.com',
            'roles' => ['admin'],
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_users_edit_admin_form_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->get('/users/edit/' . $admin->id);

        $response->assertStatus(200);
    }


    public function test_edit_returns_404_when_user_not_found(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->putJson('/users/101', [
            'name' => 'Admin2',
            'email' => 'admin2@example.com',
            'roles' => ['admin'],
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_edit_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->putJson('/users/' . $admin->id, [
            'name' => 'Admin2',
            'email' => 'admin2@example.com',
            'roles' => ['admin'],
        ]);

        $response
            ->assertStatus(200);
    }

    public function test_users_delete_admin_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->deleteJson('/users/' . $admin->id);

        $response
            ->assertStatus(200);
    }

    public function test_users_delete_admin_returns_404_when_user_not_found(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->deleteJson('/users/1000');

        $response
            ->assertStatus(404)
            ->assertJson([
                'status' => 'error',
            ]);
    }


    public function test_users_admin_password_change_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->postJson('/users/password/'. $admin->id);

        $response->assertStatus(200);
    }

    public function test_users_admin_password_change_returns_404_when_user_not_found(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->postJson('/users/password/1000');

        $response
            ->assertStatus(404)
            ->assertJson([
                'status' => 'error',
            ]);
    }

}
