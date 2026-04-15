<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Clubs\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function actingAsSuperAdmin(): User
{
    $u = User::factory()->create();
    $role = Role::findOrCreate('super_admin', 'web');
    $role->givePermissionTo(['clubs.viewAny', 'clubs.create', 'clubs.update', 'clubs.delete']);
    $u->assignRole($role);
    return $u;
}

it('lists clubs', function () {
    Club::factory()->count(3)->create();
    $this->actingAs(actingAsSuperAdmin(), 'sanctum')
        ->getJson('/api/v1/clubs')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
});

it('creates a club', function () {
    $this->actingAs(actingAsSuperAdmin(), 'sanctum')
        ->postJson('/api/v1/clubs', [
            'name_ar'    => 'نادي الهلال',
            'name_en'    => 'Al Hilal',
            'short_name' => 'HIL',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name_en', 'Al Hilal');

    expect(Club::where('name_en', 'Al Hilal')->exists())->toBeTrue();
});

it('rejects duplicate club name', function () {
    Club::factory()->create(['name_en' => 'Al Nassr']);
    $this->actingAs(actingAsSuperAdmin(), 'sanctum')
        ->postJson('/api/v1/clubs', ['name_ar' => 'النصر', 'name_en' => 'Al Nassr'])
        ->assertUnprocessable();
});

it('forbids users without permission', function () {
    $u = User::factory()->create();
    $this->actingAs($u, 'sanctum')
        ->postJson('/api/v1/clubs', ['name_ar' => 'x', 'name_en' => 'y'])
        ->assertForbidden();
});
