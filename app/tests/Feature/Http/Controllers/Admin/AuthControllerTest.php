<?php

namespace Feature\Http\Controllers\Admin;

use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Contracts\AdminInterface;
use App\Models\Admin\Admin;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $connectionsToTransact = ['main', 'admin'];
    private AdminInterface $adminService;

    const TEST_ADMIN_PASSWORD = 'admin';

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

    public function test_login_page_returns_200(): void
    {
        $response = $this->getJson('/login');

        $response->assertStatus(200);
    }

    private function createAdmin()
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
            'password' => Hash::make(self::TEST_ADMIN_PASSWORD),
        ]);
        $adminUser->assignRole('admin');

        return $adminUser;
    }

    public function test_login_is_valid_and_redirection_302_to_page_texts_when_user_exists(): void
    {
        $admin = $this->createAdmin();

        $response = $this->postJson('/login/', [
            'email' => $admin->email,
            'password' => self::TEST_ADMIN_PASSWORD,
        ]);

        $response->assertStatus(302)
            ->assertRedirect('/texts');
        $this->assertAuthenticated();
    }

    public function test_login_returns_302_redirect_to_login_when_user_not_exists(): void
    {
        $response = $this->postJson('/login/', [
            'email' => 'test@test.ru',
            'password' => '1',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();
    }

    public function test_user_logout_is_valid_and_redirection_302_to_login()
    {
        $admin = $this->createAdmin();

        $this->postJson('/login/', [
            'email' => $admin->email,
            'password' => self::TEST_ADMIN_PASSWORD,
        ]);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
