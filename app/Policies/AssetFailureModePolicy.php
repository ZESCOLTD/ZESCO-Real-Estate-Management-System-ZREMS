<?php

namespace App\Policies;

use App\Models\AssetFailureMode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetFailureModePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AssetFailureMode $assetFailureMode): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AssetFailureMode $assetFailureMode): bool
    {
    }

    public function delete(User $user, AssetFailureMode $assetFailureMode): bool
    {
    }

    public function restore(User $user, AssetFailureMode $assetFailureMode): bool
    {
    }

    public function forceDelete(User $user, AssetFailureMode $assetFailureMode): bool
    {
    }
}
