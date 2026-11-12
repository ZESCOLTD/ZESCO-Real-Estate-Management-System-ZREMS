<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantOnboardingApplication;
use App\Models\TenantRegister as TenantRecord;
use App\Services\TenantHistoryRecorder;
use App\Services\TenantOnboardingWorkflowService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Onboarding extends Component
{
    use WithFileUploads;

    public ?int $applicationId = null;
    public int $currentStep = 1;
    public array $supportingDocuments = [];
    public string $supportingDocumentType = 'Supporting document';
    public string $supportingDocumentExpiry = '';

    public array $form = [
        'tenant_type' => '',
        'applicant_name' => '',
        'identity_reference' => '',
        'man_no' => '',
        'employer' => '',
        'organization_registration_no' => '',
        'contact_person' => '',
        'phone' => '',
        'email' => '',
        'physical_address' => '',
        'postal_address' => '',
        'station_location' => '',
        'category' => '',
        'location_code' => '',
        'requested_unit_reference' => '',
        'occupants_count' => '',
        'requested_move_in_date' => '',
        'declaration_confirmed' => false,
    ];

    public function mount(?TenantOnboardingApplication $application = null): void
    {
        if ($application?->exists) {
            $this->applicationId = $application->id;
            $this->authorizeApplication($application);
            foreach (array_keys($this->form) as $field) {
                $this->form[$field] = $application->{$field} ?? '';
            }
        } else {
            $this->authorize('tenant_register.view');
        }
    }

    public function nextStep(): void
    {
        $this->authorizeCurrentForm();
        $this->validate($this->rulesForStep($this->currentStep));
        $this->currentStep = min(5, $this->currentStep + 1);
    }

    public function previousStep(): void
    {
        $this->authorizeCurrentForm();
        $this->currentStep = max(1, $this->currentStep - 1);
    }

    public function backToApplications(): void
    {
        $this->authorize('tenant_register.view');
        $this->redirectRoute('tenant-register.onboarding');
    }

    public function saveDraft(): void
    {
        $this->authorizeCurrentForm();
        $application = $this->persist();

        session()->flash('success', 'Draft saved. You can return to it before submitting.');

        $this->redirectRoute('tenant-register.onboarding');
    }

    public function submitForVerification(): void
    {
        $this->authorizeCurrentForm();
        $this->validate($this->submissionRules());
        $application = $this->persist();
        app(TenantOnboardingWorkflowService::class)->submit($application);
        $application->refresh();

        session()->flash('success', "Application {$application->reference_no} submitted for verification.");

        $this->redirectRoute('tenant-register.onboarding');
    }

    public function render()
    {
        $application = $this->authorizeCurrentForm();
        $application?->load('documents');

        return view('livewire.tenant-register.onboarding', [
            'application' => $application,
            'stationOptions' => TenantRecord::query()->whereNotNull('station_location')->distinct()->orderBy('station_location')->pluck('station_location'),
            'categoryOptions' => TenantRecord::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ])->layout('layouts.master', ['title' => 'My Tenant Applications']);
    }

    private function authorizeCurrentForm(): ?TenantOnboardingApplication
    {
        if (! $this->applicationId) {
            $this->authorize('tenant_register.create');

            return null;
        }

        $application = TenantOnboardingApplication::query()->findOrFail($this->applicationId);
        $this->authorizeApplication($application);

        return $application;
    }

    private function authorizeApplication(TenantOnboardingApplication $application): void
    {
        abort_unless(in_array($application->status, ['Draft', 'Returned for Correction'], true), 403);

        if ((int) $application->created_by === (int) auth()->id()) {
            $this->authorize('tenant_register.create');

            return;
        }

        $this->authorize('tenant_register.update');
    }

    private function persist(): TenantOnboardingApplication
    {
        $this->validate([
            'supportingDocuments' => ['nullable', 'array', 'max:10'],
            'supportingDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'supportingDocumentType' => ['required', 'string', 'max:255'],
            'supportingDocumentExpiry' => ['nullable', 'date'],
        ]);

        $application = $this->applicationId
            ? TenantOnboardingApplication::query()
                ->whereKey($this->applicationId)
                ->whereIn('status', ['Draft', 'Returned for Correction'])
                ->firstOrFail()
            : new TenantOnboardingApplication();

        $isNew = ! $application->exists;
        $application->fill($this->form);
        $changedFields = array_keys($application->getDirty());
        $application->created_by ??= auth()->id();
        $application->status ??= 'Draft';
        $application->save();

        app(TenantHistoryRecorder::class)->record(
            $application,
            $isNew ? 'application_draft_created' : 'application_draft_updated',
            $isNew ? 'Tenant onboarding draft created.' : 'Tenant onboarding draft updated.',
            ['changed_fields' => $changedFields]
        );

        foreach ($this->supportingDocuments as $file) {
            $path = $file->store('tenant-onboarding/' . $application->reference_no, 'private');

            if ($path) {
                $document = $application->documents()->create([
                    'document_type' => $this->supportingDocumentType,
                    'original_name' => $file->getClientOriginalName(),
                    'storage_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'review_status' => 'Not Reviewed',
                    'expires_at' => $this->supportingDocumentExpiry ?: null,
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                ]);

                app(TenantHistoryRecorder::class)->record(
                    $application,
                    'document_uploaded',
                    'A supporting document was uploaded.',
                    ['document_id' => $document->id, 'document_type' => $document->document_type]
                );
            }
        }

        $this->supportingDocuments = [];
        $this->supportingDocumentExpiry = '';
        $this->applicationId = $application->id;

        return $application->fresh();
    }

    private function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'form.tenant_type' => ['required', 'in:zesco_employee,organization,individual'],
                'form.applicant_name' => ['required', 'string', 'max:255'],
                'form.man_no' => ['nullable', 'required_if:form.tenant_type,zesco_employee', 'string', 'max:80'],
                'form.organization_registration_no' => ['nullable', 'string', 'max:100'],
                'form.contact_person' => ['nullable', 'required_if:form.tenant_type,organization', 'string', 'max:255'],
                'form.identity_reference' => ['nullable', 'string', 'max:120'],
                'form.employer' => ['nullable', 'string', 'max:255'],
            ],
            2 => [
                'form.phone' => ['required', 'string', 'max:60'],
                'form.email' => ['nullable', 'email', 'max:255'],
                'form.physical_address' => ['required', 'string', 'max:2000'],
                'form.postal_address' => ['nullable', 'string', 'max:2000'],
            ],
            3 => [
                'form.station_location' => ['required', 'string', 'max:255'],
                'form.category' => ['required', 'string', 'max:255'],
                'form.location_code' => ['nullable', 'string', 'max:120'],
                'form.requested_unit_reference' => ['nullable', 'string', 'max:255'],
                'form.occupants_count' => ['nullable', 'integer', 'min:1', 'max:100'],
                'form.requested_move_in_date' => ['nullable', 'date'],
            ],
            4 => [
                'supportingDocuments' => ['nullable', 'array', 'max:10'],
                'supportingDocuments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            ],
            default => [],
        };
    }

    private function submissionRules(): array
    {
        return [
            ...$this->rulesForStep(1),
            ...$this->rulesForStep(2),
            ...$this->rulesForStep(3),
            ...$this->rulesForStep(4),
            'form.declaration_confirmed' => ['accepted'],
        ];
    }
}