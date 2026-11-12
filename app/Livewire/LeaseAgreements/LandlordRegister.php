<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseStatus;
use Livewire\Component;
use Livewire\WithPagination;

class LandlordRegister extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('lease_agreements.view');

        $search = trim($this->search);
        $baseQuery = LeaseAgreement::query()->where('zesco_role', 'landlord');

        $leases = (clone $baseQuery)
            ->with(['leaseStatus', 'responsibleOfficer'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $like = '%' . $search . '%';
                    $query->where('lease_number', 'like', $like)
                        ->orWhere('tenant_name', 'like', $like)
                        ->orWhere('tenant_contact_name', 'like', $like)
                        ->orWhere('tenant_contact_phone', 'like', $like)
                        ->orWhere('tenant_contact_email', 'like', $like)
                        ->orWhere('property_reference', 'like', $like)
                        ->orWhere('contract_description', 'like', $like)
                        ->orWhere('comment', 'like', $like)
                        ->orWhereHas('responsibleOfficer', fn ($officerQuery) => $officerQuery->where('name', 'like', $like));
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('lease_status_id', $this->statusFilter))
            ->latest('id')
            ->paginate(15);

        return view('livewire.lease-agreements.landlord-register', [
            'leases' => $leases,
            'totalLeases' => (clone $baseQuery)->count(),
            'ongoingLeases' => (clone $baseQuery)->whereHas('leaseStatus', fn ($query) => $query->where('code', 'ongoing'))->count(),
            'statuses' => LeaseStatus::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.master', ['title' => 'ZESCO as Landlord Lease Register']);
    }
}