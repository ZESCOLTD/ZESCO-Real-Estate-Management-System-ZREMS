<?php

namespace App\Policies;

use App\Models\SupplierView;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierViewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, SupplierView $supplierView): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, SupplierView $supplierView): bool
    {
    }

    public function delete(User $user, SupplierView $supplierView): bool
    {
    }

    public function restore(User $user, SupplierView $supplierView): bool
    {
    }

    public function forceDelete(User $user, SupplierView $supplierView): bool
    {
    }
}
