<?php

declare(strict_types=1);

namespace App\Modules\Voting\Actions;

use App\Modules\Campaigns\Domain\TeamOfSeasonFormation;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Voting\Exceptions\VotingException;

/**
 * Validates a voter-chosen TOS selection.
 *
 * The VOTER picks any valid formation. We check:
 *   - exactly 4 keys: attack, midfield, defense, goalkeeper
 *   - goalkeeper = 1, attack+midfield+defense = 10, each outfield in [2, 6]
 *     (all enforced via TeamOfSeasonFormation::validate)
 *   - no duplicate candidate across lines
 *   - every picked candidate belongs to its declared line in this campaign
 */
final class ValidateTeamOfSeasonSelectionAction
{
    /**
     * @param  array<string, int[]>  $payload
     * @return array<int, array{category_id:int, candidate_ids:int[]}>
     */
    public function execute(Campaign $campaign, array $payload): array
    {
        $required = ['attack', 'midfield', 'defense', 'goalkeeper'];

        $unknown = array_diff(array_keys($payload), $required);
        if ($unknown) {
            throw new VotingException(__('Unexpected keys in selection: :keys', ['keys' => implode(',', $unknown)]));
        }
        foreach ($required as $slot) {
            if (! array_key_exists($slot, $payload)) {
                throw new VotingException(__('Line :slot is missing.', ['slot' => __(ucfirst($slot))]));
            }
        }

        $voterFormation = [
            'attack'     => count(array_unique($payload['attack'])),
            'midfield'   => count(array_unique($payload['midfield'])),
            'defense'    => count(array_unique($payload['defense'])),
            'goalkeeper' => count(array_unique($payload['goalkeeper'])),
        ];
        try {
            TeamOfSeasonFormation::validate($voterFormation);
        } catch (\DomainException $e) {
            throw new VotingException($e->getMessage());
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
        foreach ($required as $slot) {
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
