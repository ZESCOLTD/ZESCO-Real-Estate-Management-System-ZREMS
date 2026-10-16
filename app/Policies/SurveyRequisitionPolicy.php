<?php

namespace App\Policies;

use App\Models\SurveyRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SurveyRequisitionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, SurveyRequisition $surveyRequisition): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, SurveyRequisition $surveyRequisition): bool
    {
    }

    public function delete(User $user, SurveyRequisition $surveyRequisition): bool
    {
    }

    public function restore(User $user, SurveyRequisition $surveyRequisition): bool
    {
    }

    public function forceDelete(User $user, SurveyRequisition $surveyRequisition): bool
    {
    }
}
