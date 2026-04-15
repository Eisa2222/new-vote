<?php

declare(strict_types=1);

namespace App\Modules\Clubs\Policies;

use App\Models\User;
use App\Modules\Clubs\Models\Club;

final class ClubPolicy
{
    public function viewAny(User $user): bool { return $user->can('clubs.viewAny'); }
    public function view(User $user, Club $club): bool { return $user->can('clubs.viewAny'); }
    public function create(User $user): bool { return $user->can('clubs.create'); }
    public function update(User $user, Club $club): bool { return $user->can('clubs.update'); }
    public function delete(User $user, Club $club): bool { return $user->can('clubs.delete'); }
}
