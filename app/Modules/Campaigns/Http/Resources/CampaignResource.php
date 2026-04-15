<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class CampaignResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->localized('title'),
            'title_ar'           => $this->title_ar,
            'title_en'           => $this->title_en,
            'description_ar'     => $this->description_ar,
            'description_en'     => $this->description_en,
            'type'               => $this->type?->value,
            'start_at'           => $this->start_at?->toIso8601String(),
            'end_at'             => $this->end_at?->toIso8601String(),
            'max_voters'         => $this->max_voters,
            'status'             => $this->status?->value,
            'results_visibility' => $this->results_visibility?->value,
            'public_url'         => url("/vote/{$this->public_token}"),
            'categories'         => VotingCategoryResource::collection($this->whenLoaded('categories')),
            'votes_count'        => $this->whenCounted('votes'),
        ];
    }
}
