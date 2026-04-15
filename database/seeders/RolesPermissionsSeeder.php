<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // clubs
            'clubs.viewAny', 'clubs.create', 'clubs.update', 'clubs.delete',
            // players
            'players.viewAny', 'players.create', 'players.update', 'players.delete',
            // campaigns
            'campaigns.viewAny', 'campaigns.create', 'campaigns.update',
            'campaigns.publish', 'campaigns.close', 'campaigns.archive',
            // results
            'results.view', 'results.calculate', 'results.approve',
            'results.hide', 'results.announce',
            // users
            'users.manage',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'web');
        }

        $super = Role::findOrCreate('super_admin', 'web');
        $super->syncPermissions(Permission::all());

        Role::findOrCreate('auditor', 'web')->syncPermissions([
            'clubs.viewAny', 'players.viewAny', 'campaigns.viewAny', 'results.view',
        ]);
    }
}
