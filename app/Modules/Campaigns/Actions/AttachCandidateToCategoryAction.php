<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Enums\CandidateType;
use App\Modules\Campaigns\Models\VotingCategory;
use App\Modules\Campaigns\Models\VotingCategoryCandidate;
use App\Modules\Clubs\Models\Club;
use App\Modules\Players\Models\Player;

final class AttachCandidateToCategoryAction
{
    public function execute(VotingCategory $category, array $data): VotingCategoryCandidate
    {
        $type = CandidateType::from($data['candidate_type'] ?? 'player');

        $entity = match ($type) {
            CandidateType::Player => Player::findOrFail($data['candidate_id']),
            CandidateType::Club, CandidateType::Team => Club::findOrFail($data['candidate_id']),
        };

        // If the category targets a specific position, the player must match it.
        if ($type === CandidateType::Player
            && $category->position_slot !== 'any'
            && $entity->position?->value !== $category->position_slot) {
            throw new \DomainException(
                "Player {$entity->name_en} ({$entity->position?->value}) does not match the category position '{$category->position_slot}'."
            );
        }

        return $category->candidates()->create([
            'candidate_type' => $type->value,
            'player_id'      => $type === CandidateType::Player ? $data['candidate_id'] : null,
            'club_id'        => $type !== CandidateType::Player ? $data['candidate_id'] : null,
            'display_order'  => $data['display_order'] ?? (int) $category->candidates()->max('display_order') + 1,
            'is_active'      => $data['is_active'] ?? true,
        ]);
    }
}
