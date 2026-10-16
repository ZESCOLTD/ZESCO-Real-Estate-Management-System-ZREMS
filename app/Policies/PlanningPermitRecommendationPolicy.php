<?php

namespace App\Policies;

use App\Models\PlanningPermitRecommendation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanningPermitRecommendationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, PlanningPermitRecommendation $planningPermitRecommendation): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, PlanningPermitRecommendation $planningPermitRecommendation): bool
    {
    }

    public function delete(User $user, PlanningPermitRecommendation $planningPermitRecommendation): bool
    {
    }

    public function restore(User $user, PlanningPermitRecommendation $planningPermitRecommendation): bool
    {
    }

    public function forceDelete(User $user, PlanningPermitRecommendation $planningPermitRecommendation): bool
    {
    }
}
