<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DemoDataSeeder::class,
<<<<<<< HEAD
        ]);

        Admin::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('test'),
=======
            PermissionsSeeder::class,
>>>>>>> 3431310 (add first part)
        ]);
    }
}
