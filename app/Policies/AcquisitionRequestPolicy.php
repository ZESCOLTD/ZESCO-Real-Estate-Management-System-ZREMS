<?php

namespace App\Policies;

use App\Models\AcquisitionRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcquisitionRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AcquisitionRequest $acquisitionRequest): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AcquisitionRequest $acquisitionRequest): bool
    {
    }

    public function delete(User $user, AcquisitionRequest $acquisitionRequest): bool
    {
    }

    public function restore(User $user, AcquisitionRequest $acquisitionRequest): bool
    {
    }

    public function forceDelete(User $user, AcquisitionRequest $acquisitionRequest): bool
    {
    }
}
