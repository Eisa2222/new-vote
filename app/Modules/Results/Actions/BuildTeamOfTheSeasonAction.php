<?php

declare(strict_types=1);

namespace App\Modules\Results\Actions;

use App\Modules\Campaigns\Domain\TeamOfTheSeasonDistributionRule;
use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Models\CampaignResult;

/**
 * Assembles the final 11-player team from an already-calculated result.
 * Validates the 3-3-4-1 distribution and returns the winners grouped by slot.
 */
final class BuildTeamOfTheSeasonAction
{
    public function __construct(private readonly TeamOfTheSeasonDistributionRule $rule) {}

    public function execute(Campaign $campaign, CampaignResult $result): array
    {
        if ($campaign->type !== CampaignType::TeamOfTheSeason) {
            throw new \DomainException('Not a Team of the Season campaign.');
        }

        $winners = $result->items()
            ->with(['candidate.player.club', 'category'])
            ->where('is_winner', true)
            ->get()
            ->groupBy(fn ($i) => $i->category->position_slot);

        $picks = [];
        foreach ($winners as $slot => $items) {
            foreach ($items as $i) {
                $picks[] = $slot;
            }
        }
        $this->rule->validatePicks($picks);

        return [
            'attack'     => $winners->get('attack', collect())->values(),
            'midfield'   => $winners->get('midfield', collect())->values(),
            'defense'    => $winners->get('defense', collect())->values(),
            'goalkeeper' => $winners->get('goalkeeper', collect())->values(),
        ];
    }
}
