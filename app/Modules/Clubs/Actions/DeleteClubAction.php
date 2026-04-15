<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Actions;

use App\Modules\Clubs\Models\Club;
use App\Modules\Users\Actions\LogActivityAction;

final class DeleteClubAction
{
    public function __construct(private readonly LogActivityAction $log) {}

    public function execute(Club $club): void
    {
        $this->log->execute('clubs.deleted', $club);
        $club->delete();
    }
}
