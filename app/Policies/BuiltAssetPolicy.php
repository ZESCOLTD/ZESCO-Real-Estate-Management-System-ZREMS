<?php

namespace App\Policies;

use App\Models\BuiltAsset;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuiltAssetPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, BuiltAsset $builtAsset): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, BuiltAsset $builtAsset): bool
    {
    }

    public function delete(User $user, BuiltAsset $builtAsset): bool
    {
    }

    public function restore(User $user, BuiltAsset $builtAsset): bool
    {
    }

    public function forceDelete(User $user, BuiltAsset $builtAsset): bool
    {
    }
}
