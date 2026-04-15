<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Http\Requests;

use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Campaign::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title_ar'       => ['required', 'string', 'max:180'],
            'title_en'       => ['required', 'string', 'max:180'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'type'           => ['required', new Enum(CampaignType::class)],
            'start_at'       => ['required', 'date'],
            'end_at'         => ['required', 'date', 'after:start_at'],
            'max_voters'     => ['nullable', 'integer', 'min:1'],

            'categories'                      => ['required', 'array', 'min:1'],
            'categories.*.title_ar'           => ['required', 'string', 'max:180'],
            'categories.*.title_en'           => ['required', 'string', 'max:180'],
            'categories.*.position_slot'      => ['required', 'in:attack,midfield,defense,goalkeeper,any'],
            'categories.*.required_picks'     => ['required', 'integer', 'min:1', 'max:11'],
            'categories.*.candidates'         => ['required', 'array', 'min:1'],
            'categories.*.candidates.*.player_id' => ['nullable', 'integer', 'exists:players,id'],
            'categories.*.candidates.*.club_id'   => ['nullable', 'integer', 'exists:clubs,id'],
        ];
    }
}
