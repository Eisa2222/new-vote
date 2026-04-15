<?php

declare(strict_types=1);

namespace App\Modules\Results\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Enums\ResultsVisibility;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Actions\AnnounceResultsAction;
use App\Modules\Results\Actions\ApproveResultsAction;
use App\Modules\Results\Actions\BuildTeamOfTheSeasonAction;
use App\Modules\Results\Actions\CalculateCampaignResultsAction;
use App\Modules\Results\Actions\HideResultsAction;
use App\Modules\Results\Http\Resources\ResultResource;
use App\Modules\Results\Models\CampaignResult;
use Illuminate\Auth\Access\AuthorizationException;

final class ResultController extends Controller
{
    public function show(Campaign $campaign): ResultResource
    {
        $this->authorizeAny(['results.view']);
        $result = CampaignResult::where('campaign_id', $campaign->id)
            ->with('items.candidate.player.club', 'items.candidate.club', 'items.category')
            ->firstOrFail();
        return new ResultResource($result);
    }

    public function calculate(Campaign $campaign, CalculateCampaignResultsAction $action): ResultResource
    {
        $this->authorizeAny(['results.calculate']);
        return new ResultResource($action->execute($campaign));
    }

    public function approve(CampaignResult $result, ApproveResultsAction $action): ResultResource
    {
        $this->authorizeAny(['results.approve']);
        return new ResultResource($action->execute($result));
    }

    public function hide(CampaignResult $result, HideResultsAction $action): ResultResource
    {
        $this->authorizeAny(['results.hide']);
        return new ResultResource($action->execute($result));
    }

    public function announce(CampaignResult $result, AnnounceResultsAction $action): ResultResource
    {
        $this->authorizeAny(['results.announce']);
        return new ResultResource($action->execute($result));
    }

    public function teamOfTheSeason(Campaign $campaign, BuildTeamOfTheSeasonAction $action)
    {
        abort_if(
            $campaign->results_visibility !== ResultsVisibility::Announced,
            404,
            __('Not announced yet.')
        );
        $result = CampaignResult::where('campaign_id', $campaign->id)->firstOrFail();
        return response()->json(['data' => $action->execute($campaign, $result)]);
    }

    private function authorizeAny(array $permissions): void
    {
        $user = auth()->user();
        foreach ($permissions as $p) {
            if ($user?->can($p)) return;
        }
        throw new AuthorizationException();
    }
}
