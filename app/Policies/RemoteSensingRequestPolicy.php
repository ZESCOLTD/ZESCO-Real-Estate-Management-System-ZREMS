<?php

namespace App\Policies;

use App\Models\RemoteSensingRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RemoteSensingRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, RemoteSensingRequest $remoteSensingRequest): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, RemoteSensingRequest $remoteSensingRequest): bool
    {
    }

    public function delete(User $user, RemoteSensingRequest $remoteSensingRequest): bool
    {
    }

    public function restore(User $user, RemoteSensingRequest $remoteSensingRequest): bool
    {
    }

    public function forceDelete(User $user, RemoteSensingRequest $remoteSensingRequest): bool
    {
    }
}
