<?php

declare(strict_types=1);

namespace App\Modules\Leagues\Models;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Clubs\Models\Club;
use App\Modules\Shared\Concerns\HasTranslations;
use App\Modules\Shared\Enums\ActiveStatus;
use App\Modules\Sports\Models\Sport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class League extends Model
{
    use HasTranslations;

    protected $fillable = ['sport_id', 'slug', 'name_ar', 'name_en', 'status'];

    protected $casts = [
        'status' => ActiveStatus::class,
    ];

    public function sport(): BelongsTo { return $this->belongsTo(Sport::class); }

    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'club_league');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function scopeActive($q) { return $q->where('status', ActiveStatus::Active->value); }
}
