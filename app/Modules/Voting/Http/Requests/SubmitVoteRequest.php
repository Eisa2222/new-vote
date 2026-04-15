<?php

declare(strict_types=1);

namespace App\Modules\Voting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitVoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'selections'                   => ['required', 'array', 'min:1'],
            'selections.*.category_id'     => ['required', 'integer', 'exists:voting_categories,id'],
            'selections.*.candidate_ids'   => ['required', 'array', 'min:1'],
            'selections.*.candidate_ids.*' => ['integer', 'exists:voting_category_candidates,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'selections.required' => __('Please make at least one selection.'),
        ];
    }
}
