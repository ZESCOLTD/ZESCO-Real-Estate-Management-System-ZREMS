<?php

namespace App\Livewire;

use App\Models\AssetRegister;
use App\Models\LeaseAgreement;
use App\Models\RealEstateProject;
use App\Models\TenantRegister;
use App\Models\WorkflowInstance;
use Illuminate\Support\Carbon;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canProjects = $user->can('real_estate_projects.view');
        $canApprove = $user->can('real_estate_projects.approve');
        $canLeases = $user->can('lease_agreements.view');
        $canTenants = $user->can('tenant_register.view');
        $canAssets = $user->can('asset_register.view');

        $metrics = [];
        $projectStatuses = collect();
        $recentProjects = collect();
        $reviewWorkflows = collect();
        $upcomingLeases = collect();
        $expiringLeaseCount = 0;
        $activeLeaseCount = 0;
        $monthlyLeaseCommitment = 0;
        $tenantCount = 0;
        $vacantUnitCount = 0;
        $assetCount = 0;

        if ($canProjects) {
            $proposalsInReview = RealEstateProject::query()
                ->where('record_origin', 'proposal')
                ->where('proposal_status', 'Under Review')
                ->count();
            $ongoingProjects = RealEstateProject::query()
                ->where('lifecycle_status', 'Ongoing')
                ->count();

            $metrics[] = [
                'label' => 'Proposals in review',
                'value' => $proposalsInReview,
                'note' => 'Awaiting a decision',
                'icon' => 'fas fa-file-signature',
                'tone' => 'amber',
                'href' => route('real-estate-projects.proposals'),
            ];
            $metrics[] = [
                'label' => 'Ongoing projects',
                'value' => $ongoingProjects,
                'note' => 'Across the portfolio',
                'icon' => 'fas fa-building',
                'tone' => 'green',
                'href' => route('real-estate-projects.register'),
            ];

            $projectStatuses = RealEstateProject::query()
                ->select('lifecycle_status')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('lifecycle_status')
                ->orderByDesc('total')
                ->get();

            $recentProjects = RealEstateProject::query()
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'reference_no', 'project_title', 'lifecycle_status', 'proposal_status', 'updated_at']);
        }

        if ($canApprove) {
            $reviewWorkflows = WorkflowInstance::query()
                ->where('workflowable_type', (new RealEstateProject())->getMorphClass())
                ->where('status', 'In Progress')
                ->whereJsonContains('current_approver_ids', (int) $user->id)
                ->with('workflowable')
                ->latest('updated_at')
                ->limit(5)
                ->get();

            $metrics[] = [
                'label' => 'My pending reviews',
                'value' => WorkflowInstance::query()
                    ->where('workflowable_type', (new RealEstateProject())->getMorphClass())
                    ->where('status', 'In Progress')
                    ->whereJsonContains('current_approver_ids', (int) $user->id)
                    ->count(),
                'note' => 'Assigned to you',
                'icon' => 'fas fa-inbox',
                'tone' => 'blue',
                'href' => route('real-estate-projects.approvals'),
            ];
        }

        if ($canLeases) {
            $activeLeaseQuery = LeaseAgreement::query()
                ->whereHas('leaseStatus', fn ($query) => $query->where('code', 'ongoing'));
            $activeLeaseCount = (clone $activeLeaseQuery)->count();
            $monthlyLeaseCommitment = (float) (clone $activeLeaseQuery)->sum('monthly_payment');

            $today = Carbon::today();
            $expiringLeasesQuery = LeaseAgreement::query()
                ->whereNotNull('end_date')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $today->copy()->addDays(90))
                ->where(function ($query): void {
                    $query->whereNull('lease_status_id')
                        ->orWhereHas('leaseStatus', fn ($statusQuery) => $statusQuery->where('code', '<>', 'expired'));
                });
            $expiringLeaseCount = (clone $expiringLeasesQuery)->count();
            $upcomingLeases = (clone $expiringLeasesQuery)
                ->with('leaseStatus')
                ->orderBy('end_date')
                ->limit(5)
                ->get(['id', 'contract_description', 'landlord_name', 'end_date', 'lease_status_id']);

            $metrics[] = [
                'label' => 'Active leases',
                'value' => $activeLeaseCount,
                'note' => 'ZMW ' . number_format($monthlyLeaseCommitment, 2) . ' monthly commitment',
                'icon' => 'fas fa-file-contract',
                'tone' => 'blue',
                'href' => route('lease_agreements.index'),
            ];
            $metrics[] = [
                'label' => 'Expiring in 90 days',
                'value' => $expiringLeaseCount,
                'note' => 'Review renewal dates',
                'icon' => 'fas fa-calendar-alt',
                'tone' => 'coral',
                'href' => route('lease_agreements.index'),
            ];
        }

        if ($canTenants) {
            $tenantCount = TenantRegister::query()->count();
            $vacantUnitCount = TenantRegister::query()
                ->whereRaw('LOWER(TRIM(current_tenant)) = ?', ['vacant'])
                ->count();

            $metrics[] = [
                'label' => 'Housing records',
                'value' => $tenantCount,
                'note' => number_format($vacantUnitCount) . ' vacant units',
                'icon' => 'fas fa-home',
                'tone' => 'green',
                'href' => route('tenant-register.index'),
            ];
        }

        if ($canAssets) {
            $assetCount = AssetRegister::query()->count();
            $metrics[] = [
                'label' => 'Registered assets',
                'value' => $assetCount,
                'note' => 'Internal asset register',
                'icon' => 'fas fa-boxes',
                'tone' => 'slate',
                'href' => route('asset-register.index'),
            ];
        }

        $greeting = match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return view('welcome', [
            'metrics' => $metrics,
            'projectStatuses' => $projectStatuses,
            'recentProjects' => $recentProjects,
            'reviewWorkflows' => $reviewWorkflows,
            'upcomingLeases' => $upcomingLeases,
            'expiringLeaseCount' => $expiringLeaseCount,
            'activeLeaseCount' => $activeLeaseCount,
            'monthlyLeaseCommitment' => $monthlyLeaseCommitment,
            'tenantCount' => $tenantCount,
            'vacantUnitCount' => $vacantUnitCount,
            'assetCount' => $assetCount,
            'canProjects' => $canProjects,
            'canApprove' => $canApprove,
            'canLeases' => $canLeases,
            'canTenants' => $canTenants,
            'canAssets' => $canAssets,
            'greeting' => $greeting,
            'firstName' => str($user->name ?? 'there')->before(' ')->toString(),
            'todayLabel' => now()->format('l, j F Y'),
        ])->layout('layouts.master', [
            'title' => 'Dashboard',
        ]);
    }
}
