<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SportsSeeder::class,
            RolesPermissionsSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'admin@sfpa.sa',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('super_admin');
    }
}
