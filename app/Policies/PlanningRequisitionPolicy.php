<?php

namespace App\Policies;

use App\Models\PlanningRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanningRequisitionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, PlanningRequisition $planningRequisition): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, PlanningRequisition $planningRequisition): bool
    {
    }

    public function delete(User $user, PlanningRequisition $planningRequisition): bool
    {
    }

    public function restore(User $user, PlanningRequisition $planningRequisition): bool
    {
    }

    public function forceDelete(User $user, PlanningRequisition $planningRequisition): bool
    {
    }
}
