<?php

namespace App\Services;

use App\Models\LeaseLandlordApplication;
use App\Models\LeaseLandlordApplicationEvent;
use App\Models\LeaseAgreement;
use App\Models\LeaseStatus;
use App\Notifications\LeaseWorkflowUpdateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaseLandlordApplicationService
{
    private const ACTIONS = [
        'tenant_feedback_received' => [
            'from' => [
                LeaseLandlordApplication::STATUS_TENANT_REVIEW,
                LeaseLandlordApplication::STATUS_SUBMITTED,
            ],
            'to' => LeaseLandlordApplication::STATUS_TENANT_FEEDBACK,
        ],
        'revision_sent_to_tenant' => [
            'from' => [LeaseLandlordApplication::STATUS_TENANT_FEEDBACK],
            'to' => LeaseLandlordApplication::STATUS_TENANT_REVIEW,
        ],
        'tenant_confirmed_draft' => [
            'from' => [LeaseLandlordApplication::STATUS_TENANT_FEEDBACK],
            'to' => LeaseLandlordApplication::STATUS_LEGAL_REVIEW,
        ],
        'ag_finalization_received' => [
            'from' => [LeaseLandlordApplication::STATUS_AG_REVIEW],
            'to' => LeaseLandlordApplication::STATUS_AG_FINALIZED,
        ],
    ];

    public function create(array $data, bool $submit): LeaseLandlordApplication
    {
        return DB::transaction(function () use ($data, $submit): LeaseLandlordApplication {
            if (! in_array($data['origin'] ?? null, [
                LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
                LeaseLandlordApplication::ORIGIN_ZESCO_OFFER,
            ], true)) {
                throw ValidationException::withMessages([
                    'origin' => 'Choose whether this started as an external request or a ZESCO offer.',
                ]);
            }

            $status = $submit
                ? LeaseLandlordApplication::STATUS_TENANT_REVIEW
                : LeaseLandlordApplication::STATUS_DRAFT;

            $application = LeaseLandlordApplication::create([
                ...$data,
                'status' => $status,
                'created_by' => Auth::id(),
                'submitted_at' => $submit ? now() : null,
            ]);

            $application->forceFill([
                'reference_no' => sprintf('ZLA-%06d', $application->id),
            ])->saveQuietly();

            LeaseLandlordApplicationEvent::create([
                'lease_landlord_application_id' => $application->id,
                'action' => $submit ? 'sent_to_tenant' : 'draft_saved',
                'from_status' => null,
                'to_status' => $status,
                'comment' => $application->proposal_notes,
                'actor_id' => Auth::id(),
                'occurred_at' => now(),
            ]);

            $initiator = $application->creator;
            if ($initiator && (int) $initiator->id !== (int) Auth::id()) {
                $initiator->notify((new LeaseWorkflowUpdateNotification(
                    $application->reference_no,
                    'Landlord lease application updated',
                    'A ZESCO officer recorded the stage "' . str_replace('_', ' ', $recordedAction) . '".',
                    route('lease_agreements.new.landlord', ['application' => $application->id])
                ))->afterCommit());
            }

            return $application->fresh(['events.actor']);
        });
    }

    public function updateDraft(LeaseLandlordApplication $application, array $data, bool $submit): LeaseLandlordApplication
    {
        return DB::transaction(function () use ($application, $data, $submit): LeaseLandlordApplication {
            $application = LeaseLandlordApplication::query()->lockForUpdate()->findOrFail($application->id);

            if ($application->status !== LeaseLandlordApplication::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'application' => 'Only draft landlord applications can be changed.',
                ]);
            }

            $fromStatus = $application->status;
            $toStatus = $submit
                ? LeaseLandlordApplication::STATUS_TENANT_REVIEW
                : LeaseLandlordApplication::STATUS_DRAFT;

            $application->update([
                ...$data,
                'status' => $toStatus,
                'submitted_at' => $submit ? now() : null,
            ]);

            LeaseLandlordApplicationEvent::create([
                'lease_landlord_application_id' => $application->id,
                'action' => $submit ? 'revised_draft_sent_to_tenant' : 'draft_updated',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'comment' => $application->proposal_notes,
                'actor_id' => Auth::id(),
                'occurred_at' => now(),
            ]);

            return $application->fresh(['events.actor']);
        });
    }

    public function recordAction(
        LeaseLandlordApplication $application,
        string $action,
        string $comment
    ): LeaseLandlordApplication {
        return DB::transaction(function () use ($application, $action, $comment): LeaseLandlordApplication {
            $application = LeaseLandlordApplication::query()->lockForUpdate()->findOrFail($application->id);
            $recordedAction = $action;

            if ($action === 'complete_legal_review') {
                if ($application->status !== LeaseLandlordApplication::STATUS_LEGAL_REVIEW) {
                    throw ValidationException::withMessages([
                        'action' => 'Legal review can only be completed at the Legal review stage.',
                    ]);
                }

                if (! in_array($application->proposed_rent_period, LeaseLandlordApplication::RENT_PERIODS, true)
                    || $application->proposed_rent_amount === null) {
                    throw ValidationException::withMessages([
                        'action' => 'Set the rent payment period and amount from the lease before routing Legal review.',
                    ]);
                }

                $sendToAttorneyGeneral = (float) $application->proposed_rent_amount > 600000;
                $transition = [
                    'from' => [LeaseLandlordApplication::STATUS_LEGAL_REVIEW],
                    'to' => $sendToAttorneyGeneral
                        ? LeaseLandlordApplication::STATUS_AG_REVIEW
                        : LeaseLandlordApplication::STATUS_LEGAL_FINALIZED,
                ];
                $recordedAction = $sendToAttorneyGeneral
                    ? 'legal_review_completed_sent_to_ag'
                    : 'legal_review_completed_and_finalized';
            } else {
                $transition = self::ACTIONS[$action] ?? null;
            }

            if (! $transition || ! in_array($application->status, $transition['from'], true)) {
                throw ValidationException::withMessages([
                    'action' => 'That action is not available at the current stage.',
                ]);
            }

            if (trim($comment) === '') {
                throw ValidationException::withMessages([
                    'actionComment' => 'Add a note describing the tenant feedback or handoff.',
                ]);
            }

            $fromStatus = $application->status;
            $toStatus = $transition['to'];

            if (in_array($toStatus, [
                LeaseLandlordApplication::STATUS_LEGAL_FINALIZED,
                LeaseLandlordApplication::STATUS_AG_FINALIZED,
            ], true)) {
                $this->createLandlordLeaseAgreement($application);
            }

            $application->update(['status' => $toStatus]);

            LeaseLandlordApplicationEvent::create([
                'lease_landlord_application_id' => $application->id,
                'action' => $recordedAction,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'comment' => trim($comment),
                'actor_id' => Auth::id(),
                'occurred_at' => now(),
            ]);

            $initiator = $application->creator;
            if ($initiator && (int) $initiator->id !== (int) Auth::id()) {
                $initiator->notify((new LeaseWorkflowUpdateNotification(
                    $application->reference_no,
                    'Landlord lease application updated',
                    'A ZESCO officer recorded the stage "' . str_replace('_', ' ', $recordedAction) . '".',
                    route('lease_agreements.new.landlord', ['application' => $application->id])
                ))->afterCommit());
            }

            return $application->fresh(['events.actor']);
        });
    }

    private function createLandlordLeaseAgreement(LeaseLandlordApplication $application): LeaseAgreement
    {
        if ($application->lease_agreement_id) {
            return LeaseAgreement::query()->findOrFail($application->lease_agreement_id);
        }

        if (! $application->documents()->where('document_type', 'Finalized lease')->exists()) {
            throw ValidationException::withMessages([
                'finalized_document' => 'Upload the finalized lease copy before adding it to the Landlord Lease Register.',
            ]);
        }

        $statusId = LeaseStatus::query()->where('code', 'executed')->value('id');
        if (! $statusId) {
            throw ValidationException::withMessages([
                'lease_status' => 'The Executed lease status is not configured.',
            ]);
        }

        $periodMonths = match ($application->proposed_rent_period) {
            'monthly' => 1,
            '3_months' => 3,
            '6_months' => 6,
            default => null,
        };

        if (! $periodMonths || $application->proposed_rent_amount === null) {
            throw ValidationException::withMessages([
                'rent_amount' => 'A valid payment term and rent amount are required before finalization.',
            ]);
        }

        $rentPerPeriod = (float) $application->proposed_rent_amount;
        $monthlyEquivalent = round($rentPerPeriod / $periodMonths, 2);

        $agreement = LeaseAgreement::create([
            'contract_description' => $application->property_description,
            'landlord_name' => 'ZESCO Limited',
            'tenant_name' => $application->external_tenant_name,
            'tenant_contact_name' => $application->contact_name,
            'tenant_contact_phone' => $application->contact_phone,
            'tenant_contact_email' => $application->contact_email,
            'property_reference' => $application->property_reference,
            'lease_duration' => $application->proposed_start_date && $application->proposed_end_date
                ? $application->proposed_start_date->diffInMonths($application->proposed_end_date) . ' months'
                : null,
            'lease_tenure_years' => $application->proposed_start_date && $application->proposed_end_date
                ? round($application->proposed_start_date->diffInDays($application->proposed_end_date) / 365.25, 2)
                : null,
            'monthly_payment' => $monthlyEquivalent,
            'annual_payment' => round($monthlyEquivalent * 12, 2),
            'payment_period' => $application->proposed_rent_period,
            'payment_amount' => $rentPerPeriod,
            'start_date' => $application->proposed_start_date,
            'end_date' => $application->proposed_end_date,
            'lease_status_id' => $statusId,
            'comment' => 'Finalized from landlord application ' . $application->reference_no,
            'responsible_officer_id' => $application->created_by,
            'zesco_role' => 'landlord',
        ]);

        $application->update(['lease_agreement_id' => $agreement->id]);

        return $agreement;
    }
}