<?php

namespace App\Services;

use App\Models\LeaseAgreement;
use App\Models\LeaseTenantWorkflow;
use App\Models\LeaseTenantWorkflowEvent;
use App\Notifications\LeaseWorkflowUpdateNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaseTenantWorkflowService
{
    private const ACTIONS = [
        'department_willing' => [
            'from' => LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION,
            'to' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE,
            'label' => 'Department confirmed it wants to continue',
        ],
        'department_declined' => [
            'from' => LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION,
            'to' => LeaseTenantWorkflow::STATUS_DEPARTMENT_DECLINED,
            'label' => 'Department declined to continue',
        ],
        'landlord_lease_requested' => [
            'from' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE,
            'to' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE,
            'label' => 'Lease requested from landlord',
        ],
        'landlord_lease_received' => [
            'from' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE,
            'to' => LeaseTenantWorkflow::STATUS_LEGAL_REVIEW,
            'label' => 'Lease received from landlord',
        ],
        'legal_review_completed' => [
            'from' => LeaseTenantWorkflow::STATUS_LEGAL_REVIEW,
            'to' => LeaseTenantWorkflow::STATUS_MD_SIGNATURE,
            'label' => 'Legal review completed',
        ],
        'md_signed_and_sent' => [
            'from' => LeaseTenantWorkflow::STATUS_MD_SIGNATURE,
            'to' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_SIGNATURE,
            'label' => 'MD signed; agreement sent to landlord',
        ],
        'countersigned_copy_received' => [
            'from' => LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_SIGNATURE,
            'to' => LeaseTenantWorkflow::STATUS_EXECUTED_COPY_RECEIVED,
            'label' => 'Landlord countersigned copy received',
        ],
    ];

    public function start(LeaseAgreement $lease, string $departmentName): LeaseTenantWorkflow
    {
        if (trim($departmentName) === '') {
            throw ValidationException::withMessages([
                'department_name' => 'Enter the ZESCO department for this lease.',
            ]);
        }

        return DB::transaction(function () use ($lease, $departmentName): LeaseTenantWorkflow {
            $lease = LeaseAgreement::query()->lockForUpdate()->findOrFail($lease->id);

            if ($lease->zesco_role !== 'tenant') {
                throw ValidationException::withMessages([
                    'lease' => 'This process is only available for leases where ZESCO is the tenant.',
                ]);
            }

            $openWorkflow = LeaseTenantWorkflow::query()
                ->where('lease_agreement_id', $lease->id)
                ->whereNotIn('status', [
                    LeaseTenantWorkflow::STATUS_EXECUTED_COPY_RECEIVED,
                    LeaseTenantWorkflow::STATUS_DEPARTMENT_DECLINED,
                ])
                ->exists();

            if ($openWorkflow) {
                throw ValidationException::withMessages([
                    'lease' => 'This lease already has an open tenant-side process.',
                ]);
            }

            $workflow = LeaseTenantWorkflow::create([
                'lease_agreement_id' => $lease->id,
                'department_name' => trim($departmentName),
                'status' => LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION,
                'initiated_by' => Auth::id(),
            ]);

            $this->recordEvent($workflow, 'process_started', null, $workflow->status, null);

            return $workflow->fresh(['leaseAgreement', 'events.actor']);
        });
    }

    public function recordAction(LeaseTenantWorkflow $workflow, string $action, ?string $comment = null): LeaseTenantWorkflow
    {
        return DB::transaction(function () use ($workflow, $action, $comment): LeaseTenantWorkflow {
            $workflow = LeaseTenantWorkflow::query()->lockForUpdate()->findOrFail($workflow->id);
            $transition = self::ACTIONS[$action] ?? null;

            if (! $transition || $workflow->status !== $transition['from']) {
                throw ValidationException::withMessages([
                    'action' => 'That action is not available at the current stage.',
                ]);
            }

            if ($action === 'department_declined' && trim((string) $comment) === '') {
                throw ValidationException::withMessages([
                    'comment' => 'Record why the department does not want to continue.',
                ]);
            }

            $this->recordEvent($workflow, $action, $workflow->status, $transition['to'], $comment);
            $workflow->update([
                'status' => $transition['to'],
                'completed_at' => in_array($transition['to'], [
                    LeaseTenantWorkflow::STATUS_EXECUTED_COPY_RECEIVED,
                    LeaseTenantWorkflow::STATUS_DEPARTMENT_DECLINED,
                ], true) ? now() : null,
            ]);

            $initiator = $workflow->initiator;
            if ($initiator && (int) $initiator->id !== (int) Auth::id()) {
                $initiator->notify((new LeaseWorkflowUpdateNotification(
                    $workflow->leaseAgreement->lease_number,
                    'Lease workflow stage updated',
                    'A ZESCO officer recorded: ' . $transition['label'] . '.',
                    route('lease_agreements.new', ['workflow' => $workflow->id])
                ))->afterCommit());
            }

            return $workflow->fresh(['leaseAgreement', 'events.actor']);
        });
    }

    public function availableActions(LeaseTenantWorkflow $workflow): array
    {
        return collect(self::ACTIONS)
            ->filter(fn (array $transition) => $transition['from'] === $workflow->status)
            ->map(fn (array $transition, string $action) => [
                'action' => $action,
                'label' => $transition['label'],
            ])
            ->values()
            ->all();
    }

    public function recordDocumentUploaded(
        LeaseTenantWorkflow $workflow,
        string $documentType,
        string $originalName
    ): void {
        $status = $workflow->status;
        $this->recordEvent(
            $workflow,
            'document_uploaded',
            $status,
            $status,
            "{$documentType}: {$originalName}"
        );
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION => 'Confirming department willingness',
            LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE => 'Awaiting lease from landlord',
            LeaseTenantWorkflow::STATUS_LEGAL_REVIEW => 'Legal review',
            LeaseTenantWorkflow::STATUS_MD_SIGNATURE => 'Awaiting MD signature',
            LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_SIGNATURE => 'Awaiting landlord signature',
            LeaseTenantWorkflow::STATUS_EXECUTED_COPY_RECEIVED => 'Countersigned copy received',
            LeaseTenantWorkflow::STATUS_DEPARTMENT_DECLINED => 'Department declined to continue',
            default => 'Unknown stage',
        };
    }

    private function recordEvent(
        LeaseTenantWorkflow $workflow,
        string $action,
        ?string $fromStatus,
        string $toStatus,
        ?string $comment
    ): void {
        LeaseTenantWorkflowEvent::create([
            'lease_tenant_workflow_id' => $workflow->id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'comment' => filled($comment) ? trim($comment) : null,
            'acted_by' => Auth::id(),
            'acted_at' => now(),
        ]);
    }
}