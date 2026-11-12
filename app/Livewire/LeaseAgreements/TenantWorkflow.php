<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\TenantDocument;
use App\Models\LeaseTenantWorkflow;
use App\Services\LeaseTenantWorkflowService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class TenantWorkflow extends Component
{
    use WithFileUploads;

    public ?int $leaseAgreementId = null;
    public ?int $selectedWorkflowId = null;
    public string $departmentName = '';
    public string $comment = '';
    public string $documentType = 'Landlord lease';
    public array $supportingDocuments = [];

    private const DOCUMENT_TYPES = [
        'Landlord lease',
        'Legal review',
        'MD-signed lease',
        'Countersigned lease',
        'Correspondence',
        'Other',
    ];

    public function mount(): void
    {
        $this->authorize('lease_tenant_workflow.view');

        $workflowId = request()->integer('workflow');
        if ($workflowId > 0) {
            $this->openWorkflow($workflowId);
        }
    }

    public function startProcess(): void
    {
        Gate::authorize('lease_tenant_workflow.manage');

        $validated = $this->validate([
            'leaseAgreementId' => [
                'required',
                'integer',
                Rule::exists('lease_agreements', 'id')->where('zesco_role', 'tenant'),
            ],
            'departmentName' => ['required', 'string', 'max:255'],
        ]);

        $workflow = app(LeaseTenantWorkflowService::class)->start(
            LeaseAgreement::query()->findOrFail($validated['leaseAgreementId']),
            $validated['departmentName']
        );

        $this->selectedWorkflowId = $workflow->id;
        $this->comment = '';
        session()->flash('success', 'Tenant-side lease process started. The Lease Register record was not changed.');
    }

    public function openWorkflow(int $workflowId): void
    {
        $this->authorize('lease_tenant_workflow.view');

        $this->selectedWorkflowId = LeaseTenantWorkflow::query()
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->findOrFail($workflowId)
            ->id;
    }

    public function recordAction(string $action): void
    {
        Gate::authorize('lease_tenant_workflow.manage');

        abort_unless($this->selectedWorkflowId, 404);

        $workflow = LeaseTenantWorkflow::query()
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->findOrFail($this->selectedWorkflowId);

        app(LeaseTenantWorkflowService::class)->recordAction($workflow, $action, $this->comment);
        $this->comment = '';
        session()->flash('success', 'Lease process updated. The Lease Register status remains unchanged.');
    }

    public function uploadDocuments(): void
    {
        Gate::authorize('lease_tenant_workflow.manage');

        abort_unless($this->selectedWorkflowId, 404);

        $validated = $this->validate([
            'documentType' => ['required', Rule::in(self::DOCUMENT_TYPES)],
            'supportingDocuments' => ['required', 'array', 'max:10'],
            'supportingDocuments.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $workflow = LeaseTenantWorkflow::query()
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->findOrFail($this->selectedWorkflowId);

        foreach ($validated['supportingDocuments'] as $file) {
            $path = $file->store('lease-workflows/' . $workflow->id, 'private');

            try {
                DB::transaction(function () use ($workflow, $file, $path): void {
                    $document = TenantDocument::create([
                        'documentable_type' => $workflow->getMorphClass(),
                        'documentable_id' => $workflow->id,
                        'document_type' => $this->documentType,
                        'original_name' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'review_status' => 'Not Reviewed',
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);

                    app(LeaseTenantWorkflowService::class)->recordDocumentUploaded(
                        $workflow,
                        $document->document_type,
                        $document->original_name
                    );
                });
            } catch (Throwable $exception) {
                Storage::disk('private')->delete($path);
                throw $exception;
            }
        }

        $this->reset('supportingDocuments');
        session()->flash('success', 'Lease document uploaded to private storage.');
    }

    public function render()
    {
        $this->authorize('lease_tenant_workflow.view');

        $workflows = LeaseTenantWorkflow::query()
            ->with(['leaseAgreement.leaseStatus', 'initiator'])
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->latest('updated_at')
            ->limit(100)
            ->get();

        $selectedWorkflow = $this->selectedWorkflowId
            ? LeaseTenantWorkflow::query()
                ->with(['leaseAgreement.leaseStatus', 'events.actor', 'documents.uploader'])
                ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
                ->find($this->selectedWorkflowId)
            : null;

        return view('livewire.lease-agreements.tenant-workflow', [
            'leases' => LeaseAgreement::query()
                ->with('leaseStatus')
                ->where('zesco_role', 'tenant')
                ->orderBy('lease_number')
                ->get(),
            'workflows' => $workflows,
            'selectedWorkflow' => $selectedWorkflow,
            'availableActions' => $selectedWorkflow
                ? app(LeaseTenantWorkflowService::class)->availableActions($selectedWorkflow)
                : [],
            'documentTypes' => self::DOCUMENT_TYPES,
        ])->layout('layouts.master', ['title' => 'New Lease']);
    }
}