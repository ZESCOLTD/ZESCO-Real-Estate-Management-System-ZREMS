<?php

namespace App\Policies;

use App\Models\AcquisitionDueDiligence;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcquisitionDueDiligencePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, AcquisitionDueDiligence $acquisitionDueDiligence): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, AcquisitionDueDiligence $acquisitionDueDiligence): bool
    {
    }

    public function delete(User $user, AcquisitionDueDiligence $acquisitionDueDiligence): bool
    {
    }

    public function restore(User $user, AcquisitionDueDiligence $acquisitionDueDiligence): bool
    {
    }

    public function forceDelete(User $user, AcquisitionDueDiligence $acquisitionDueDiligence): bool
    {
    }
}
