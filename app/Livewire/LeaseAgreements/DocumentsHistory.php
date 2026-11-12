<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseLandlordApplication;
use App\Models\LeaseLifecycleCase;
use App\Models\LeaseTenantWorkflow;
use App\Models\TenantDocument;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentsHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $activeView = 'documents';
    public string $search = '';
    public string $source = '';

    private const DOCUMENT_SOURCES = [
        'tenant_workflow' => LeaseTenantWorkflow::class,
        'renewal' => LeaseLifecycleCase::class,
        'termination' => LeaseLifecycleCase::class,
        'handover' => LeaseLifecycleCase::class,
        'landlord_application' => LeaseLandlordApplication::class,
        'lease_record' => LeaseAgreement::class,
    ];

    public function mount(): void
    {
        $this->authorize('lease_tenant_workflow.view');
    }

    public function setView(string $view): void
    {
        abort_unless(in_array($view, ['documents', 'history'], true), 404);
        $this->activeView = $view;
        if ($view === 'history' && $this->source === 'lease_record') {
            $this->source = '';
        }
        $this->resetPage('documentsPage');
        $this->resetPage('historyPage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('documentsPage');
        $this->resetPage('historyPage');
    }

    public function updatingSource(): void
    {
        $this->resetPage('documentsPage');
        $this->resetPage('historyPage');
    }

    public function documentRecordLabel(TenantDocument $document): string
    {
        $owner = $document->documentable;

        return match (true) {
            $owner instanceof LeaseTenantWorkflow => $owner->leaseAgreement?->lease_number . ' · ' . $owner->leaseAgreement?->landlord_name,
            $owner instanceof LeaseLifecycleCase => $owner->leaseAgreement?->lease_number . ' · ' . ucfirst($owner->case_type),
            $owner instanceof LeaseLandlordApplication => $owner->reference_no . ' · ' . $owner->external_tenant_name,
            $owner instanceof LeaseAgreement => $owner->lease_number . ' · ' . $owner->landlord_name,
            default => 'Lease record',
        };
    }

    public function documentSourceLabel(TenantDocument $document): string
    {
        return match (true) {
            $document->documentable instanceof LeaseTenantWorkflow => 'Tenant lease process',
            $document->documentable instanceof LeaseLifecycleCase => ucfirst($document->documentable->case_type),
            $document->documentable instanceof LeaseLandlordApplication => 'Landlord application',
            $document->documentable instanceof LeaseAgreement => 'Lease agreement',
            default => 'Lease record',
        };
    }

    public function render()
    {
        $this->authorize('lease_tenant_workflow.view');

        $documents = TenantDocument::query()
            ->whereIn('documentable_type', $this->allowedDocumentTypes())
            ->with([
                'uploader',
                'documentable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    LeaseTenantWorkflow::class => ['leaseAgreement'],
                    LeaseLifecycleCase::class => ['leaseAgreement'],
                ]),
            ])
            ->when($this->source !== '', function ($query): void {
                if ($this->source === 'renewal' || $this->source === 'termination' || $this->source === 'handover') {
                    $query->where('documentable_type', (new LeaseLifecycleCase())->getMorphClass())
                        ->whereHasMorph('documentable', [LeaseLifecycleCase::class], fn ($caseQuery) => $caseQuery->where('case_type', $this->source));
                } elseif (isset(self::DOCUMENT_SOURCES[$this->source])) {
                    $query->where('documentable_type', (new (self::DOCUMENT_SOURCES[$this->source]))->getMorphClass());
                }
            })
            ->when(trim($this->search) !== '', function ($query): void {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($term): void {
                    $query->where('original_name', 'like', $term)
                        ->orWhere('document_type', 'like', $term)
                        ->orWhere('review_comment', 'like', $term)
                        ->orWhereHasMorph(
                            'documentable',
                            [LeaseTenantWorkflow::class, LeaseLifecycleCase::class, LeaseLandlordApplication::class, LeaseAgreement::class],
                            function ($ownerQuery, string $ownerType) use ($term): void {
                                if ($ownerType === LeaseTenantWorkflow::class || $ownerType === LeaseLifecycleCase::class) {
                                    $ownerQuery->whereHas('leaseAgreement', function ($leaseQuery) use ($term): void {
                                        $leaseQuery->where('lease_number', 'like', $term)
                                            ->orWhere('landlord_name', 'like', $term);
                                    });
                                } elseif ($ownerType === LeaseLandlordApplication::class) {
                                    $ownerQuery->where('reference_no', 'like', $term)
                                        ->orWhere('external_tenant_name', 'like', $term);
                                } else {
                                    $ownerQuery->where('lease_number', 'like', $term)
                                        ->orWhere('landlord_name', 'like', $term);
                                }
                            }
                        );
                });
            })
            ->latest('uploaded_at')
            ->paginate(20, ['*'], 'documentsPage');

        $history = $this->historyQuery()
            ->when($this->source !== '', fn ($query) => $query->where('source', $this->historySource($this->source)))
            ->when(trim($this->search) !== '', function ($query): void {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($query) use ($term): void {
                    $query->where('record_reference', 'like', $term)
                        ->orWhere('record_name', 'like', $term)
                        ->orWhere('action', 'like', $term)
                        ->orWhere('comment', 'like', $term)
                        ->orWhere('actor_name', 'like', $term);
                });
            })
            ->orderByDesc('occurred_at')
            ->paginate(25, ['*'], 'historyPage');

        return view('livewire.lease-agreements.documents-history', [
            'documents' => $documents,
            'history' => $history,
        ])->layout('layouts.master', ['title' => 'Lease Documents & History']);
    }

    private function allowedDocumentTypes(): array
    {
        return collect(self::DOCUMENT_SOURCES)
            ->map(fn (string $class) => (new $class())->getMorphClass())
            ->unique()
            ->values()
            ->all();
    }

    private function historySource(string $filter): ?string
    {
        return match ($filter) {
            'tenant_workflow' => 'Tenant workflow',
            'renewal' => 'Renewal',
            'termination' => 'Termination',
            'handover' => 'Handover',
            'landlord_application' => 'Landlord application',
            default => null,
        };
    }

    private function historyQuery()
    {
        $tenantEvents = DB::table('lease_tenant_workflow_events as event')
            ->join('lease_tenant_workflows as workflow', 'workflow.id', '=', 'event.lease_tenant_workflow_id')
            ->join('lease_agreements as lease', 'lease.id', '=', 'workflow.lease_agreement_id')
            ->leftJoin('users as actor', 'actor.id', '=', 'event.acted_by')
            ->where('lease.zesco_role', 'tenant')
            ->select([
                DB::raw("'Tenant workflow' as source"),
                'lease.lease_number as record_reference',
                'lease.landlord_name as record_name',
                'event.action',
                'event.comment',
                'actor.name as actor_name',
                'event.acted_at as occurred_at',
            ]);

        $lifecycleEvents = DB::table('lease_lifecycle_events as event')
            ->join('lease_lifecycle_cases as lifecycle', 'lifecycle.id', '=', 'event.lease_lifecycle_case_id')
            ->join('lease_agreements as lease', 'lease.id', '=', 'lifecycle.lease_agreement_id')
            ->leftJoin('users as actor', 'actor.id', '=', 'event.actor_id')
            ->where('lease.zesco_role', 'tenant')
            ->select([
                DB::raw('CASE lifecycle.case_type WHEN \'renewal\' THEN \'Renewal\' WHEN \'termination\' THEN \'Termination\' ELSE \'Handover\' END as source'),
                'lease.lease_number as record_reference',
                'lease.landlord_name as record_name',
                'event.action',
                'event.comment',
                'actor.name as actor_name',
                'event.occurred_at',
            ]);

        $landlordEvents = DB::table('lease_landlord_application_events as event')
            ->join('lease_landlord_applications as application', 'application.id', '=', 'event.lease_landlord_application_id')
            ->leftJoin('users as actor', 'actor.id', '=', 'event.actor_id')
            ->select([
                DB::raw("'Landlord application' as source"),
                'application.reference_no as record_reference',
                'application.external_tenant_name as record_name',
                'event.action',
                'event.comment',
                'actor.name as actor_name',
                'event.occurred_at',
            ]);

        return DB::query()->fromSub(
            $tenantEvents->unionAll($lifecycleEvents)->unionAll($landlordEvents),
            'lease_history'
        );
    }
}