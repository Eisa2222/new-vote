<?php

declare(strict_types=1);

use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Clubs\Models\Club;
use App\Modules\Players\Enums\PlayerPosition;
use App\Modules\Players\Models\Player;
use App\Modules\Sports\Models\Sport;
use App\Modules\Voting\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeActiveCampaign(int $maxVoters = null): Campaign
{
    $sport = Sport::create(['slug' => 'football', 'name_ar' => 'كرة', 'name_en' => 'Football', 'status' => 'active']);
    $club  = Club::factory()->create();
    $player = Player::factory()->create([
        'club_id' => $club->id, 'sport_id' => $sport->id, 'position' => PlayerPosition::Attack,
    ]);

    $c = Campaign::create([
        'title_ar'   => 'أفضل لاعب', 'title_en' => 'Player of the Year',
        'type'       => CampaignType::IndividualAward->value,
        'start_at'   => now()->subHour(),
        'end_at'     => now()->addDay(),
        'max_voters' => $maxVoters,
        'status'     => CampaignStatus::Active->value,
    ]);

    $cat = $c->categories()->create([
        'title_ar' => 'الأفضل', 'title_en' => 'Best',
        'position_slot' => 'any', 'required_picks' => 1, 'display_order' => 0,
    ]);
    $cat->candidates()->create(['player_id' => $player->id, 'display_order' => 0]);

    return $c->load('categories.candidates');
}

it('accepts a valid public vote', function () {
    $c   = makeActiveCampaign();
    $cat = $c->categories->first();
    $cand= $cat->candidates->first();

    $this->post("/vote/{$c->public_token}", [
        'selections' => [['category_id' => $cat->id, 'candidate_ids' => [$cand->id]]],
    ])->assertRedirect(route('voting.thanks', $c->public_token));

    expect(Vote::count())->toBe(1);
});

it('prevents duplicate voting from same ip/user-agent', function () {
    $c   = makeActiveCampaign();
    $cat = $c->categories->first();
    $cand= $cat->candidates->first();

    $payload = ['selections' => [['category_id' => $cat->id, 'candidate_ids' => [$cand->id]]]];

    $this->post("/vote/{$c->public_token}", $payload)->assertRedirect();
    $this->post("/vote/{$c->public_token}", $payload)->assertSessionHasErrors();

    expect(Vote::count())->toBe(1);
});

it('auto-closes the campaign when max_voters reached', function () {
    $c   = makeActiveCampaign(maxVoters: 1);
    $cat = $c->categories->first();
    $cand= $cat->candidates->first();

    $this->post("/vote/{$c->public_token}", [
        'selections' => [['category_id' => $cat->id, 'candidate_ids' => [$cand->id]]],
    ]);

    expect($c->fresh()->status->value)->toBe('closed');
});
