<?php

namespace App\Policies;

use App\Models\ParcelStakeholder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParcelStakeholderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ParcelStakeholder $parcelStakeholder): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ParcelStakeholder $parcelStakeholder): bool
    {
    }

    public function delete(User $user, ParcelStakeholder $parcelStakeholder): bool
    {
    }

    public function restore(User $user, ParcelStakeholder $parcelStakeholder): bool
    {
    }

    public function forceDelete(User $user, ParcelStakeholder $parcelStakeholder): bool
    {
    }
}
