<?php

namespace App\Policies;

use App\Models\AssetValuation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssetValuationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AssetValuation $assetValuation): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AssetValuation $assetValuation): bool
    {
    }

    public function delete(User $user, AssetValuation $assetValuation): bool
    {
    }

    public function restore(User $user, AssetValuation $assetValuation): bool
    {
    }

    public function forceDelete(User $user, AssetValuation $assetValuation): bool
    {
    }
}
