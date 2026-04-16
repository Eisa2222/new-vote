<?php

declare(strict_types=1);

namespace App\Modules\Voting\Http\Requests;

use App\Modules\Campaigns\Domain\TeamOfSeasonFormation;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

/**
 * TOS formation is set by the ADMIN at campaign creation and stored in the
 * categories (required_picks). The voter must submit exactly that count per line.
 */
final class SubmitTeamOfSeasonVoteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $token = $this->route('token');
        $campaign = Campaign::where('public_token', $token)
            ->with('categories')->firstOrFail();
        $f = TeamOfSeasonFormation::fromCampaign($campaign);

        return [
            'attack'       => ['required', 'array', 'size:'.$f['attack']],
            'attack.*'     => ['integer', 'exists:voting_category_candidates,id'],
            'midfield'     => ['required', 'array', 'size:'.$f['midfield']],
            'midfield.*'   => ['integer', 'exists:voting_category_candidates,id'],
            'defense'      => ['required', 'array', 'size:'.$f['defense']],
            'defense.*'    => ['integer', 'exists:voting_category_candidates,id'],
            'goalkeeper'   => ['required', 'array', 'size:'.$f['goalkeeper']],
            'goalkeeper.*' => ['integer', 'exists:voting_category_candidates,id'],
        ];
    }
}
