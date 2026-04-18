<?php

declare(strict_types=1);

namespace App\Modules\Sports\Models;

use App\Modules\Clubs\Models\Club;
use App\Modules\Leagues\Models\League;
use App\Modules\Shared\Concerns\HasTranslations;
use App\Modules\Shared\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Sport extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'name_ar', 'name_en', 'status'];
    protected $casts = ['status' => ActiveStatus::class];

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_sport');
    }

    public function leagues(): HasMany
    {
        return $this->hasMany(League::class);
    }

    /**
     * Distinct count of clubs attached to this sport via EITHER path:
     *   - direct club_sport pivot, or
     *   - any league belonging to this sport (club_league ↔ leagues.sport_id)
     *
     * A club counted once even if it plays this sport in multiple leagues.
     */
    public function totalClubsCount(): int
    {
        $direct = $this->clubs()->pluck('clubs.id');
        $viaLeagues = Club::query()
            ->whereHas('leagues', fn ($q) => $q->where('leagues.sport_id', $this->id))
            ->pluck('id');

        return $direct->merge($viaLeagues)->unique()->count();
    }
}
