<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Enums;

enum CampaignStatus: string
{
    case Draft            = 'draft';
    case PendingApproval  = 'pending_approval';   // admin submitted → committee reviews
    case Published        = 'published';
    case Active           = 'active';
    case Closed           = 'closed';
    case Archived         = 'archived';
    case Rejected         = 'rejected';            // committee rejected; admin can edit and re-submit

    /** Which transitions are legal. */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft            => in_array($next, [self::PendingApproval, self::Published, self::Archived], true),
            self::PendingApproval  => in_array($next, [self::Published, self::Rejected, self::Archived], true),
            self::Rejected         => in_array($next, [self::Draft, self::PendingApproval, self::Archived], true),
            self::Published        => in_array($next, [self::Active, self::Closed, self::Archived], true),
            self::Active           => in_array($next, [self::Closed, self::Archived], true),
            self::Closed           => $next === self::Archived,
            self::Archived         => false,
        };
    }

    public function acceptsVotes(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft            => __('Draft'),
            self::PendingApproval  => __('Pending committee approval'),
            self::Rejected         => __('Rejected by committee'),
            self::Published        => __('Published'),
            self::Active           => __('Active'),
            self::Closed           => __('Closed'),
            self::Archived         => __('Archived'),
        };
    }
}
