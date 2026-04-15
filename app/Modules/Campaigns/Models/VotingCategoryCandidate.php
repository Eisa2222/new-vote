<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Models;

use App\Modules\Clubs\Models\Club;
use App\Modules\Players\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VotingCategoryCandidate extends Model
{
    protected $fillable = [
        'voting_category_id', 'player_id', 'club_id', 'display_order',
    ];

    public function category(): BelongsTo { return $this->belongsTo(VotingCategory::class, 'voting_category_id'); }
    public function player(): BelongsTo   { return $this->belongsTo(Player::class); }
    public function club(): BelongsTo     { return $this->belongsTo(Club::class); }
}
