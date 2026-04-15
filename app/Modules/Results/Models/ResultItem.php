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
        'votes_count', 'rank', 'is_winner',
    ];

    protected $casts = ['is_winner' => 'boolean'];

    public function result(): BelongsTo { return $this->belongsTo(CampaignResult::class, 'campaign_result_id'); }
    public function category(): BelongsTo { return $this->belongsTo(VotingCategory::class, 'voting_category_id'); }
    public function candidate(): BelongsTo { return $this->belongsTo(VotingCategoryCandidate::class, 'candidate_id'); }
}
