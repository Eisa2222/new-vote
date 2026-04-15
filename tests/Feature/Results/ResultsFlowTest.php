<?php

declare(strict_types=1);

use App\Modules\Campaigns\Enums\ResultsVisibility;
use App\Modules\Results\Actions\AnnounceResultsAction;
use App\Modules\Results\Actions\ApproveResultsAction;
use App\Modules\Results\Actions\CalculateCampaignResultsAction;
use App\Modules\Results\Enums\ResultStatus;
use App\Modules\Users\Actions\LogActivityAction;
use App\Modules\Voting\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

require_once __DIR__ . '/../Voting/PublicVotingTest.php';

it('calculates, approves, and announces results with proper visibility states', function () {
    $c   = makeActiveCampaign();
    $cat = $c->categories->first();
    $cand= $cat->candidates->first();

    $v = Vote::create([
        'campaign_id' => $c->id, 'voter_identifier' => 'abc',
        'submitted_at' => now(),
    ]);
    $v->items()->create(['voting_category_id' => $cat->id, 'candidate_id' => $cand->id]);

    $result = (new CalculateCampaignResultsAction())->execute($c);
    expect($result->status)->toBe(ResultStatus::Calculated);
    expect($result->items->first()->is_winner)->toBeTrue();

    // Before approval, visibility must still be hidden.
    expect($c->fresh()->results_visibility)->toBe(ResultsVisibility::Hidden);

    $log = new LogActivityAction();
    (new ApproveResultsAction($log))->execute($result);
    expect($c->fresh()->results_visibility)->toBe(ResultsVisibility::Approved);

    (new AnnounceResultsAction($log))->execute($result->fresh());
    expect($c->fresh()->results_visibility)->toBe(ResultsVisibility::Announced);
});
