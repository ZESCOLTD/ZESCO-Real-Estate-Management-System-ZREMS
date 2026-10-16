<?php

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\UtilsController;
use App\Livewire\Acquisitions\Spms\SpmsDocumentShow;
use App\Livewire\Admin\EmailLogIndex;
use App\Livewire\DashboardIndex;
use App\Livewire\Imports\FmsAssetRegisterImport;
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
use App\Models\OrganogramMappingView;
use App\Models\ProjectAllView;
use App\Models\VwBudgetCodesCombined;
use Illuminate\Http\Request;
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
    });

    Route::get('/configurations/roles/index', App\Livewire\Configurations\Roles\Index::class)->name('configurations.roles.index');

    Route::get('/users/index', UserIndex::class)->name('users.index');
    Route::get('/users/create', UserCreate::class)->name('users.create');
    Route::get('/users/{user}/edit', UserEdit::class)->name('users.edit');
    Route::get('/users/{user}/show', UserShow::class)->name('users.show');

    Route::get('/configurations/workflows/workflow-definitions/index', App\Livewire\Configurations\Workflows\WorkflowDefinition\Index::class)->name('configurations.workflows.workflow_definitions.index');
    Route::get('/configurations/workflows/workflow-definitions/{id}/show', App\Livewire\Configurations\Workflows\WorkflowDefinition\Show::class)->name('configurations.workflows.workflow_definitions.show');

    Route::middleware(['auth'])->get('/support-center', AdminChatCenter::class)->name('support.center');

});

Route::get('/getManNumbers', [UtilsController::class, 'getManNumbers'])->name('getManNumbers');
Route::get('/getManNumber', [UtilsController::class, 'getManNumber'])->name('getManNumber');
Route::get('/getEmployee', [UtilsController::class, 'getEmployee'])->name('getEmployee');
Route::get('/getManNumbersFiltered', [UtilsController::class, 'getManNumbers'])->name('getManNumbersFiltered');

require __DIR__.'/settings.php';
