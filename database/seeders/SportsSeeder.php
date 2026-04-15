<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Sports\Models\Sport;
use Illuminate\Database\Seeder;

final class SportsSeeder extends Seeder
{
    public function run(): void
    {
        $sports = [
            ['slug' => 'football',   'name_ar' => 'كرة القدم',   'name_en' => 'Football'],
            ['slug' => 'basketball', 'name_ar' => 'كرة السلة',    'name_en' => 'Basketball'],
            ['slug' => 'volleyball', 'name_ar' => 'كرة الطائرة', 'name_en' => 'Volleyball'],
            ['slug' => 'handball',   'name_ar' => 'كرة اليد',     'name_en' => 'Handball'],
        ];

        foreach ($sports as $s) {
            Sport::updateOrCreate(['slug' => $s['slug']], $s + ['status' => 'active']);
        }
    }
}
