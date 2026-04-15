<?php

declare(strict_types=1);

namespace App\Modules\Voting\Models;

use App\Modules\Campaigns\Models\VotingCategory;
use App\Modules\Campaigns\Models\VotingCategoryCandidate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VoteItem extends Model
{
    protected $fillable = ['vote_id', 'voting_category_id', 'candidate_id'];

    public function vote(): BelongsTo { return $this->belongsTo(Vote::class); }
    public function category(): BelongsTo { return $this->belongsTo(VotingCategory::class, 'voting_category_id'); }
    public function candidate(): BelongsTo { return $this->belongsTo(VotingCategoryCandidate::class, 'candidate_id'); }
}
