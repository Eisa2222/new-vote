<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class VotingCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->localized('title'),
            'title_ar'       => $this->title_ar,
            'title_en'       => $this->title_en,
            'position_slot'  => $this->position_slot,
            'required_picks' => $this->required_picks,
            'candidates'     => CandidateResource::collection($this->whenLoaded('candidates')),
        ];
    }
}
