<?php

declare(strict_types=1);

namespace App\Modules\Sports\Models;

use App\Modules\Clubs\Models\Club;
use App\Modules\Shared\Concerns\HasTranslations;
use App\Modules\Shared\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Sport extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'name_ar', 'name_en', 'status'];
    protected $casts = ['status' => ActiveStatus::class];

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_sport');
    }
}
