<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantRegister as TenantRecord;
use App\Services\TenantHistoryRecorder;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public TenantRecord $tenant;
    public array $form = [];
    public bool $canEdit = false;
    public bool $canManageDocuments = false;
    public bool $canViewHistory = false;
    public $documentFile;
    public string $documentType = 'Supporting document';
    public string $documentExpiry = '';

    private const FIELDS = [
        'serial_no', 'station_location', 'category', 'current_tenant', 'man_no', 'employer',
        'location', 'physical_address', 'area_m2', 'bedrooms', 'open_closed_verandah_m2',
        'garage_m2', 'servants_quarter_m2', 'condition', 'current_rental_charge',
        'market_rental_charge_gvd', 'proposed_rental_charge_50_percent',
        'proposed_rentals_after_verification', 'proposed_percentage_increment',
        'water_charges', 'sewer_charges', 'proposed_total_amount',
    ];

    public function mount(TenantRecord $tenant): void
    {
        $this->authorize('tenant_register.view');
        $this->tenant = $tenant;
        $this->canEdit = auth()->user()->can('tenant_register.update');
        $this->canManageDocuments = auth()->user()->can('tenant_register.documents.manage');
        $this->canViewHistory = auth()->user()->can('tenant_register.history.view');
        foreach (self::FIELDS as $field) {
            $this->form[$field] = $tenant->{$field} ?? '';
        }
    }

    public function save(): void
    {
        $this->authorize('tenant_register.update');
        $rules = [
            'form.serial_no' => ['nullable', 'string', 'max:255'],
            'form.station_location' => ['required', 'string', 'max:255'],
            'form.category' => ['required', 'string', 'max:255'],
            'form.current_tenant' => ['nullable', 'string', 'max:255'],
            'form.man_no' => ['nullable', 'string', 'max:255'],
            'form.employer' => ['nullable', 'string', 'max:255'],
            'form.location' => ['nullable', 'string', 'max:255'],
            'form.physical_address' => ['nullable', 'string', 'max:255'],
            'form.area_m2' => ['nullable', 'numeric', 'min:0'],
            'form.bedrooms' => ['nullable', 'integer', 'min:0'],
            'form.open_closed_verandah_m2' => ['nullable', 'numeric', 'min:0'],
            'form.garage_m2' => ['nullable', 'numeric', 'min:0'],
            'form.servants_quarter_m2' => ['nullable', 'numeric', 'min:0'],
            'form.condition' => ['nullable', 'string', 'max:255'],
            'form.current_rental_charge' => ['nullable', 'numeric', 'min:0'],
            'form.market_rental_charge_gvd' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_rental_charge_50_percent' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_rentals_after_verification' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_percentage_increment' => ['nullable', 'numeric', 'min:0'],
            'form.water_charges' => ['nullable', 'numeric', 'min:0'],
            'form.sewer_charges' => ['nullable', 'numeric', 'min:0'],
            'form.proposed_total_amount' => ['nullable', 'numeric', 'min:0'],
        ];
        $data = $this->validate($rules)['form'];

        if (blank($data['physical_address']) && blank($data['serial_no'])) {
            throw ValidationException::withMessages([
                'form.physical_address' => 'Enter a physical address or serial number to identify this property.',
            ]);
        }

        $data['unit_key'] = TenantRecord::unitKeyFor($data);
        try {
            $this->tenant->fill($data);
            $changedFields = array_keys($this->tenant->getDirty());
            $this->tenant->save();
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'form.physical_address' => 'Another tenant record already uses this station, category, location, and property reference.',
            ]);
        }

        if ($changedFields !== []) {
            app(TenantHistoryRecorder::class)->record(
                $this->tenant,
                'tenant_register_updated',
                'Tenant register details were updated.',
                ['changed_fields' => $changedFields]
            );
        }

        session()->flash('success', 'Tenant register record updated.');
        $this->tenant->refresh();
    }

    public function uploadDocument(): void
    {
        $this->authorize('tenant_register.documents.manage');
        $validated = $this->validate([
            'documentFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'documentType' => ['required', 'string', 'max:255'],
            'documentExpiry' => ['nullable', 'date'],
        ]);

        $path = $validated['documentFile']->store('tenant-register-documents/' . $this->tenant->id, 'private');
        if (! $path) {
            $this->addError('documentFile', 'The document could not be stored.');

            return;
        }

        $document = $this->tenant->documents()->create([
            'document_type' => $validated['documentType'],
            'original_name' => $validated['documentFile']->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $validated['documentFile']->getMimeType(),
            'file_size' => $validated['documentFile']->getSize(),
            'review_status' => 'Not Reviewed',
            'expires_at' => $validated['documentExpiry'] ?: null,
            'uploaded_by' => auth()->id(),
            'uploaded_at' => now(),
        ]);

        app(TenantHistoryRecorder::class)->record(
            $this->tenant,
            'document_uploaded',
            'A tenant document was uploaded.',
            ['document_id' => $document->id, 'document_type' => $document->document_type]
        );

        $this->reset('documentFile', 'documentExpiry');
        $this->documentType = 'Supporting document';
        session()->flash('success', 'Tenant document uploaded.');
    }

    public function render()
    {
        $this->authorize('tenant_register.view');

        return view('livewire.tenant-register.edit')
            ->with([
                'documents' => $this->tenant->documents()->with(['uploader', 'reviewer'])->latest('uploaded_at')->get(),
            ])
            ->layout('layouts.master', ['title' => $this->tenant->current_tenant ?: $this->tenant->physical_address]);
    }
}