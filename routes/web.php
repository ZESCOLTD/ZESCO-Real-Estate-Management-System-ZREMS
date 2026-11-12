<?php

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\UtilsController;
use App\Livewire\Acquisitions\Spms\SpmsDocumentShow;
use App\Livewire\Admin\EmailLogIndex;
use App\Livewire\DashboardIndex;
use App\Livewire\Imports\FmsAssetRegisterImport;
use App\Livewire\AssetRegister\Index as InternalAssetRegisterIndex;
use App\Livewire\AssetRegister\Import as InternalAssetRegisterImport;
use App\Livewire\AssetRegister\Edit as InternalAssetRegisterEdit;
use App\Livewire\AssetRegister\Configurations as InternalAssetRegisterConfigurations;
use App\Livewire\TenantRegister\Index as TenantRegisterIndex;
use App\Livewire\TenantRegister\Import as TenantRegisterImport;
use App\Livewire\TenantRegister\Edit as TenantRegisterEdit;
use App\Livewire\TenantRegister\Onboarding as TenantRegisterOnboarding;
use App\Livewire\TenantRegister\Applications as TenantRegisterApplications;
use App\Livewire\TenantRegister\ApplicationShow as TenantRegisterApplicationShow;
use App\Livewire\TenantRegister\Verification as TenantRegisterVerification;
use App\Livewire\TenantRegister\Documents as TenantRegisterDocuments;
use App\Livewire\TenantRegister\History as TenantRegisterHistory;
use App\Livewire\TenantRegister\Dashboard as TenantRegisterDashboard;
use App\Livewire\RealEstateProjects\Index as RealEstateProjectIndex;
use App\Livewire\RealEstateProjects\ProposalForm as RealEstateProjectProposalForm;
use App\Livewire\RealEstateProjects\Dashboard as RealEstateProjectDashboard;
use App\Livewire\RealEstateProjects\Approvals as RealEstateProjectApprovals;
use App\Livewire\RealEstateProjects\ExistingProjectForm as ExistingRealEstateProjectForm;
use App\Livewire\RealEstateProjects\Reports as RealEstateProjectReports;
use App\Livewire\RealEstateProjects\Workspace as RealEstateProjectWorkspace;
use App\Livewire\LeaseAgreements\TenantWorkflow as LeaseTenantWorkflowPage;
use App\Livewire\LeaseAgreements\Dashboard as LeaseDashboard;
use App\Livewire\LeaseAgreements\Lifecycle as LeaseLifecycle;
use App\Livewire\LeaseAgreements\LandlordApplication as LandlordLeaseApplication;
use App\Livewire\LeaseAgreements\LandlordRegister as LandlordLeaseRegister;
use App\Livewire\LeaseAgreements\DocumentsHistory as LeaseDocumentsHistory;
use App\Livewire\Settings\NotificationPreferences;
use App\Livewire\Support\AdminChatCenter;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Livewire\Users\UserShow;
use App\Livewire\Workflows\WorkflowApprovers;
use App\Models\Currency;
use App\Models\FmsAssetRegister;
use App\Models\ImportError;
use App\Models\TenantOnboardingApplication;
use App\Models\TenantDocument;
use App\Models\LeaseTenantWorkflow;
use App\Models\LeaseLifecycleCase;
use App\Models\LeaseLandlordApplication;
use App\Models\OrganogramMappingView;
use App\Models\ProjectAllView;
use App\Models\VwBudgetCodesCombined;
use App\Models\WorkflowInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');
// Fortify registration route
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->name('register');

