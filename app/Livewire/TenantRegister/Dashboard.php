<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantDocument;
use App\Models\TenantHistoryEvent;
use App\Models\TenantOnboardingApplication;
use App\Models\TenantRegister as TenantRecord;
use App\Models\WorkflowInstance;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $canViewRegister = $user->can('tenant_register.view');
        $canVerify = $user->can('tenant_register.verify');
        $canViewDocuments = $user->can('tenant_register.documents.view');
        $canViewHistory = $user->can('tenant_register.history.view');

        $metrics = [];
        $recentApplications = collect();
        $recentEvents = collect();
        $pendingReviews = 0;
        $documentsExpiringSoon = 0;

        if ($canViewRegister) {
            $vacantUnits = TenantRecord::query()
                ->whereRaw('LOWER(TRIM(current_tenant)) = ?', ['vacant'])
                ->count();
            $housingRecords = TenantRecord::query()->count();

            $metrics[] = [
                'label' => 'Housing records',
                'value' => $housingRecords,
                'detail' => 'Imported and registered units',
                'icon' => 'fas fa-home',
                'tone' => 'blue',
                'href' => route('tenant-register.index'),
            ];
            $metrics[] = [
                'label' => 'Vacant units',
                'value' => $vacantUnits,
                'detail' => $housingRecords > 0 ? number_format(($vacantUnits / $housingRecords) * 100, 1) . '% of housing records' : 'No housing records yet',
                'icon' => 'fas fa-door-open',
                'tone' => 'amber',
                'href' => route('tenant-register.index'),
            ];

            $pendingApplications = TenantOnboardingApplication::query()
                ->where('status', 'Pending Verification')
                ->count();
            $returnedApplications = TenantOnboardingApplication::query()
                ->where('status', 'Returned for Correction')
                ->count();
            $submittedThisMonth = TenantOnboardingApplication::query()
                ->whereNotNull('submitted_at')
                ->whereBetween('submitted_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            $metrics[] = [
                'label' => 'Pending verification',
                'value' => $pendingApplications,
                'detail' => 'Submitted applications',
                'icon' => 'fas fa-clipboard-check',
                'tone' => 'coral',
                'href' => $canVerify ? route('tenant-register.verification') : route('tenant-register.index'),
            ];
            $metrics[] = [
                'label' => 'Returned for correction',
                'value' => $returnedApplications,
                'detail' => 'Awaiting applicant updates',
                'icon' => 'fas fa-undo-alt',
                'tone' => 'violet',
                'href' => route('tenant-register.index'),
            ];
            $metrics[] = [
                'label' => 'Submitted this month',
                'value' => $submittedThisMonth,
                'detail' => now()->format('F Y'),
                'icon' => 'fas fa-calendar-check',
                'tone' => 'green',
                'href' => route('tenant-register.index'),
            ];

            $recentApplications = TenantOnboardingApplication::query()
                ->whereIn('status', ['Pending Verification', 'Verified', 'Returned for Correction'])
                ->latest('updated_at')
                ->limit(6)
                ->get(['id', 'reference_no', 'applicant_name', 'tenant_type', 'station_location', 'category', 'status', 'submitted_at', 'updated_at', 'created_by']);
        }

        if ($canVerify) {
            $pendingReviews = WorkflowInstance::query()
                ->where('workflowable_type', (new TenantOnboardingApplication())->getMorphClass())
                ->where('status', 'In Progress')
                ->whereJsonContains('current_approver_ids', (int) $user->id)
                ->count();
        }

        if ($canViewDocuments) {
            $documentsExpiringSoon = TenantDocument::query()
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [today(), today()->addDays(30)])
                ->count();
            $metrics[] = [
                'label' => 'Documents expiring soon',
                'value' => $documentsExpiringSoon,
                'detail' => 'Within the next 30 days',
                'icon' => 'fas fa-calendar-times',
                'tone' => 'coral',
                'href' => route('tenant-register.documents'),
            ];
        }

        if ($canViewHistory) {
            $recentEvents = TenantHistoryEvent::query()
                ->with(['tenantable', 'actor'])
                ->latest('occurred_at')
                ->limit(6)
                ->get();
        }

        return view('livewire.tenant-register.dashboard', [
            'metrics' => $metrics,
            'recentApplications' => $recentApplications,
            'recentEvents' => $recentEvents,
            'pendingReviews' => $pendingReviews,
            'documentsExpiringSoon' => $documentsExpiringSoon,
            'canViewRegister' => $canViewRegister,
            'canVerify' => $canVerify,
            'canViewDocuments' => $canViewDocuments,
            'canViewHistory' => $canViewHistory,
            'todayLabel' => now()->format('l, j F Y'),
        ])->layout('layouts.master', ['title' => 'Tenant Dashboard']);
    }
}