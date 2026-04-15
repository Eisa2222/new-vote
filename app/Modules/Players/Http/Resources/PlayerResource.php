<?php

declare(strict_types=1);

namespace App\Modules\Players\Http\Resources;

use App\Modules\Clubs\Http\Resources\ClubResource;
use App\Modules\Sports\Http\Resources\SportResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class PlayerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->localized('name'),
            'name_ar'       => $this->name_ar,
            'name_en'       => $this->name_en,
            'photo_url'     => $this->photo_path ? Storage::url($this->photo_path) : null,
            'position'      => $this->position?->value,
            'position_label'=> $this->position?->label(),
            'is_captain'    => (bool) $this->is_captain,
            'jersey_number' => $this->jersey_number,
            'status'        => $this->status?->value,
            'club'          => new ClubResource($this->whenLoaded('club')),
            'sport'         => new SportResource($this->whenLoaded('sport')),
        ];
    }
}
