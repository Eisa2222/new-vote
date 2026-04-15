<?php

declare(strict_types=1);

namespace App\Modules\Voting\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Voting\Actions\SubmitVoteAction;
use App\Modules\Voting\Http\Requests\SubmitVoteRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class PublicVoteController extends Controller
{
    public function show(string $token): View
    {
        $campaign = Campaign::where('public_token', $token)
            ->with('categories.candidates.player.club', 'categories.candidates.club')
            ->firstOrFail();

        abort_unless($campaign->isAcceptingVotes(), 410, __('This campaign is not open for voting.'));

        return view('voting::public', compact('campaign'));
    }

    public function submit(string $token, SubmitVoteRequest $request, SubmitVoteAction $action): RedirectResponse
    {
        $campaign = Campaign::where('public_token', $token)->firstOrFail();
        $action->execute($campaign, $request, $request->validated('selections'));
        return redirect()->route('voting.thanks', $token);
    }

    public function thanks(string $token): View
    {
        $campaign = Campaign::where('public_token', $token)->firstOrFail();
        return view('voting::thanks', compact('campaign'));
    }
}
