<?php

declare(strict_types=1);

namespace App\Modules\Results\Models;

use App\Modules\Campaigns\Models\VotingCategory;
use App\Modules\Campaigns\Models\VotingCategoryCandidate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ResultItem extends Model
{
    protected $fillable = [
        'campaign_result_id', 'voting_category_id', 'candidate_id',
        'position', 'votes_count', 'vote_percentage', 'rank',
        'is_winner', 'is_announced', 'metadata',
        'needs_committee_decision', 'committee_decided_by', 'committee_decided_at',
    ];

    protected $casts = [
        'is_winner'                => 'boolean',
        'is_announced'             => 'boolean',
        'needs_committee_decision' => 'boolean',
        'committee_decided_at'     => 'datetime',
        'vote_percentage'          => 'float',
        'metadata'                 => 'array',
    ];

    public function result(): BelongsTo { return $this->belongsTo(CampaignResult::class, 'campaign_result_id'); }
    public function category(): BelongsTo { return $this->belongsTo(VotingCategory::class, 'voting_category_id'); }
    public function candidate(): BelongsTo { return $this->belongsTo(VotingCategoryCandidate::class, 'candidate_id'); }
    public function decidedBy(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'committee_decided_by'); }
}
