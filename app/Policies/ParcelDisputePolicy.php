<?php

namespace App\Policies;

use App\Models\ParcelDispute;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParcelDisputePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ParcelDispute $parcelDispute): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ParcelDispute $parcelDispute): bool
    {
    }

    public function delete(User $user, ParcelDispute $parcelDispute): bool
    {
    }

    public function restore(User $user, ParcelDispute $parcelDispute): bool
    {
    }

    public function forceDelete(User $user, ParcelDispute $parcelDispute): bool
    {
    }
}
