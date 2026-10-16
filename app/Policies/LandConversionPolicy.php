<?php

namespace App\Policies;

use App\Models\LandConversion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LandConversionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, LandConversion $landConversion): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, LandConversion $landConversion): bool
    {
    }

    public function delete(User $user, LandConversion $landConversion): bool
    {
    }

    public function restore(User $user, LandConversion $landConversion): bool
    {
    }

    public function forceDelete(User $user, LandConversion $landConversion): bool
    {
    }
}