// AJAX verification for ZESCO employees
Route::post('/register/verify', function (Request $request, CreateNewUser $creator) {
    try {
        $userData = $creator->verifyZesco($request->only('man_no', 'email'));

        return response()->json([
            'success' => true,
            'user' => $userData,
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->errors()['man_no'][0] ?? 'Verification failed',
        ]);
    }
})->name('register.verify');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('/notifications', App\Livewire\Notifications\Index::class)->name('notifications.index');
    Route::get('/settings/notifications', NotificationPreferences::class)->name('settings.notifications');
    Route::get('/admin/email-log', EmailLogIndex::class)->name('admin.email_log');

    Route::get('/real-estate-projects/dashboard', RealEstateProjectDashboard::class)
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.dashboard');
    Route::get('/real-estate-projects/register', RealEstateProjectIndex::class)
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.register');
    Route::get('/real-estate-projects/approvals', RealEstateProjectApprovals::class)
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.approvals');
    Route::get('/real-estate-projects/reports', RealEstateProjectReports::class)
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.reports');
    Route::get('/real-estate-projects/proposals', RealEstateProjectIndex::class)
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.proposals');
    Route::get('/real-estate-projects/proposals/create', RealEstateProjectProposalForm::class)
        ->middleware('can:real_estate_projects.create')
        ->name('real-estate-projects.proposals.create');
    Route::get('/real-estate-projects/register-existing', ExistingRealEstateProjectForm::class)
        ->middleware('can:real_estate_projects.create')
        ->name('real-estate-projects.existing.create');
    Route::get('/real-estate-projects/{project}/proposal/edit', RealEstateProjectProposalForm::class)
        ->whereNumber('project')
        ->middleware('can:real_estate_projects.update')
        ->name('real-estate-projects.proposal.edit');
    Route::get('/real-estate-projects/{project}', RealEstateProjectWorkspace::class)
        ->whereNumber('project')
        ->middleware('can:real_estate_projects.view')
        ->name('real-estate-projects.show');


    Route::get('/currency-list/search', function (Request $request) {
        $search = strtolower($request->get('q'));
        $results = Currency::query()
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->code,
                    'text' => $item->code.' - '.$item->name,
                ];
            });

        return response()->json(['results' => $results]);
    });

    Route::get('/asset-details/search', function (Request $request) {
        $search = strtolower($request->get('q'));

        $results = FmsAssetRegister::query()
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(lams_asset_number) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(location) LIKE ?', ["%{$search}%"]);
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->lams_asset_number,
                    'text' => $item->lams_asset_number.' - '.$item->major_category.' - '.$item->description,
                ];
            });

        return response()->json(['results' => $results]);
    });
    Route::get('/project-codes/search', function (Request $request) {
        $search = strtolower($request->get('q'));

        $results = ProjectAllView::query()
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(project_code) LIKE ?', ["%{$search}%"]);
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->project_code,
                    'text' => $item->project_code.' - '.$item->description,
                ];
            });

        return response()->json(['results' => $results]);
    });

    Route::get('/business-units/search', function (Request $request) {
        $search = strtolower($request->get('q'));

        $results = OrganogramMappingView::query()
            ->select('bu_code', 'bu_description')
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(bu_code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(bu_description) LIKE ?', ["%{$search}%"]);
            })
            ->groupBy('bu_code', 'bu_description')
            ->limit(20)

            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->bu_code,
                    'text' => $item->bu_code.' - '.$item->bu_description,
                ];
            });

        return response()->json(['results' => $results]);
    });

    Route::get('/cost-centers/search', function (Request $request) {
        $search = strtolower($request->get('q'));

        $results = OrganogramMappingView::query()
            ->select('cc_code', 'cc_description')
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(cc_code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(cc_description) LIKE ?', ["%{$search}%"]);
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->cc_code,
                    'text' => $item->cc_code.' - '.$item->cc_description,
                ];
            });

        return response()->json(['results' => $results]);
    });

    Route::get('/budget-lines/search', function (Request $request) {
        $search = strtolower($request->get('q'));

        $results = VwBudgetCodesCombined::query()
            ->when($search, function ($q) use ($search) {
                $q->whereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(reporting_line) LIKE ?', ["%{$search}%"]);
            })
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->code,
                    'text' => $item->code.' - '.$item->reporting_line,
                ];
            });

        return response()->json(['results' => $results]);
    });

    Route::get('/workflow-approvers', WorkflowApprovers::class)
        ->name('workflow.approvers');

    Route::get('/spms/show', SpmsDocumentShow::class)->name('spms-document.show');

    Route::get('/asset-register', InternalAssetRegisterIndex::class)
        ->middleware('can:asset_register.view')
        ->name('asset-register.index');
    Route::get('/asset-register/import', InternalAssetRegisterImport::class)
        ->middleware('can:asset_register.create')
        ->name('asset-register.import');
    Route::get('/asset-register/{asset}/edit', InternalAssetRegisterEdit::class)
        ->whereNumber('asset')
        ->middleware('can:asset_register.view')
        ->name('asset-register.edit');
    Route::get('/tenant-register/dashboard', TenantRegisterDashboard::class)
        ->middleware('can:tenant_register.view')
        ->name('tenant-register.dashboard');
    Route::get('/tenant-register', TenantRegisterIndex::class)
        ->middleware('can:tenant_register.view')
        ->name('tenant-register.index');
    Route::get('/tenant-register/import', TenantRegisterImport::class)
        ->middleware('can:tenant_register.create')
        ->name('tenant-register.import');
    Route::get('/tenant-register/onboarding', TenantRegisterApplications::class)
        ->middleware('can:tenant_register.view')
        ->name('tenant-register.onboarding');
    Route::get('/tenant-register/onboarding/create', TenantRegisterOnboarding::class)
        ->middleware('can:tenant_register.create')
        ->name('tenant-register.onboarding.create');
    Route::get('/tenant-register/onboarding/{application}', TenantRegisterApplicationShow::class)
        ->whereNumber('application')
        ->middleware('can:tenant_register.view')
        ->name('tenant-register.onboarding.show');
    Route::get('/tenant-register/onboarding/{application}/edit', TenantRegisterOnboarding::class)
        ->whereNumber('application')
        ->name('tenant-register.onboarding.edit');
    Route::get('/tenant-register/verification', TenantRegisterVerification::class)
        ->middleware('can:tenant_register.verify')
        ->name('tenant-register.verification');
    Route::get('/tenant-register/documents', TenantRegisterDocuments::class)
        ->middleware('can:tenant_register.documents.view')
        ->name('tenant-register.documents');
    Route::get('/tenant-register/history', TenantRegisterHistory::class)
        ->middleware('can:tenant_register.history.view')
        ->name('tenant-register.history');
    Route::get('/tenant-register/documents/{document}/download', function (TenantDocument $document) {
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('document')
        ->middleware('can:tenant_register.documents.view')
        ->name('tenant-register.documents.download');
    Route::get('/tenant-register/applications/{application}/documents/{document}', function (int $applicationId, int $documentId) {
        $application = TenantOnboardingApplication::query()->findOrFail($applicationId);
        abort_unless($application->status === 'Pending Verification', 404);

        $document = TenantDocument::query()
            ->where('documentable_type', $application->getMorphClass())
            ->where('documentable_id', $application->id)
            ->findOrFail($documentId);
        $workflow = WorkflowInstance::query()
            ->where('workflowable_type', $application->getMorphClass())
            ->where('workflowable_id', $application->id)
            ->where('status', 'In Progress')
            ->latest('id')
            ->firstOrFail();
        $assignedIds = array_map('intval', $workflow->current_approver_ids ?? []);

        abort_unless(in_array((int) auth()->id(), $assignedIds, true), 403);
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('application')
        ->whereNumber('document')
        ->middleware('can:tenant_register.verify')
        ->name('tenant-register.verification.documents.download');
    Route::get('/tenant-register/{tenant}/edit', TenantRegisterEdit::class)
        ->whereNumber('tenant')
        ->middleware('can:tenant_register.view')
        ->name('tenant-register.edit');
    Route::get('/configurations/asset-register', InternalAssetRegisterConfigurations::class)
        ->middleware('can:configuration.manage')
        ->name('configurations.asset-register');

    Route::get('/fms-assets-register/index', App\Livewire\FmsAssetsRegister\Index::class)->name('fms_assets_register.index');
    Route::get('/fms-assets-register/{id}/show', App\Livewire\FmsAssetsRegister\Show::class)->name('fms_assets_register.show');
    Route::get('/fms-assets-register/{id}/edit', App\Livewire\FmsAssetsRegister\Edit::class)->name('fms_assets_register.edit');

    Route::get('/imports/fms-asset-register-import', FmsAssetRegisterImport::class)->name('imports.fms_assets.index');
    Route::get('/imports/{job}/errors/download', function ($jobId) {
        $errors = ImportError::where('import_job_id', $jobId)->get();
        $filename = "import_errors_{$jobId}.csv";
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $callback = function () use ($errors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Row', 'Error', 'Data']);

            foreach ($errors as $error) {
                fputcsv($file, [
                    $error->row_number,
                    $error->error_message,
                    $error->row_data,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    })->name('imports.errors.download');

    Route::get('/configurations/roles/index', App\Livewire\Configurations\Roles\Index::class)->name('configurations.roles.index');

    Route::get('/users/index', UserIndex::class)->name('users.index');
    Route::get('/users/create', UserCreate::class)->name('users.create');
    Route::get('/users/{user}/edit', UserEdit::class)->name('users.edit');
    Route::get('/users/{user}/show', UserShow::class)->name('users.show');

    Route::get('/configurations/workflows/workflow-definitions/index', App\Livewire\Configurations\Workflows\WorkflowDefinition\Index::class)->name('configurations.workflows.workflow_definitions.index');
    Route::get('/configurations/workflows/workflow-definitions/{id}/show', App\Livewire\Configurations\Workflows\WorkflowDefinition\Show::class)->name('configurations.workflows.workflow_definitions.show');

    Route::middleware(['auth'])->get('/support-center', AdminChatCenter::class)->name('support.center');
    Route::get('/lease-agreements/dashboard', LeaseDashboard::class)
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.dashboard');
    Route::get('/lease-agreements/renewals', LeaseLifecycle::class)
        ->defaults('caseType', 'renewal')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.renewals');
    Route::get('/lease-agreements/terminations', LeaseLifecycle::class)
        ->defaults('caseType', 'termination')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.terminations');
    Route::get('/lease-agreements/handovers', LeaseLifecycle::class)
        ->defaults('caseType', 'handover')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.handovers');
    Route::get('/lease-agreements/new', LeaseTenantWorkflowPage::class)
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.new');
    Route::get('/lease-agreements/new/landlord', LandlordLeaseApplication::class)
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.new.landlord');
    Route::get('/lease-agreements/documents-history', LeaseDocumentsHistory::class)
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.documents_history');
    Route::get('/lease-agreements/documents/{document}/download', function (TenantDocument $document) {
        $owner = $document->documentable;
        $isLeaseDocument = match (true) {
            $owner instanceof \App\Models\LeaseAgreement => true,
            $owner instanceof LeaseTenantWorkflow => $owner->leaseAgreement?->zesco_role === 'tenant',
            $owner instanceof LeaseLifecycleCase => $owner->leaseAgreement?->zesco_role === 'tenant',
            $owner instanceof LeaseLandlordApplication => true,
            default => false,
        };

        abort_unless($isLeaseDocument, 404);
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('document')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.documents.download');
    Route::get('/lease-agreements/workflows/{workflow}/documents/{document}/download', function (LeaseTenantWorkflow $workflow, TenantDocument $document) {
        abort_unless($workflow->leaseAgreement?->zesco_role === 'tenant', 404);
        abort_unless(
            $document->documentable_type === $workflow->getMorphClass()
                && (int) $document->documentable_id === (int) $workflow->id,
            404
        );
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('workflow')
        ->whereNumber('document')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.tenant_workflows.documents.download');
    Route::get('/lease-agreements/lifecycle/{lifecycleCase}/documents/{document}/download', function (LeaseLifecycleCase $lifecycleCase, TenantDocument $document) {
        abort_unless($lifecycleCase->leaseAgreement?->zesco_role === 'tenant', 404);
        abort_unless(
            $document->documentable_type === $lifecycleCase->getMorphClass()
                && (int) $document->documentable_id === (int) $lifecycleCase->id,
            404
        );
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('lifecycleCase')
        ->whereNumber('document')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.lifecycle.documents.download');
    Route::get('/lease-agreements/landlord-applications/{application}/documents/{document}/download', function (LeaseLandlordApplication $application, TenantDocument $document) {
        abort_unless(
            $document->documentable_type === $application->getMorphClass()
                && (int) $document->documentable_id === (int) $application->id,
            404
        );
        abort_unless(Storage::disk('private')->exists($document->storage_path), 404);

        return Storage::disk('private')->download($document->storage_path, $document->original_name);
    })
        ->whereNumber('application')
        ->whereNumber('document')
        ->middleware('can:lease_tenant_workflow.view')
        ->name('lease_agreements.landlord_applications.documents.download');
    Route::get('/lease-agreements/landlord-register', LandlordLeaseRegister::class)
        ->middleware('can:lease_agreements.view')
        ->name('lease_agreements.landlord_register');
    Route::get('/lease-agreements', App\Livewire\LeaseAgreements\Index::class)
        ->middleware('can:lease_agreements.view')
        ->name('lease_agreements.index');
    
    Route::get(
    '/configurations/lease-statuses',
    App\Livewire\Configurations\LeaseStatuses\Index::class
)
    ->middleware('can:configuration.manage')
    ->name('configurations.lease_statuses.index');
    
    Route::get(
        '/lease-agreements/import',
        App\Livewire\LeaseAgreements\Import::class
    )
        ->middleware('can:lease_agreements.manage')
        ->name('lease_agreements.import');
    });

Route::get('/getManNumbers', [UtilsController::class, 'getManNumbers'])->name('getManNumbers');
Route::get('/getManNumber', [UtilsController::class, 'getManNumber'])->name('getManNumber');
Route::get('/getEmployee', [UtilsController::class, 'getEmployee'])->name('getEmployee');
Route::get('/getManNumbersFiltered', [UtilsController::class, 'getManNumbers'])->name('getManNumbersFiltered');

require __DIR__.'/settings.php';
