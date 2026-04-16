<?php

declare(strict_types=1);

namespace App\Modules\Voting\Actions;

use App\Modules\Campaigns\Domain\TeamOfSeasonFormation;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Voting\Exceptions\VotingException;

/**
 * Validates a voter submission against the ADMIN-set formation stored on the
 * campaign's categories. Voter must match the formation exactly.
 */
final class ValidateTeamOfSeasonSelectionAction
{
    /**
     * @param  array<string, int[]>  $payload
     * @return array<int, array{category_id:int, candidate_ids:int[]}>
     */
    public function execute(Campaign $campaign, array $payload): array
    {
        $formation = TeamOfSeasonFormation::fromCampaign($campaign);
        if (! $formation) {
            throw new VotingException(__('Campaign is not configured for Team of the Season.'));
        }

        $unknown = array_diff(array_keys($payload), array_keys($formation));
        if ($unknown) {
            throw new VotingException(__('Unexpected keys in selection: :keys', ['keys' => implode(',', $unknown)]));
        }

        foreach ($formation as $slot => $expected) {
            $got = count(array_unique($payload[$slot] ?? []));
            if ($got !== $expected) {
                throw new VotingException(__(
                    'Line :slot requires exactly :n players (got :got).',
                    ['slot' => __(ucfirst($slot)), 'n' => $expected, 'got' => $got],
                ));
            }
        }

        $all = array_merge(...array_values($payload));
        if (count($all) !== count(array_unique($all))) {
            throw new VotingException(__('A player cannot appear in more than one line.'));
        }

        $categories = $campaign->categories()
            ->where('is_active', true)
            ->with(['candidates' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->keyBy('position_slot');

        $selections = [];
        foreach ($formation as $slot => $expected) {
            $cat = $categories[$slot] ?? null;
            if (! $cat) {
                throw new VotingException(__('Campaign is not configured for Team of the Season.'));
            }
            $valid = $cat->candidates->pluck('id')->all();
            if (array_diff($payload[$slot], $valid)) {
                throw new VotingException(__('Invalid player(s) for line :slot.', ['slot' => __(ucfirst($slot))]));
            }
            $selections[] = [
                'category_id'   => $cat->id,
                'candidate_ids' => array_values(array_unique($payload[$slot])),
            ];
        }

        return $selections;
    }
}
