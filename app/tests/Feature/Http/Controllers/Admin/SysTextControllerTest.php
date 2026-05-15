<?php

namespace Feature\Http\Controllers\Admin;

use App\Models\Admin\Permission;
use App\Models\Admin\Role;
use App\Models\Bot\SysText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use App\Contracts\AdminInterface;
use App\Models\Admin\Admin;

class SysTextControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $connectionsToTransact = ['main', 'admin'];
    private AdminInterface $adminService;

    const PAYLOAD = [
        'alias' => 'test',
        'context' => 'test',
        'lang' => 'ru'
    ];

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

    public function test_texts_page_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->get('/texts');

        $response->assertStatus(200);
    }

    public function test_create_text_form_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->get('/texts/create');

        $response->assertStatus(200);
    }


    public function test_new_text_store_returns_409_when_alias_exists(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        SysText::create(self::PAYLOAD);

        $this->postJson('/texts', self::PAYLOAD);

        $response = $this->postJson('/texts', self::PAYLOAD);

        $response
            ->assertStatus(409)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_new_text_store_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');



        $response = $this->postJson('/texts', self::PAYLOAD);

        $response
            ->assertStatus(200);
        $this->assertDatabaseHas('sys_texts', self::PAYLOAD);
    }

    public function test_edit_text_form_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');
        $text = SysText::create(self::PAYLOAD);
        $response = $this->get('/texts/edit/' . $text->id);

        $response->assertStatus(200);
    }


    public function test_edit_returns_404_when_text_not_found(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $nonExistingId = SysText::max('id') + 1000;

        $response = $this->putJson('/texts/' . $nonExistingId, self::PAYLOAD);

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Запись не найдена',
            ]);
    }

    public function test_edit_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');
        $text = SysText::create(self::PAYLOAD);
        $newPayload = [
            'alias' => 'd',
            'context' => '1',
            'lang' => 'ru',
        ];
        $response = $this->putJson('/texts/' . $text->id, $newPayload);

        $response
            ->assertStatus(200);
        $this->assertDatabaseHas('sys_texts', $newPayload);
    }

    public function test_edit_returns_422_wrong_alias_format(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');
        $text = SysText::create(self::PAYLOAD);
        $response = $this->putJson('/texts/' . $text->id, [
            'alias' => '1',
            'context' => '1',
            'lang' => 'ru',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['alias']);
    }

    public function test_delete_text_returns_200(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');
        $text = SysText::create(self::PAYLOAD);
        $response = $this->deleteJson('/texts/' . $text->id);

        $response
            ->assertStatus(200);
    }

    public function test_delete_text_returns_404_when_text_not_found(): void
    {
        $admin = $this->authHelper();

        $this->be($admin, 'admin');

        $response = $this->deleteJson('/texts/1000');

        $response
            ->assertStatus(404)
            ->assertJson([
                'status' => 'error',
            ]);
    }

}
