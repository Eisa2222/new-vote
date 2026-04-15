<?php

declare(strict_types=1);

namespace App\Modules\Shared\Enums;

enum ActiveStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active   => __('Active'),
            self::Inactive => __('Inactive'),
        };
    }
}
