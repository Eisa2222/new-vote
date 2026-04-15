<?php

declare(strict_types=1);

namespace App\Modules\Players\Policies;

use App\Models\User;
use App\Modules\Players\Models\Player;

final class PlayerPolicy
{
    public function viewAny(User $user): bool { return $user->can('players.viewAny'); }
    public function view(User $user, Player $p): bool { return $user->can('players.viewAny'); }
    public function create(User $user): bool { return $user->can('players.create'); }
    public function update(User $user, Player $p): bool { return $user->can('players.update'); }
    public function delete(User $user, Player $p): bool { return $user->can('players.delete'); }
}
