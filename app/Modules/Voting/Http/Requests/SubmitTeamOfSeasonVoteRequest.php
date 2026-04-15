<?php

declare(strict_types=1);

namespace App\Modules\Voting\Http\Requests;

use App\Modules\Campaigns\Domain\TeamOfSeasonFormation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Voter-chosen formation — each outfield line is accepted in [MIN_LINE, MAX_LINE].
 * The Action then enforces goalkeeper=1 and outfield sum=10.
 */
final class SubmitTeamOfSeasonVoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $min = TeamOfSeasonFormation::MIN_LINE;
        $max = TeamOfSeasonFormation::MAX_LINE;

        return [
            'attack'       => ['required', 'array', "min:{$min}", "max:{$max}"],
            'attack.*'     => ['integer', 'exists:voting_category_candidates,id'],
            'midfield'     => ['required', 'array', "min:{$min}", "max:{$max}"],
            'midfield.*'   => ['integer', 'exists:voting_category_candidates,id'],
            'defense'      => ['required', 'array', "min:{$min}", "max:{$max}"],
            'defense.*'    => ['integer', 'exists:voting_category_candidates,id'],
            'goalkeeper'   => ['required', 'array', 'size:1'],
            'goalkeeper.*' => ['integer', 'exists:voting_category_candidates,id'],
        ];
    }
}
