<?php

declare(strict_types=1);

namespace App\Modules\Results\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class ResultResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'campaign_id'   => $this->campaign_id,
            'status'        => $this->status?->value,
            'calculated_at' => $this->calculated_at?->toIso8601String(),
            'approved_at'   => $this->approved_at?->toIso8601String(),
            'announced_at'  => $this->announced_at?->toIso8601String(),
            'items'         => ResultItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
