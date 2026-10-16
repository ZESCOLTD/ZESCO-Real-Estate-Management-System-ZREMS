<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wayleave;
use Illuminate\Auth\Access\HandlesAuthorization;

class WayleavePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, Wayleave $wayleave): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, Wayleave $wayleave): bool
    {
    }

    public function delete(User $user, Wayleave $wayleave): bool
    {
    }

    public function restore(User $user, Wayleave $wayleave): bool
    {
    }

    public function forceDelete(User $user, Wayleave $wayleave): bool
    {
    }
}
