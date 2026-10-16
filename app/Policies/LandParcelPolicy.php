<?php

namespace App\Policies;

use App\Models\LandParcel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LandParcelPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, LandParcel $landParcel): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, LandParcel $landParcel): bool
    {
    }

    public function delete(User $user, LandParcel $landParcel): bool
    {
    }

    public function restore(User $user, LandParcel $landParcel): bool
    {
    }

    public function forceDelete(User $user, LandParcel $landParcel): bool
    {
    }
}
