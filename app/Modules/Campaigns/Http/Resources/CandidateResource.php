<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Http\Resources;

use App\Modules\Clubs\Http\Resources\ClubResource;
use App\Modules\Players\Http\Resources\PlayerResource;
use Illuminate\Http\Resources\Json\JsonResource;

final class CandidateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'player' => new PlayerResource($this->whenLoaded('player')),
            'club'   => new ClubResource($this->whenLoaded('club')),
        ];
    }
}
