<?php

namespace App\Policies;

use App\Models\ParcelDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParcelDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ParcelDocument $parcelDocument): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ParcelDocument $parcelDocument): bool
    {
    }

    public function delete(User $user, ParcelDocument $parcelDocument): bool
    {
    }

    public function restore(User $user, ParcelDocument $parcelDocument): bool
    {
    }

    public function forceDelete(User $user, ParcelDocument $parcelDocument): bool
    {
    }
}
