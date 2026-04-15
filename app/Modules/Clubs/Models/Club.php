<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Models;

use App\Modules\Players\Models\Player;
use App\Modules\Shared\Concerns\HasTranslations;
use App\Modules\Shared\Enums\ActiveStatus;
use App\Modules\Sports\Models\Sport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Club extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name_ar', 'name_en', 'short_name', 'logo_path', 'status',
    ];

    protected $casts = [
        'status' => ActiveStatus::class,
    ];

    public function sports(): BelongsToMany
    {
        return $this->belongsToMany(Sport::class, 'club_sport');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function scopeActive($q)
    {
        return $q->where('status', ActiveStatus::Active->value);
    }

    public function scopeSearch($q, ?string $term)
    {
        if (! $term) return $q;
        $like = '%'.$term.'%';
        return $q->where(fn ($w) => $w
            ->where('name_ar', 'like', $like)
            ->orWhere('name_en', 'like', $like)
            ->orWhere('short_name', 'like', $like));
    }

    protected static function newFactory()
    {
        return \Database\Factories\ClubFactory::new();
    }
}
