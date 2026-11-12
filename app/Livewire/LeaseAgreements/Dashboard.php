<?php

namespace App\Livewire\LeaseAgreements;

use App\Models\LeaseAgreement;
use App\Models\LeaseTenantWorkflow;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $this->authorize('lease_tenant_workflow.view');

        $today = today();
        $openStatuses = [
            LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION,
            LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_LEASE,
            LeaseTenantWorkflow::STATUS_LEGAL_REVIEW,
            LeaseTenantWorkflow::STATUS_MD_SIGNATURE,
            LeaseTenantWorkflow::STATUS_AWAITING_LANDLORD_SIGNATURE,
        ];

        $expiring = fn ($from, $to) => LeaseAgreement::query()
            ->whereHas('leaseStatus', fn ($query) => $query->where('code', 'ongoing'))
            ->whereDate('end_date', '>=', $from)
            ->whereDate('end_date', '<=', $to)
            ->count();

        $stats = [
            'total' => LeaseAgreement::count(),
            'zesco_as_tenant' => LeaseAgreement::where('zesco_role', 'tenant')->count(),
            'zesco_as_landlord' => LeaseAgreement::where('zesco_role', 'landlord')->count(),
            'active' => LeaseAgreement::whereHas('leaseStatus', fn ($query) => $query->where('code', 'ongoing'))->count(),
            'open_processes' => LeaseTenantWorkflow::whereIn('status', $openStatuses)->count(),
            'expiring_30' => $expiring($today, $today->copy()->addDays(30)),
            'expiring_60' => $expiring($today->copy()->addDays(31), $today->copy()->addDays(60)),
            'expiring_90' => $expiring($today->copy()->addDays(61), $today->copy()->addDays(90)),
        ];

        $queue = LeaseTenantWorkflow::query()
            ->with(['leaseAgreement', 'events'])
            ->whereIn('status', $openStatuses)
            ->whereHas('leaseAgreement', fn ($query) => $query->where('zesco_role', 'tenant'))
            ->orderBy('updated_at')
            ->limit(12)
            ->get();

        return view('livewire.lease-agreements.dashboard', [
            'stats' => $stats,
            'queue' => $queue,
            'statusLabels' => app(\App\Services\LeaseTenantWorkflowService::class),
        ])->layout('layouts.master', ['title' => 'Lease Dashboard']);
    }
}