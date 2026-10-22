<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseStatus;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';
    public string $statusFilter = '';

    public function render()
    {
        $search = trim($this->search);

        $leases = LeaseAgreement::query()
            ->with(['leaseStatus', 'province', 'responsibleOfficer'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('contract_description', 'like', "%{$search}%")
                        ->orWhere('landlord_name', 'like', "%{$search}%")
                        ->orWhere('landlord_contact_phone', 'like', "%{$search}%")
                        ->orWhere('town', 'like', "%{$search}%")
                        ->orWhere('comment', 'like', "%{$search}%")
                        ->orWhereHas('province', function ($provinceQuery) use ($search) {
                            $provinceQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('responsibleOfficer', function ($officerQuery) use ($search) {
                            $officerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('lease_status_id', $this->statusFilter);
            })
            ->orderByDesc('id')
            ->get();

        $totalLeases = LeaseAgreement::count();

        $ongoingLeases = LeaseAgreement::whereHas(
            'leaseStatus',
            fn ($query) => $query->where('code', 'ongoing')
        )->count();

        $statuses = LeaseStatus::where('is_active', true)
    ->orderBy('name')
    ->get();

        return view('livewire.lease-agreements.index', [
            'leases' => $leases,
            'totalLeases' => $totalLeases,
            'ongoingLeases' => $ongoingLeases,
            'statuses' => $statuses,
        ])->layout('layouts.master');
    }
}