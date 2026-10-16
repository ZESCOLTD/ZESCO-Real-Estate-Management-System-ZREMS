<?php

namespace App\Policies;

use App\Models\ComplianceRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComplianceRecordPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, ComplianceRecord $complianceRecord): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, ComplianceRecord $complianceRecord): bool
    {
    }

    public function delete(User $user, ComplianceRecord $complianceRecord): bool
    {
    }

    public function restore(User $user, ComplianceRecord $complianceRecord): bool
    {
    }

    public function forceDelete(User $user, ComplianceRecord $complianceRecord): bool
    {
    }
}
