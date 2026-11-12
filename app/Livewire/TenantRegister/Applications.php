<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantOnboardingApplication;
use Livewire\Component;
use Livewire\WithPagination;

class Applications extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('tenant_register.view');

        $baseQuery = TenantOnboardingApplication::query()
            ->where('created_by', auth()->id());

        $applications = (clone $baseQuery)
            ->when($this->search !== '', function ($query): void {
                $search = '%' . $this->search . '%';
                $query->where(function ($query) use ($search): void {
                    $query->where('reference_no', 'like', $search)
                        ->orWhere('applicant_name', 'like', $search)
                        ->orWhere('man_no', 'like', $search)
                        ->orWhere('station_location', 'like', $search);
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->latest('updated_at')
            ->paginate(10);

        return view('livewire.tenant-register.applications', [
            'applications' => $applications,
            'totalApplications' => (clone $baseQuery)->count(),
            'draftApplications' => (clone $baseQuery)->where('status', 'Draft')->count(),
            'pendingApplications' => (clone $baseQuery)->where('status', 'Pending Verification')->count(),
            'returnedApplications' => (clone $baseQuery)->where('status', 'Returned for Correction')->count(),
            'statuses' => (clone $baseQuery)->distinct()->orderBy('status')->pluck('status'),
        ])->layout('layouts.master', ['title' => 'My Tenant Applications']);
    }
}