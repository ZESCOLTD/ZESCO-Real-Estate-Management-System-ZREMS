<?php

namespace App\Policies;

use App\Models\Stakeholder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StakeholderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Stakeholder $stakeholder): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Stakeholder $stakeholder): bool
    {
    }

    public function delete(User $user, Stakeholder $stakeholder): bool
    {
    }

    public function restore(User $user, Stakeholder $stakeholder): bool
    {
    }

    public function forceDelete(User $user, Stakeholder $stakeholder): bool
    {
    }
}
