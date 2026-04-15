<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Actions\CloseVotingCampaignAction;
use App\Modules\Campaigns\Actions\PublishVotingCampaignAction;
use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class AdminCampaignController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Campaign::class);
        $campaigns = Campaign::withCount('votes')->orderByDesc('id')->paginate(15);
        return view('admin.campaigns.index', compact('campaigns'));
    }

    public function show(Campaign $campaign): View
    {
        $this->authorize('view', $campaign);
        $campaign->load('categories.candidates.player.club', 'categories.candidates.club')->loadCount('votes');
        return view('admin.campaigns.show', compact('campaign'));
    }

    public function publish(Campaign $campaign, PublishVotingCampaignAction $a): RedirectResponse
    {
        $this->authorize('publish', $campaign);
        try {
            $a->execute($campaign);
            return back()->with('success', __('Campaign published.'));
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    public function close(Campaign $campaign, CloseVotingCampaignAction $a): RedirectResponse
    {
        $this->authorize('close', $campaign);
        try {
            $a->execute($campaign);
            return back()->with('success', __('Campaign closed.'));
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }
}
