<?php

declare(strict_types=1);

use App\Modules\Campaigns\Actions\ActivateVotingCampaignAction;
use App\Modules\Campaigns\Actions\PublishVotingCampaignAction;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;

function mkCampaign(string $status = 'draft', array $attrs = []): Campaign
{
    return Campaign::create(array_merge([
        'title_ar' => 'x', 'title_en' => 'x', 'type' => 'individual_award',
        'start_at' => now()->addDay(), 'end_at' => now()->addDays(7),
        'status' => $status,
    ], $attrs));
}

it('admin can open the edit page for a draft campaign', function () {
    $c = mkCampaign();
    $this->actingAs(makeSuperAdmin())
        ->get("/admin/campaigns/{$c->id}/edit")
        ->assertOk()
        ->assertSee($c->title_en);
});

it('edit page is forbidden for non-draft campaigns', function () {
    $c = mkCampaign('active', ['start_at' => now()->subHour(), 'end_at' => now()->addDay()]);
    $this->actingAs(makeSuperAdmin())
        ->get("/admin/campaigns/{$c->id}/edit")
        ->assertForbidden();
});

it('admin can update a draft campaign title and dates', function () {
    $c = mkCampaign();
    $this->actingAs(makeSuperAdmin())
        ->put("/admin/campaigns/{$c->id}", [
            'title_ar' => 'محدّث', 'title_en' => 'Updated',
            'type' => 'individual_award',
            'start_at' => now()->addDays(2)->toDateTimeString(),
            'end_at'   => now()->addDays(5)->toDateTimeString(),
        ])
        ->assertRedirect("/admin/campaigns/{$c->id}");

    expect($c->fresh()->title_en)->toBe('Updated');
});

it('update is forbidden for non-draft campaigns', function () {
    $c = mkCampaign('active', ['start_at' => now()->subHour(), 'end_at' => now()->addDay()]);
    $this->actingAs(makeSuperAdmin())
        ->put("/admin/campaigns/{$c->id}", [
            'title_ar' => 'x', 'title_en' => 'x', 'type' => 'individual_award',
            'start_at' => now()->toDateTimeString(),
            'end_at' => now()->addDay()->toDateTimeString(),
        ])
        ->assertForbidden();
});

it('activate pulls start_at forward when in future', function () {
    $c = mkCampaign();
    (new PublishVotingCampaignAction(new LogActivityAction()))->execute($c); // → published
    expect($c->fresh()->status->value)->toBe('published');
    expect($c->fresh()->start_at->isFuture())->toBeTrue();

    (new ActivateVotingCampaignAction(new LogActivityAction()))->execute($c->fresh());

    $c = $c->fresh();
    expect($c->status->value)->toBe('active');
    expect($c->start_at->isFuture())->toBeFalse(); // moved to now()
});

it('activate extends end_at when it was past', function () {
    $c = mkCampaign('published', ['start_at' => now()->subDay(), 'end_at' => now()->subHour()]);
    (new ActivateVotingCampaignAction(new LogActivityAction()))->execute($c);
    expect($c->fresh()->end_at->isFuture())->toBeTrue();
});
