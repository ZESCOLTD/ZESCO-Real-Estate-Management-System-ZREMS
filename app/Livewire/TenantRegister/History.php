<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantHistoryEvent;
use App\Models\TenantOnboardingApplication;
use App\Models\TenantRegister as TenantRecord;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $recordType = '';
    public ?int $tenantRegisterId = null;
    public ?int $applicationId = null;

    public function mount(): void
    {
        $this->authorize('tenant_register.history.view');
        $this->tenantRegisterId = request()->integer('tenant_register_id') ?: null;
        $this->applicationId = request()->integer('application_id') ?: null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRecordType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('tenant_register.history.view');

        $events = TenantHistoryEvent::query()
            ->with(['tenantable', 'actor'])
            ->when($this->tenantRegisterId, fn ($query) => $query
                ->where('tenantable_type', (new TenantRecord())->getMorphClass())
                ->where('tenantable_id', $this->tenantRegisterId))
            ->when($this->applicationId, fn ($query) => $query
                ->where('tenantable_type', (new TenantOnboardingApplication())->getMorphClass())
                ->where('tenantable_id', $this->applicationId))
            ->when($this->recordType === 'register', fn ($query) => $query->where('tenantable_type', (new TenantRecord())->getMorphClass()))
            ->when($this->recordType === 'application', fn ($query) => $query->where('tenantable_type', (new TenantOnboardingApplication())->getMorphClass()))
            ->when($this->search !== '', function ($query): void {
                $search = '%' . $this->search . '%';
                $query->where(function ($query) use ($search): void {
                    $query->where('summary', 'like', $search)
                        ->orWhere('event_type', 'like', $search);
                });
            })
            ->orderByDesc('occurred_at')
            ->paginate(25);

        return view('livewire.tenant-register.history', [
            'events' => $events,
        ])->layout('layouts.master', ['title' => 'Tenant History & Status']);
    }
}