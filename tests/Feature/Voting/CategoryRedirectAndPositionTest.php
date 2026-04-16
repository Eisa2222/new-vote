<?php

declare(strict_types=1);

use App\Modules\Campaigns\Actions\AttachCandidateToCategoryAction;
use App\Modules\Campaigns\Actions\CreateTeamOfSeasonCampaignAction;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Players\Enums\PlayerPosition;
use App\Modules\Users\Actions\LogActivityAction;

it('opening /admin/campaigns/{id}/categories on a TOS campaign redirects to TOS UI', function () {
    seedRolesAndPermissions();
    $c = (new CreateTeamOfSeasonCampaignAction(new LogActivityAction()))->execute([
        'title_ar' => 'تشكيلة', 'title_en' => 'TOTS',
        'start_at' => now(), 'end_at' => now()->addDay(),
    ]);

    $this->actingAs(makeSuperAdmin())
        ->get("/admin/campaigns/{$c->id}/categories")
        ->assertRedirect("/admin/tos/{$c->id}/candidates");
});

it('generic categories page still works for individual awards', function () {
    $c = Campaign::create([
        'title_ar' => 'x', 'title_en' => 'x', 'type' => 'individual_award',
        'start_at' => now(), 'end_at' => now()->addDay(), 'status' => 'draft',
    ]);
    $this->actingAs(makeSuperAdmin())
        ->get("/admin/campaigns/{$c->id}/categories")
        ->assertOk();
});

it('AttachCandidateToCategoryAction rejects a player whose position mismatches the category slot', function () {
    seedRolesAndPermissions();
    $c = (new CreateTeamOfSeasonCampaignAction(new LogActivityAction()))->execute([
        'title_ar' => 'x', 'title_en' => 'x',
        'start_at' => now(), 'end_at' => now()->addDay(),
    ]);
    $gkCategory = $c->categories->firstWhere('position_slot', 'goalkeeper');
    $attacker = makePlayer(['position' => PlayerPosition::Attack, 'club_id' => makeClub()->id]);

    (new AttachCandidateToCategoryAction())->execute($gkCategory, [
        'candidate_type' => 'player',
        'candidate_id' => $attacker->id,
    ]);
})->throws(DomainException::class);

it('AttachCandidateToCategoryAction allows any player for a position_slot=any category', function () {
    seedRolesAndPermissions();
    $c = Campaign::create([
        'title_ar' => 'x', 'title_en' => 'x', 'type' => 'individual_award',
        'start_at' => now(), 'end_at' => now()->addDay(), 'status' => 'draft',
    ]);
    $cat = $c->categories()->create([
        'title_ar' => 'x', 'title_en' => 'X',
        'position_slot' => 'any', 'required_picks' => 1, 'is_active' => true,
    ]);
    $p = makePlayer();
    $result = (new AttachCandidateToCategoryAction())->execute($cat, [
        'candidate_type' => 'player', 'candidate_id' => $p->id,
    ]);
    expect($result->player_id)->toBe($p->id);
});
