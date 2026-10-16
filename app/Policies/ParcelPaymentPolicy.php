<?php

namespace App\Policies;

use App\Models\ParcelPayment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParcelPaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ParcelPayment $parcelPayment): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ParcelPayment $parcelPayment): bool
    {
    }

    public function delete(User $user, ParcelPayment $parcelPayment): bool
    {
    }

    public function restore(User $user, ParcelPayment $parcelPayment): bool
    {
    }

    public function forceDelete(User $user, ParcelPayment $parcelPayment): bool
    {
    }
}
