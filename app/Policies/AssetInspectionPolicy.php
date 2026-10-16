<?php

namespace App\Policies;

use App\Models\AssetInspection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetInspectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AssetInspection $assetInspection): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AssetInspection $assetInspection): bool
    {
    }

    public function delete(User $user, AssetInspection $assetInspection): bool
    {
    }

    public function restore(User $user, AssetInspection $assetInspection): bool
    {
    }

    public function forceDelete(User $user, AssetInspection $assetInspection): bool
    {
    }
}
