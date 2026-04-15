<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Actions;

use App\Modules\Campaigns\Domain\TeamOfTheSeasonDistributionRule;
use App\Modules\Campaigns\Enums\CampaignStatus;
use App\Modules\Campaigns\Enums\CampaignType;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Users\Actions\LogActivityAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class CreateVotingCampaignAction
{
    public function __construct(
        private readonly LogActivityAction $log,
        private readonly TeamOfTheSeasonDistributionRule $tots,
    ) {}

    public function execute(array $data): Campaign
    {
        return DB::transaction(function () use ($data) {
            $categories = $data['categories'];
            unset($data['categories']);

            if (($data['type'] ?? null) === CampaignType::TeamOfTheSeason->value) {
                $this->tots->validate($categories);
            }

            $data['status']     = CampaignStatus::Draft->value;
            $data['created_by'] = Auth::id();

            /** @var Campaign $campaign */
            $campaign = Campaign::create($data);

            foreach ($categories as $i => $cat) {
                $category = $campaign->categories()->create([
                    'title_ar'       => $cat['title_ar'],
                    'title_en'       => $cat['title_en'],
                    'position_slot'  => $cat['position_slot'],
                    'required_picks' => $cat['required_picks'],
                    'display_order'  => $i,
                ]);

                foreach ($cat['candidates'] as $j => $cand) {
                    $category->candidates()->create([
                        'player_id'     => $cand['player_id'] ?? null,
                        'club_id'       => $cand['club_id']   ?? null,
                        'display_order' => $j,
                    ]);
                }
            }

            $this->log->execute('campaigns.created', $campaign);

            return $campaign->load('categories.candidates.player', 'categories.candidates.club');
        });
    }
}
