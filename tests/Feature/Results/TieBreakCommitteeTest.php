<?php

declare(strict_types=1);

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Enums\CategoryType;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Results\Actions\ApproveResultsAction;
use App\Modules\Results\Actions\CalculateCampaignResultsAction;
use App\Modules\Results\Actions\ResolveTieAction;
use App\Modules\Voting\Models\Vote;

beforeEach(function () { seedRolesAndPermissions(); });

/**
 * Build an individual-award campaign with one category, N candidates,
 * and inject pre-counted votes so we can simulate a tie at the cutoff.
 *
 * @param  array<int, int>  $voteMap  candidate_display_order => vote count
 */
function tieCampaignWithVotes(int $requiredPicks, array $voteMap): array
{
    $club = makeClub();
    makeFootball();

    $c = Campaign::create([
        'title_ar' => 'سباق تعادل', 'title_en' => 'Tie race',
        'type'     => CampaignType::IndividualAward->value,
        'status'   => CampaignStatus::Active->value,
        'start_at' => now()->subDay(),
        'end_at'   => now()->addDay(),
    ]);
    $cat = $c->categories()->create([
        'title_ar' => 'فئة', 'title_en' => 'Category',
        'category_type' => CategoryType::SingleChoice->value,
        'position_slot' => 'any',
        'required_picks'=> $requiredPicks,
        'selection_min' => $requiredPicks,
        'selection_max' => $requiredPicks,
        'is_active'     => true,
        'display_order' => 0,
    ]);

    // Build candidates + inject votes by raw INSERT so we don't have to
    // orchestrate the full public-voting flow for each fake vote.
    foreach ($voteMap as $displayOrder => $votes) {
        $player = makePlayer(['club_id' => $club->id, 'name_en' => 'P'.$displayOrder]);
        $cand = $cat->candidates()->create([
            'candidate_type' => 'player',
            'player_id'      => $player->id,
            'display_order'  => $displayOrder,
            'is_active'      => true,
        ]);
        for ($i = 0; $i < $votes; $i++) {
            $v = Vote::create([
                'campaign_id'      => $c->id,
                'voter_identifier' => bin2hex(random_bytes(16)),
                'submitted_at'     => now(),
            ]);
            $v->items()->create([
                'voting_category_id' => $cat->id,
                'candidate_id'       => $cand->id,
            ]);
        }
    }

    return [$c, $cat];
}

it('flags a tie-at-cutoff as needing a committee decision', function () {
    // 3 slots. Winners are P1(100), P2(90), then a tie between P3 and P4 (80 each).
    [$c] = tieCampaignWithVotes(3, [1 => 100, 2 => 90, 3 => 80, 4 => 80, 5 => 10]);

    $result = app(CalculateCampaignResultsAction::class)->execute($c->load('categories'));

    $tied = $result->items->where('needs_committee_decision', true);
    expect($tied)->toHaveCount(2);                          // P3 + P4 both tied
    expect($tied->pluck('is_winner')->every(fn ($v) => $v === null))->toBeTrue();

    $confirmed = $result->items->where('is_winner', true);
    expect($confirmed)->toHaveCount(2);                     // P1 and P2 only
});

it('does not flag ties that fall entirely outside the winners cutoff', function () {
    // 2 slots. P1(100), P2(90), P3(50), P4(50). Tie at 50 is below cutoff → ignored.
    [$c] = tieCampaignWithVotes(2, [1 => 100, 2 => 90, 3 => 50, 4 => 50]);

    $result = app(CalculateCampaignResultsAction::class)->execute($c->load('categories'));

    expect($result->items->where('needs_committee_decision', true))->toBeEmpty();
    expect($result->items->where('is_winner', true))->toHaveCount(2);
});

it('blocks approval while a tie is unresolved', function () {
    [$c] = tieCampaignWithVotes(3, [1 => 100, 2 => 90, 3 => 80, 4 => 80]);
    $result = app(CalculateCampaignResultsAction::class)->execute($c->load('categories'));

    expect(fn() => app(ApproveResultsAction::class)->execute($result))
        ->toThrow(\DomainException::class);
});

it('committee resolution stamps winner, clears ambiguity, and unblocks approval', function () {
    [$c, $cat] = tieCampaignWithVotes(3, [1 => 100, 2 => 90, 3 => 80, 4 => 80]);
    $result = app(CalculateCampaignResultsAction::class)->execute($c->load('categories'));

    $tiedIds = $result->items->where('needs_committee_decision', true)->pluck('candidate_id')->all();
    expect($tiedIds)->toHaveCount(2);

    // Pick the first tied candidate as the committee's winner
    app(ResolveTieAction::class)->execute($result, $cat->id, [$tiedIds[0]]);

    $result->refresh()->load('items');
    expect($result->items->where('needs_committee_decision', true))->toBeEmpty();
    expect($result->items->where('is_winner', true))->toHaveCount(3);
    expect($result->items->firstWhere('candidate_id', $tiedIds[0])->is_winner)->toBeTrue();
    expect($result->items->firstWhere('candidate_id', $tiedIds[1])->is_winner)->toBeFalse();

    // Approval now works
    app(ApproveResultsAction::class)->execute($result);
    expect($result->fresh()->status->value)->toBe('approved');
});

it('rejects resolution when the wrong number of winners are picked', function () {
    [$c, $cat] = tieCampaignWithVotes(3, [1 => 100, 2 => 90, 3 => 80, 4 => 80]);
    $result = app(CalculateCampaignResultsAction::class)->execute($c->load('categories'));
    $tiedIds = $result->items->where('needs_committee_decision', true)->pluck('candidate_id')->all();

    expect(fn() => app(ResolveTieAction::class)->execute($result, $cat->id, $tiedIds))
        ->toThrow(\DomainException::class);   // picked 2 but only 1 slot remains
});
