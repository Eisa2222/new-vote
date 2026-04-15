<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Enums\ResultStatus;
use App\Modules\Results\Models\CampaignResult;
use App\Modules\Voting\Models\VoteItem;
use Illuminate\Support\Facades\DB;

final class CalculateCampaignResultsAction
{
    public function execute(Campaign $campaign): CampaignResult
    {
        return DB::transaction(function () use ($campaign) {
            $result = CampaignResult::firstOrCreate(
                ['campaign_id' => $campaign->id],
                ['status' => ResultStatus::PendingCalculation->value],
            );

            $result->items()->delete();

            $rows = VoteItem::query()
                ->selectRaw('voting_category_id, candidate_id, COUNT(*) as votes_count')
                ->whereIn('vote_id', fn ($q) => $q->select('id')->from('votes')
                    ->where('campaign_id', $campaign->id))
                ->groupBy('voting_category_id', 'candidate_id')
                ->get();

            $byCategory = $rows->groupBy('voting_category_id');

            foreach ($campaign->categories as $category) {
                $tallies = ($byCategory[$category->id] ?? collect())
                    ->sortByDesc('votes_count')
                    ->values();

                $winnersTaken = 0;
                foreach ($tallies as $i => $row) {
                    $isWinner = $winnersTaken < (int) $category->required_picks;
                    if ($isWinner) $winnersTaken++;

                    $result->items()->create([
                        'voting_category_id' => $category->id,
                        'candidate_id'       => $row->candidate_id,
                        'votes_count'        => $row->votes_count,
                        'rank'               => $i + 1,
                        'is_winner'          => $isWinner,
                    ]);
                }
            }

            $result->update([
                'status'        => ResultStatus::Calculated->value,
                'calculated_at' => now(),
            ]);

            return $result->fresh('items');
        });
    }
}
