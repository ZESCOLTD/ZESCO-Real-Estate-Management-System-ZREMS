<?php

namespace App\Policies;

use App\Models\PurchaseOfficeSelectionView;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseOfficeSelectionViewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, PurchaseOfficeSelectionView $purchaseOfficeSelectionView): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, PurchaseOfficeSelectionView $purchaseOfficeSelectionView): bool
    {
    }

    public function delete(User $user, PurchaseOfficeSelectionView $purchaseOfficeSelectionView): bool
    {
    }

    public function restore(User $user, PurchaseOfficeSelectionView $purchaseOfficeSelectionView): bool
    {
    }

    public function forceDelete(User $user, PurchaseOfficeSelectionView $purchaseOfficeSelectionView): bool
    {
    }
}
