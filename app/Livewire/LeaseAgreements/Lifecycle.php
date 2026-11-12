<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseLifecycleCase;
use App\Models\LeaseLifecycleEvent;
use App\Models\TenantDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Lifecycle extends Component
{
    use WithFileUploads;

    public string $caseType = LeaseLifecycleCase::TYPE_RENEWAL;
    public ?int $leaseAgreementId = null;
    public ?int $selectedCaseId = null;
    public string $reason = '';
    public string $proposedStartDate = '';
    public string $proposedEndDate = '';
    public string $proposedMonthlyPayment = '';
    public string $proposedEffectiveDate = '';
    public string $noticeDate = '';
    public array $details = [
        'handover_type' => 'move_in',
        'inspection_date' => '',
        'outgoing_occupant' => '',
        'incoming_occupant' => '',
        'property_condition' => '',
        'meter_readings' => '',
        'keys_and_items' => '',
        'defects_issues' => '',
        'acknowledgements' => '',
        'financial_checks' => '',
        'notes' => '',
    ];
    public string $documentType = 'Supporting document';
    public array $supportingDocuments = [];

    private const CASE_TYPES = [
        LeaseLifecycleCase::TYPE_RENEWAL,
        LeaseLifecycleCase::TYPE_TERMINATION,
        LeaseLifecycleCase::TYPE_HANDOVER,
    ];

    private const DOCUMENT_TYPES = [
        'Supporting document',
        'Renewal proposal',
        'Termination notice',
        'Handover report',
        'Inspection photo',
        'Acknowledgement',
        'Other',
    ];

    public function mount(string $caseType): void
    {
        $this->authorize('lease_tenant_workflow.view');
        abort_unless(in_array($caseType, self::CASE_TYPES, true), 404);
        $this->caseType = $caseType;
    }

    public function submit(): void
    {
        Gate::authorize('lease_tenant_workflow.manage');

        $validated = $this->validate($this->rules());
        $data = [
            'reason' => $this->reason ?: null,
            'proposed_start_date' => $this->proposedStartDate ?: null,
            'proposed_end_date' => $this->proposedEndDate ?: null,
            'proposed_monthly_payment' => $this->proposedMonthlyPayment !== '' ? $this->proposedMonthlyPayment : null,
            'proposed_effective_date' => $this->proposedEffectiveDate ?: null,
            'notice_date' => $this->noticeDate ?: null,
            'details' => $validated['details'] ?? [],
        ];

        $lifecycleCase = app(\App\Services\LeaseLifecycleService::class)->createCase(
            LeaseAgreement::query()->findOrFail($validated['leaseAgreementId']),
            $this->caseType,
            $data,
            true
        );

        $this->selectedCaseId = $lifecycleCase->id;
        session()->flash('success', $this->caseType === LeaseLifecycleCase::TYPE_HANDOVER
            ? 'Handover record saved. The lease status was not changed.'
            : 'Request recorded for tracking. No lease terms or status were changed.');
        $this->resetForm();
    }

    public function openCase(int $caseId): void
    {
        $this->authorize('lease_tenant_workflow.view');

        $this->selectedCaseId = LeaseLifecycleCase::query()
            ->where('case_type', $this->caseType)
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->findOrFail($caseId)
            ->id;
    }

    public function uploadDocuments(): void
    {
        Gate::authorize('lease_tenant_workflow.manage');
        abort_unless($this->selectedCaseId, 404);

        $validated = $this->validate([
            'documentType' => ['required', Rule::in(self::DOCUMENT_TYPES)],
            'supportingDocuments' => ['required', 'array', 'max:10'],
            'supportingDocuments.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $lifecycleCase = LeaseLifecycleCase::query()
            ->where('case_type', $this->caseType)
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->findOrFail($this->selectedCaseId);

        foreach ($validated['supportingDocuments'] as $file) {
            $path = $file->store('lease-lifecycle/' . $lifecycleCase->id, 'private');

            try {
                DB::transaction(function () use ($lifecycleCase, $file, $path): void {
                    $document = TenantDocument::create([
                        'documentable_type' => $lifecycleCase->getMorphClass(),
                        'documentable_id' => $lifecycleCase->id,
                        'document_type' => $this->documentType,
                        'original_name' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'review_status' => 'Not Reviewed',
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);

                    LeaseLifecycleEvent::create([
                        'lease_lifecycle_case_id' => $lifecycleCase->id,
                        'action' => 'document_uploaded',
                        'from_status' => $lifecycleCase->status,
                        'to_status' => $lifecycleCase->status,
                        'comment' => $document->document_type . ': ' . $document->original_name,
                        'actor_id' => auth()->id(),
                        'occurred_at' => now(),
                    ]);
                });
            } catch (Throwable $exception) {
                Storage::disk('private')->delete($path);
                throw $exception;
            }
        }

        $this->reset('supportingDocuments');
        session()->flash('success', 'Lifecycle document uploaded privately.');
    }

    public function render()
    {
        $this->authorize('lease_tenant_workflow.view');

        $cases = LeaseLifecycleCase::query()
            ->with(['leaseAgreement', 'creator'])
            ->where('case_type', $this->caseType)
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->latest('created_at')
            ->limit(100)
            ->get();

        $selectedCase = $this->selectedCaseId
            ? LeaseLifecycleCase::query()
                ->with(['leaseAgreement', 'events.actor', 'documents.uploader'])
                ->where('case_type', $this->caseType)
                ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
                ->find($this->selectedCaseId)
            : null;

        return view('livewire.lease-agreements.lifecycle', [
            'leases' => LeaseAgreement::query()
                ->where('zesco_role', 'tenant')
                ->orderBy('lease_number')
                ->get(),
            'cases' => $cases,
            'selectedCase' => $selectedCase,
            'caseTitle' => $this->caseTitle(),
            'documentTypes' => self::DOCUMENT_TYPES,
        ])->layout('layouts.master', ['title' => $this->caseTitle()]);
    }

    private function rules(): array
    {
        $rules = [
            'leaseAgreementId' => [
                'required',
                'integer',
                Rule::exists('lease_agreements', 'id')->where('zesco_role', 'tenant'),
            ],
        ];

        if ($this->caseType === LeaseLifecycleCase::TYPE_RENEWAL) {
            return $rules + [
                'reason' => ['required', 'string', 'max:5000'],
                'proposedStartDate' => ['required', 'date'],
                'proposedEndDate' => ['required', 'date', 'after:proposedStartDate'],
                'proposedMonthlyPayment' => ['nullable', 'numeric', 'min:0'],
            ];
        }

        if ($this->caseType === LeaseLifecycleCase::TYPE_TERMINATION) {
            return $rules + [
                'reason' => ['required', 'string', 'max:5000'],
                'proposedEffectiveDate' => ['required', 'date'],
                'noticeDate' => ['nullable', 'date', 'before_or_equal:proposedEffectiveDate'],
                'details.financial_checks' => ['nullable', 'string', 'max:5000'],
            ];
        }

        return $rules + [
            'details.handover_type' => ['required', Rule::in(['move_in', 'move_out'])],
            'details.inspection_date' => ['required', 'date'],
            'details.property_condition' => ['required', 'string', 'max:5000'],
            'details.outgoing_occupant' => ['nullable', 'string', 'max:255'],
            'details.incoming_occupant' => ['nullable', 'string', 'max:255'],
            'details.meter_readings' => ['nullable', 'string', 'max:3000'],
            'details.keys_and_items' => ['nullable', 'string', 'max:3000'],
            'details.defects_issues' => ['nullable', 'string', 'max:5000'],
            'details.acknowledgements' => ['nullable', 'string', 'max:1000'],
            'details.notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function caseTitle(): string
    {
        return match ($this->caseType) {
            LeaseLifecycleCase::TYPE_RENEWAL => 'Lease Renewals',
            LeaseLifecycleCase::TYPE_TERMINATION => 'Lease Terminations',
            LeaseLifecycleCase::TYPE_HANDOVER => 'Property Handover',
            default => 'Lease Lifecycle',
        };
    }

    private function resetForm(): void
    {
        $this->leaseAgreementId = null;
        $this->reason = '';
        $this->proposedStartDate = '';
        $this->proposedEndDate = '';
        $this->proposedMonthlyPayment = '';
        $this->proposedEffectiveDate = '';
        $this->noticeDate = '';
        $this->details = [
            'handover_type' => 'move_in', 'inspection_date' => '', 'outgoing_occupant' => '',
            'incoming_occupant' => '', 'property_condition' => '', 'meter_readings' => '',
            'keys_and_items' => '', 'defects_issues' => '', 'acknowledgements' => '',
            'financial_checks' => '', 'notes' => '',
        ];
        $this->resetValidation();
    }
}