<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Actions\CloseVotingCampaignAction;
use App\Modules\Campaigns\Actions\CreateVotingCampaignAction;
use App\Modules\Campaigns\Actions\PublishVotingCampaignAction;
use App\Modules\Campaigns\Http\Requests\StoreCampaignRequest;
use App\Modules\Campaigns\Http\Requests\UpdateCampaignRequest;
use App\Modules\Campaigns\Http\Resources\CampaignResource;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Campaign::class);

        $paginator = Campaign::query()
            ->withCount('votes')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'),   fn ($q) => $q->where('type',   $request->string('type')))
            ->orderByDesc('id')
            ->paginate(15);

        return CampaignResource::collection($paginator);
    }

    public function show(Campaign $campaign): CampaignResource
    {
        $this->authorize('view', $campaign);
        return new CampaignResource(
            $campaign->load('categories.candidates.player.club', 'categories.candidates.club')
                ->loadCount('votes')
        );
    }

    public function store(StoreCampaignRequest $request, CreateVotingCampaignAction $action): CampaignResource
    {
        return new CampaignResource($action->execute($request->validated()));
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): CampaignResource
    {
        $campaign->update($request->validated());
        return new CampaignResource($campaign->fresh());
    }

    public function publish(Campaign $campaign, PublishVotingCampaignAction $action): CampaignResource
    {
        $this->authorize('publish', $campaign);
        return new CampaignResource($action->execute($campaign));
    }

    public function close(Campaign $campaign, CloseVotingCampaignAction $action): CampaignResource
    {
        $this->authorize('close', $campaign);
        return new CampaignResource($action->execute($campaign));
    }

    public function destroy(Campaign $campaign): JsonResponse
    {
        $this->authorize('update', $campaign);
        $campaign->delete();
        return response()->json(status: 204);
    }
}
