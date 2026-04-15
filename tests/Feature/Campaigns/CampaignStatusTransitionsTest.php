<?php

declare(strict_types=1);

use App\Modules\Campaigns\Enums\CampaignStatus;

it('allows draft -> published', function () {
    expect(CampaignStatus::Draft->canTransitionTo(CampaignStatus::Published))->toBeTrue();
});

it('forbids closed -> active', function () {
    expect(CampaignStatus::Closed->canTransitionTo(CampaignStatus::Active))->toBeFalse();
});

it('forbids archived -> anything', function () {
    foreach (CampaignStatus::cases() as $c) {
        expect(CampaignStatus::Archived->canTransitionTo($c))->toBeFalse();
    }
});
