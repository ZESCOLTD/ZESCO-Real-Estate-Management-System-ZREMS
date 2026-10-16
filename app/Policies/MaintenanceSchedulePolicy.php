<?php

namespace App\Policies;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
    }

    public function delete(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
    }

    public function restore(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
    }

    public function forceDelete(User $user, MaintenanceSchedule $maintenanceSchedule): bool
    {
    }
}
