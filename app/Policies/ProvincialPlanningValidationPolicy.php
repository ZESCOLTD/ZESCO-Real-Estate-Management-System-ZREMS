<?php

namespace App\Policies;

use App\Models\ProvincialPlanningValidation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProvincialPlanningValidationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ProvincialPlanningValidation $provincialPlanningValidation): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ProvincialPlanningValidation $provincialPlanningValidation): bool
    {
    }

    public function delete(User $user, ProvincialPlanningValidation $provincialPlanningValidation): bool
    {
    }

    public function restore(User $user, ProvincialPlanningValidation $provincialPlanningValidation): bool
    {
    }

    public function forceDelete(User $user, ProvincialPlanningValidation $provincialPlanningValidation): bool
    {
    }
}
