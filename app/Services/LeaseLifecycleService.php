<?php

namespace App\Services;

use App\Models\LeaseAgreement;
use App\Models\LeaseLifecycleCase;
use App\Models\LeaseLifecycleEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaseLifecycleService
{
    private const CASE_TYPES = [
        LeaseLifecycleCase::TYPE_RENEWAL,
        LeaseLifecycleCase::TYPE_TERMINATION,
        LeaseLifecycleCase::TYPE_HANDOVER,
    ];

    public function createCase(LeaseAgreement $lease, string $caseType, array $data, bool $submit): LeaseLifecycleCase
    {
        return DB::transaction(function () use ($lease, $caseType, $data, $submit): LeaseLifecycleCase {
            $lease = LeaseAgreement::query()->lockForUpdate()->findOrFail($lease->id);

            if ($lease->zesco_role !== 'tenant') {
                throw ValidationException::withMessages([
                    'lease_agreement_id' => 'Renewals, terminations, and handovers are currently limited to ZESCO-as-tenant leases.',
                ]);
            }

            if (! in_array($caseType, self::CASE_TYPES, true)) {
                throw ValidationException::withMessages([
                    'case_type' => 'Choose a supported lease lifecycle process.',
                ]);
            }

            $isHandover = $caseType === LeaseLifecycleCase::TYPE_HANDOVER;
            $status = $submit
                ? ($isHandover ? LeaseLifecycleCase::STATUS_RECORDED : LeaseLifecycleCase::STATUS_SUBMITTED)
                : LeaseLifecycleCase::STATUS_DRAFT;

            $lifecycleCase = LeaseLifecycleCase::create([
                'lease_agreement_id' => $lease->id,
                'case_type' => $caseType,
                'status' => $status,
                'reason' => $data['reason'] ?? null,
                'proposed_start_date' => $data['proposed_start_date'] ?? null,
                'proposed_end_date' => $data['proposed_end_date'] ?? null,
                'proposed_monthly_payment' => $data['proposed_monthly_payment'] ?? null,
                'proposed_effective_date' => $data['proposed_effective_date'] ?? null,
                'notice_date' => $data['notice_date'] ?? null,
                'details' => $data['details'] ?? [],
                'created_by' => Auth::id(),
                'submitted_at' => $submit ? now() : null,
            ]);

            LeaseLifecycleEvent::create([
                'lease_lifecycle_case_id' => $lifecycleCase->id,
                'action' => match (true) {
                    ! $submit => 'draft_saved',
                    $isHandover => 'handover_recorded',
                    default => $caseType . '_requested',
                },
                'from_status' => null,
                'to_status' => $status,
                'comment' => $data['reason'] ?? null,
                'actor_id' => Auth::id(),
                'occurred_at' => now(),
            ]);

            return $lifecycleCase->fresh(['leaseAgreement', 'events.actor']);
        });
    }
}