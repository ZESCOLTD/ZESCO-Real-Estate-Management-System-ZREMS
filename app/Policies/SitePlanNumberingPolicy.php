<?php

namespace App\Policies;

use App\Models\SitePlanNumbering;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePlanNumberingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, SitePlanNumbering $sitePlanNumbering): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, SitePlanNumbering $sitePlanNumbering): bool
    {
    }

    public function delete(User $user, SitePlanNumbering $sitePlanNumbering): bool
    {
    }

    public function restore(User $user, SitePlanNumbering $sitePlanNumbering): bool
    {
    }

    public function forceDelete(User $user, SitePlanNumbering $sitePlanNumbering): bool
    {
    }
}
