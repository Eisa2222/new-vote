<?php

declare(strict_types=1);

namespace App\Modules\Sports\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class SportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'slug'    => $this->slug,
            'name'    => $this->localized('name'),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'status'  => $this->status?->value,
        ];
    }
}
