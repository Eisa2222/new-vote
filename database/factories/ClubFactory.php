<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Clubs\Models\Club;
use App\Modules\Shared\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        $en = $this->faker->unique()->company();
        return [
            'name_ar'    => 'نادي '.$this->faker->unique()->firstNameMale(),
            'name_en'    => $en,
            'short_name' => strtoupper($this->faker->lexify('???')),
            'logo_path'  => null,
            'status'     => ActiveStatus::Active,
        ];
    }
}
