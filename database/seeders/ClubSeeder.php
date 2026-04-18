<?php

namespace Database\Seeders;

use App\Modules\Clubs\Models\Club;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        Club::factory()->count(1000)->create();
    }
}
