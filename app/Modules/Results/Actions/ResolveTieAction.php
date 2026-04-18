<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Results\Models\CampaignResult;
use App\Modules\Results\Models\ResultItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Committee resolves a tie inside one voting category.
 *
 * `$winnerIds` is the subset of tied candidate_ids the committee
 * picked as the extra winners. The count must equal the remaining
 * slot deficit in that category (we infer it here rather than
 * trusting the client).
 *
 * Every item flagged `needs_committee_decision` in this category
 * is updated:
 *   - if its candidate_id is in $winnerIds → is_winner=true
 *   - otherwise                            → is_winner=false
 * Either way `needs_committee_decision` is cleared and the audit
 * fields are stamped with the acting user.
 */
final class ResolveTieAction
{
    /**
     * @param  int[]  $winnerIds
     */
    public function execute(CampaignResult $result, int $categoryId, array $winnerIds): void
    {
        $items = $result->items()
            ->where('voting_category_id', $categoryId)
            ->where('needs_committee_decision', true)
            ->get();

        if ($items->isEmpty()) {
            throw new \DomainException(__('There is no pending tie in this line.'));
        }

        $category = $result->campaign->categories->firstWhere('id', $categoryId);
        if (! $category) {
            throw new \DomainException(__('Invalid category.'));
        }

        // How many remaining winner slots in this category? = required_picks
        // minus already-confirmed winners in the same category.
        $confirmedWinners = $result->items()
            ->where('voting_category_id', $categoryId)
            ->where('is_winner', true)
            ->count();
        $expectedWinners = max(0, (int) $category->required_picks - $confirmedWinners);

        $winnerIds = array_values(array_unique(array_map('intval', $winnerIds)));

        if (count($winnerIds) !== $expectedWinners) {
            throw new \DomainException(__(
                'Pick exactly :n winner(s) from the tied candidates (you picked :got).',
                ['n' => $expectedWinners, 'got' => count($winnerIds)],
            ));
        }

        $validIds = $items->pluck('candidate_id')->all();
        if (array_diff($winnerIds, $validIds)) {
            throw new \DomainException(__('One of the selected candidates is not part of this tie.'));
        }

        DB::transaction(function () use ($items, $winnerIds) {
            foreach ($items as $item) {
                $item->update([
                    'is_winner'                => in_array((int) $item->candidate_id, $winnerIds, true),
                    'needs_committee_decision' => false,
                    'committee_decided_by'     => Auth::id(),
                    'committee_decided_at'     => now(),
                ]);
            }
        });
    }
}
