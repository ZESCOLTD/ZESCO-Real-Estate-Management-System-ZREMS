<?php

namespace App\Policies;

use App\Models\InvitationToTreat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationToTreatPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, InvitationToTreat $invitationToTreat): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, InvitationToTreat $invitationToTreat): bool
    {
    }

    public function delete(User $user, InvitationToTreat $invitationToTreat): bool
    {
    }

    public function restore(User $user, InvitationToTreat $invitationToTreat): bool
    {
    }

    public function forceDelete(User $user, InvitationToTreat $invitationToTreat): bool
    {
    }
}
