<?php

namespace App\Policies;

use App\Models\TeamRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamRolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, TeamRole $teamRole): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, TeamRole $teamRole): bool
    {
    }

    public function delete(User $user, TeamRole $teamRole): bool
    {
    }

    public function restore(User $user, TeamRole $teamRole): bool
    {
    }

    public function forceDelete(User $user, TeamRole $teamRole): bool
    {
    }
}
