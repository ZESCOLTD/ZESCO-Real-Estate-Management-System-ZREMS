<?php

namespace App\Policies;

use App\Models\GisLand;
use App\Models\ServiceDepartmentPerson;
use App\Models\ServiceDepartmentSubordinate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GisLandPolicy
{
    use HandlesAuthorization;

    /**
     * Any authenticated user may browse the GIS land list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user may view a specific parcel.
     */
    public function view(User $user, GisLand $land): bool
    {
        return true;
    }

    /**
     * Any authenticated user may create an unlinked parcel.
     * Linked-survey creation is further guarded inside
     * LandCreate::initializeFromLinkedService() and save().
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the creator, or an HOD / subordinate of the linked Geomatics
     * department, may edit a parcel.
     */
    public function update(User $user, GisLand $land): bool
    {
        if ((int) $land->created_by === (int) $user->id) {
            return true;
        }

        return $this->isLinkedDepartmentMember($user, $land);
    }

    /**
     * Only the creator may delete a parcel.
     */
    public function delete(User $user, GisLand $land): bool
    {
        return (int) $land->created_by === (int) $user->id;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function isLinkedDepartmentMember(User $user, GisLand $land): bool
    {
        $service = $land->serviceRequestService;
        if (!$service) {
            return false;
        }

        $deptCode = (string) ($service->department?->department_code ?? '');
        if ($deptCode === '') {
            return false;
        }

        $isHod = ServiceDepartmentPerson::query()
            ->where('department_code', $deptCode)
            ->where('hod_user_id', $user->id)
            ->exists();

        if ($isHod) {
            return true;
        }

        return ServiceDepartmentSubordinate::query()
            ->where('department_code', $deptCode)
            ->where('user_id', $user->id)
            ->exists();
    }
}
