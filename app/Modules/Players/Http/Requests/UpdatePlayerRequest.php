<?php

declare(strict_types=1);

namespace App\Modules\Players\Http\Requests;

use App\Modules\Players\Enums\PlayerPosition;
use App\Modules\Shared\Enums\ActiveStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('player')) ?? false;
    }

    public function rules(): array
    {
        $player = $this->route('player');

        return [
            'club_id'       => ['sometimes', 'required', 'integer', Rule::exists('clubs', 'id')],
            'sport_id'      => ['sometimes', 'required', 'integer', Rule::exists('sports', 'id')],
            'name_ar'       => ['sometimes', 'required', 'string', 'max:120'],
            'name_en'       => ['sometimes', 'required', 'string', 'max:120'],
            'photo'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'position'      => ['sometimes', new Enum(PlayerPosition::class)],
            'is_captain'    => ['boolean'],
            'jersey_number' => [
                'nullable', 'integer', 'min:1', 'max:999',
                Rule::unique('players')
                    ->ignore($player?->id)
                    ->where('club_id', $this->integer('club_id', $player?->club_id))
                    ->where('sport_id', $this->integer('sport_id', $player?->sport_id))
                    ->whereNull('deleted_at'),
            ],
            'status'        => ['nullable', new Enum(ActiveStatus::class)],
        ];
    }
}
