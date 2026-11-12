<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseLandlordApplication;
use App\Models\LeaseLandlordApplicationEvent;
use App\Models\TenantDocument;
use App\Services\LeaseLandlordApplicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class LandlordApplication extends Component
{
    use WithFileUploads;

    public string $origin = '';
    public string $externalTenantName = '';
    public string $contactName = '';
    public string $contactPhone = '';
    public string $contactEmail = '';
    public string $propertyReference = '';
    public string $propertyDescription = '';
    public string $proposedStartDate = '';
    public string $proposedEndDate = '';
    public string $proposedRentPeriod = 'monthly';
    public string $proposedRentAmount = '';
    public string $proposalNotes = '';
    public string $actionComment = '';
    public ?int $selectedApplicationId = null;
    public string $documentType = 'Supporting document';
    public array $supportingDocuments = [];

    private const DOCUMENT_TYPES = [
        'Supporting document',
        'Tenant request',
        'Property offer',
        'Proposed lease',
        'Finalized lease',
        'Other',
    ];

    public function mount(): void
    {
        $this->authorize('lease_tenant_workflow.view');

        $applicationId = request()->integer('application');
        if ($applicationId > 0) {
            $this->openApplication($applicationId);
        }
    }

    public function saveDraft(): void
    {
        $this->save(false);
    }

    public function submitForTracking(): void
    {
        $this->save(true);
    }

    public function openApplication(int $applicationId): void
    {
        $this->authorize('lease_tenant_workflow.view');
        $application = LeaseLandlordApplication::query()->findOrFail($applicationId);
        $this->selectedApplicationId = $application->id;
        $this->resetForm();

        if ($application->status === LeaseLandlordApplication::STATUS_DRAFT) {
            $this->origin = $application->origin;
            $this->externalTenantName = $application->external_tenant_name;
            $this->contactName = $application->contact_name ?? '';
            $this->contactPhone = $application->contact_phone ?? '';
            $this->contactEmail = $application->contact_email ?? '';
            $this->propertyReference = $application->property_reference ?? '';
            $this->propertyDescription = $application->property_description;
            $this->proposedStartDate = $application->proposed_start_date?->format('Y-m-d') ?? '';
            $this->proposedEndDate = $application->proposed_end_date?->format('Y-m-d') ?? '';
            $this->proposedRentPeriod = $application->proposed_rent_period
                ?? ($application->proposed_monthly_rent !== null ? 'monthly' : '');
            $this->proposedRentAmount = $application->proposed_rent_amount
                ?? $application->proposed_monthly_rent
                ?? '';
            $this->proposalNotes = $application->proposal_notes ?? '';
        }
    }

    public function startNew(): void
    {
        $this->selectedApplicationId = null;
        $this->resetForm();
    }

    public function recordAction(string $action): void
    {
        Gate::authorize('lease_tenant_workflow.manage');
        abort_unless($this->selectedApplicationId, 404);

        $validated = $this->validate([
            'actionComment' => ['required', 'string', 'max:5000'],
        ]);

        $application = LeaseLandlordApplication::query()->findOrFail($this->selectedApplicationId);
        $application = app(LeaseLandlordApplicationService::class)->recordAction(
            $application,
            $action,
            $validated['actionComment']
        );

        $this->actionComment = '';
        session()->flash('success', match ($action) {
            'tenant_feedback_received' => 'Tenant feedback recorded.',
            'revision_sent_to_tenant' => 'Revised draft recorded as sent to the tenant.',
            'tenant_confirmed_draft' => 'Tenant confirmation recorded. The draft is now at ZESCO Legal review.',
            default => 'Application stage updated.',
        });
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            LeaseLandlordApplication::STATUS_DRAFT => 'Draft',
            LeaseLandlordApplication::STATUS_SUBMITTED,
            LeaseLandlordApplication::STATUS_TENANT_REVIEW => 'Tenant review',
            LeaseLandlordApplication::STATUS_TENANT_FEEDBACK => 'Tenant feedback received',
            LeaseLandlordApplication::STATUS_LEGAL_REVIEW => 'ZESCO Legal review',
            LeaseLandlordApplication::STATUS_AG_REVIEW => 'Attorney General review',
            LeaseLandlordApplication::STATUS_LEGAL_FINALIZED => 'Finalized by ZESCO Legal',
            LeaseLandlordApplication::STATUS_AG_FINALIZED => 'Finalized by Attorney General',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public function uploadDocuments(): void
    {
        Gate::authorize('lease_tenant_workflow.manage');
        abort_unless($this->selectedApplicationId, 404);

        $validated = $this->validate([
            'documentType' => ['required', Rule::in(self::DOCUMENT_TYPES)],
            'supportingDocuments' => ['required', 'array', 'max:10'],
            'supportingDocuments.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $application = LeaseLandlordApplication::query()->findOrFail($this->selectedApplicationId);

        foreach ($validated['supportingDocuments'] as $file) {
            $path = $file->store('lease-landlord-applications/' . $application->id, 'private');

            try {
                DB::transaction(function () use ($application, $file, $path): void {
                    $document = TenantDocument::create([
                        'documentable_type' => $application->getMorphClass(),
                        'documentable_id' => $application->id,
                        'document_type' => $this->documentType,
                        'original_name' => $file->getClientOriginalName(),
                        'storage_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'review_status' => 'Not Reviewed',
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                    ]);

                    LeaseLandlordApplicationEvent::create([
                        'lease_landlord_application_id' => $application->id,
                        'action' => 'document_uploaded',
                        'from_status' => $application->status,
                        'to_status' => $application->status,
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
        session()->flash('success', 'Application document uploaded privately.');
    }

    public function render()
    {
        $this->authorize('lease_tenant_workflow.view');

        $applications = LeaseLandlordApplication::query()
            ->with('creator')
            ->latest('created_at')
            ->limit(100)
            ->get();

        $selectedApplication = $this->selectedApplicationId
            ? LeaseLandlordApplication::query()
                ->with(['events.actor', 'documents.uploader'])
                ->find($this->selectedApplicationId)
            : null;

        return view('livewire.lease-agreements.landlord-application', [
            'applications' => $applications,
            'selectedApplication' => $selectedApplication,
            'documentTypes' => self::DOCUMENT_TYPES,
        ])->layout('layouts.master', ['title' => 'New Lease · ZESCO as Landlord']);
    }

    private function save(bool $submit): void
    {
        Gate::authorize('lease_tenant_workflow.manage');

        $validated = $this->validate([
            'origin' => ['required', Rule::in([
                LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
                LeaseLandlordApplication::ORIGIN_ZESCO_OFFER,
            ])],
            'externalTenantName' => ['required', 'string', 'max:255'],
            'contactName' => ['nullable', 'string', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:30'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'propertyReference' => ['nullable', 'string', 'max:255'],
            'propertyDescription' => ['required', 'string', 'max:5000'],
            'proposedStartDate' => ['nullable', 'date', 'required_with:proposedEndDate'],
            'proposedEndDate' => ['nullable', 'date', 'after:proposedStartDate'],
            'proposedRentPeriod' => [$submit ? 'required' : 'nullable', Rule::in(LeaseLandlordApplication::RENT_PERIODS)],
            'proposedRentAmount' => [$submit ? 'required' : 'nullable', 'numeric', 'min:0'],
            'proposalNotes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = [
            'origin' => $validated['origin'],
            'external_tenant_name' => $validated['externalTenantName'],
            'contact_name' => $validated['contactName'] ?: null,
            'contact_phone' => $validated['contactPhone'] ?: null,
            'contact_email' => $validated['contactEmail'] ?: null,
            'property_reference' => $validated['propertyReference'] ?: null,
            'property_description' => $validated['propertyDescription'],
            'proposed_start_date' => $validated['proposedStartDate'] ?: null,
            'proposed_end_date' => $validated['proposedEndDate'] ?: null,
            'proposed_rent_period' => $validated['proposedRentPeriod'] ?: null,
            'proposed_rent_amount' => $validated['proposedRentAmount'] !== '' ? $validated['proposedRentAmount'] : null,
            'proposed_monthly_rent' => $validated['proposedRentPeriod'] === 'monthly'
                ? $validated['proposedRentAmount']
                : null,
            'proposal_notes' => $validated['proposalNotes'] ?: null,
        ];

        $service = app(LeaseLandlordApplicationService::class);
        $application = $this->selectedApplicationId
            ? $service->updateDraft(
                LeaseLandlordApplication::query()->findOrFail($this->selectedApplicationId),
                $data,
                $submit
            )
            : $service->create($data, $submit);

        $this->selectedApplicationId = $application->id;
        $this->resetForm();
        session()->flash('success', $submit
            ? "Application {$application->reference_no} submitted for tracking. No lease agreement was created."
            : "Draft {$application->reference_no} saved. No lease agreement was created.");
    }

    private function resetForm(): void
    {
        $this->origin = '';
        $this->externalTenantName = '';
        $this->contactName = '';
        $this->contactPhone = '';
        $this->contactEmail = '';
        $this->propertyReference = '';
        $this->propertyDescription = '';
        $this->proposedStartDate = '';
        $this->proposedEndDate = '';
        $this->proposedRentPeriod = 'monthly';
        $this->proposedRentAmount = '';
        $this->proposalNotes = '';
        $this->resetValidation();
    }
}