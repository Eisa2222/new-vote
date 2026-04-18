<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Domain\ResultTieBreakerRule;
use App\Modules\Results\Enums\ResultStatus;
use App\Modules\Results\Events\ResultsCalculated;
use App\Modules\Results\Models\CampaignResult;
use App\Modules\Voting\Models\VoteItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Calculates / recalculates a campaign's results.
 *
 * Winner selection rules:
 *   - Sort by votes desc, then the deterministic tie-breaker
 *     (display_order, candidate_id).
 *   - Walk the sorted list: every candidate strictly above the cutoff
 *     is an unambiguous winner.
 *   - If a tie group STRADDLES the cutoff (e.g. 5 candidates tied on
 *     the same vote count but only 3 winner slots left), EVERY
 *     candidate in that tie group is marked `needs_committee_decision`
 *     and `is_winner` is left NULL until the committee picks manually.
 *
 *  Approval and announcement are blocked while any tie is unresolved.
 */
final class CalculateCampaignResultsAction
{
    public function __construct(private readonly ResultTieBreakerRule $tieBreaker = new ResultTieBreakerRule()) {}

    public function execute(Campaign $campaign): CampaignResult
    {
        return DB::transaction(function () use ($campaign) {
            $result = CampaignResult::firstOrCreate(
                ['campaign_id' => $campaign->id],
                ['status' => ResultStatus::PendingCalculation->value],
            );
            $result->items()->delete();

            $totalVotes = (int) $campaign->votes()->count();

            $rows = VoteItem::query()
                ->join('voting_category_candidates as c', 'c.id', '=', 'vote_items.candidate_id')
                ->selectRaw('vote_items.voting_category_id as voting_category_id,
                             vote_items.candidate_id       as candidate_id,
                             c.display_order               as display_order,
                             COUNT(*)                       as votes_count')
                ->whereIn('vote_id', fn ($q) => $q->select('id')->from('votes')
                    ->where('campaign_id', $campaign->id))
                ->groupBy('vote_items.voting_category_id', 'vote_items.candidate_id', 'c.display_order')
                ->get();

            $categories = $campaign->categories;
            $byCategory = $rows->groupBy('voting_category_id');

            foreach ($categories as $category) {
                $tallies       = $this->tieBreaker->sort($byCategory[$category->id] ?? collect());
                $categoryTotal = $tallies->sum('votes_count') ?: 1;
                $requiredPicks = (int) $category->required_picks;

                // Group tallies into tie-groups (same votes_count).
                // Walk the groups filling winner slots; the first group that
                // overflows is flagged as needing a committee decision.
                $winnersAssigned   = 0;
                $ambiguousCandIds  = [];    // candidate_ids in the tied-at-cutoff group
                $groups            = $tallies->groupBy('votes_count')->values(); // already desc-sorted

                foreach ($groups as $group) {
                    $groupSize = $group->count();
                    $remaining = $requiredPicks - $winnersAssigned;

                    if ($remaining <= 0) {
                        // all remaining candidates are non-winners
                        break;
                    }
                    if ($groupSize <= $remaining) {
                        // whole group is won
                        $winnersAssigned += $groupSize;
                    } else {
                        // tie straddles the cutoff — flag every member
                        foreach ($group as $r) $ambiguousCandIds[] = $r->candidate_id;
                        break;
                    }
                }

                foreach ($tallies as $i => $row) {
                    $isAmbiguous = in_array($row->candidate_id, $ambiguousCandIds, true);
                    $rank        = $i + 1;
                    $isWinner    = $isAmbiguous
                        ? null                        // committee must decide
                        : ($rank <= $requiredPicks);  // otherwise deterministic

                    $result->items()->create([
                        'voting_category_id'       => $category->id,
                        'candidate_id'             => $row->candidate_id,
                        'position'                 => $category->position_slot ?? null,
                        'votes_count'              => $row->votes_count,
                        'vote_percentage'          => round(($row->votes_count / $categoryTotal) * 100, 2),
                        'rank'                     => $rank,
                        'is_winner'                => $isWinner,
                        'needs_committee_decision' => $isAmbiguous,
                        'is_announced'             => false,
                    ]);
                }
            }

            $result->update([
                'status'        => ResultStatus::Calculated->value,
                'calculated_at' => now(),
                'calculated_by' => Auth::id(),
                'total_votes'   => $totalVotes,
            ]);

            event(new ResultsCalculated($result));

            return $result->fresh('items');
        });
    }
}
