<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain;

use App\Modules\Players\Enums\PlayerPosition;

/**
 * Enforces the 3-3-4-1 distribution for the Team of the Season.
 *
 * The "distribution" is expressed as category slots (required_picks per
 * position_slot). We require the sum to match exactly this blueprint.
 */
final class TeamOfTheSeasonDistributionRule
{
    public const BLUEPRINT = [
        'attack'     => 3,
        'midfield'   => 3,
        'defense'    => 4,
        'goalkeeper' => 1,
    ];

    /**
     * @param  array<int, array{position_slot:string, required_picks:int}>  $categories
     */
    public function validate(array $categories): void
    {
        $totals = ['attack' => 0, 'midfield' => 0, 'defense' => 0, 'goalkeeper' => 0];

        foreach ($categories as $c) {
            $slot = $c['position_slot'] ?? null;
            if (! array_key_exists($slot, $totals)) {
                throw new \DomainException("Invalid position_slot '{$slot}' for Team of the Season.");
            }
            $totals[$slot] += (int) ($c['required_picks'] ?? 0);
        }

        foreach (self::BLUEPRINT as $slot => $expected) {
            if ($totals[$slot] !== $expected) {
                throw new \DomainException(
                    "Team of the Season requires exactly {$expected} {$slot} slots, got {$totals[$slot]}."
                );
            }
        }
    }

    public function validatePicks(array $picks): void
    {
        $counts = ['attack' => 0, 'midfield' => 0, 'defense' => 0, 'goalkeeper' => 0];
        foreach ($picks as $position) {
            $p = $position instanceof PlayerPosition ? $position->value : $position;
            if (! isset($counts[$p])) {
                throw new \DomainException("Invalid player position '{$p}'.");
            }
            $counts[$p]++;
        }

        if ($counts !== self::BLUEPRINT) {
            throw new \DomainException('Team of the Season picks must be 3 attack, 3 midfield, 4 defense, 1 goalkeeper.');
        }
    }
}
