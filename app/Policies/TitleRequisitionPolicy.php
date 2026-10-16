<?php

namespace App\Policies;

use App\Models\TitleRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TitleRequisitionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, TitleRequisition $titleRequisition): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, TitleRequisition $titleRequisition): bool
    {
    }

    public function delete(User $user, TitleRequisition $titleRequisition): bool
    {
    }

    public function restore(User $user, TitleRequisition $titleRequisition): bool
    {
    }

    public function forceDelete(User $user, TitleRequisition $titleRequisition): bool
    {
    }
}
