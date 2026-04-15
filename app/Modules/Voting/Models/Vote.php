<?php

declare(strict_types=1);

namespace App\Modules\Voting\Models;

use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Vote extends Model
{
    protected $fillable = [
        'campaign_id', 'voter_identifier', 'ip_address', 'user_agent', 'submitted_at',
    ];

    protected $casts = ['submitted_at' => 'datetime'];

    public function campaign(): BelongsTo { return $this->belongsTo(Campaign::class); }
    public function items(): HasMany { return $this->hasMany(VoteItem::class); }
}
