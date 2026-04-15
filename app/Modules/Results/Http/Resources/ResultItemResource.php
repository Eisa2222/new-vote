<?php

declare(strict_types=1);

namespace App\Modules\Results\Http\Resources;

use App\Modules\Campaigns\Http\Resources\CandidateResource;
use Illuminate\Http\Resources\Json\JsonResource;

final class ResultItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'voting_category_id' => $this->voting_category_id,
            'candidate'          => new CandidateResource($this->whenLoaded('candidate')),
            'votes_count'        => $this->votes_count,
            'rank'               => $this->rank,
            'is_winner'          => (bool) $this->is_winner,
        ];
    }
}
