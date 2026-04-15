<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Enums;

enum CampaignType: string
{
    case IndividualAward  = 'individual_award';   // e.g. Player of the Year
    case TeamAward        = 'team_award';         // e.g. Team of the Year
    case TeamOfTheSeason  = 'team_of_the_season'; // 11 players by position
}
