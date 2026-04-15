<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Http\Resources;

use App\Modules\Sports\Http\Resources\SportResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class ClubResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->localized('name'),
            'name_ar'    => $this->name_ar,
            'name_en'    => $this->name_en,
            'short_name' => $this->short_name,
            'logo_url'   => $this->logo_path ? Storage::url($this->logo_path) : null,
            'status'     => $this->status?->value,
            'sports'     => SportResource::collection($this->whenLoaded('sports')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
