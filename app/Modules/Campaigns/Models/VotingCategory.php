<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Models;

use App\Modules\Players\Enums\PlayerPosition;
use App\Modules\Shared\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class VotingCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'campaign_id', 'title_ar', 'title_en',
        'position_slot', 'required_picks', 'display_order',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(VotingCategoryCandidate::class)->orderBy('display_order');
    }

    public function positionSlot(): ?PlayerPosition
    {
        return $this->position_slot === 'any'
            ? null
            : PlayerPosition::tryFrom($this->position_slot);
    }
}
