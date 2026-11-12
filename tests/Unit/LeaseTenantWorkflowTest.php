<?php

use App\Models\LeaseAgreement;
use App\Models\LeaseExpiryNotificationLog;
use App\Models\LeaseLifecycleCase;
use App\Models\LeaseLandlordApplication;
use App\Models\LeaseStatus;
use App\Models\LeaseTenantWorkflow;
use App\Models\TenantDocument;
use App\Models\User;
use App\Livewire\LeaseAgreements\Dashboard as LeaseDashboard;
use App\Livewire\LeaseAgreements\DocumentsHistory as LeaseDocumentsHistory;
use App\Livewire\LeaseAgreements\Lifecycle as LeaseLifecyclePage;
use App\Livewire\LeaseAgreements\LandlordRegister as LandlordLeaseRegisterPage;
use App\Livewire\LeaseAgreements\LandlordApplication as LandlordApplicationPage;
use App\Livewire\LeaseAgreements\TenantWorkflow as TenantWorkflowPage;
use App\Livewire\Settings\NotificationPreferences;
use App\Notifications\EmailConnectivityTestNotification;
use App\Notifications\LeaseExpiryNotification;
use App\Notifications\LeaseWorkflowUpdateNotification;
use App\Services\LeaseTenantWorkflowService;
use App\Services\LeaseLifecycleService;
use App\Services\LeaseLandlordApplicationService;
use App\Services\LeaseExpiryNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Gate::define('lease_tenant_workflow.view', fn () => true);
    Gate::define('lease_tenant_workflow.manage', fn () => true);

    foreach ([
        'lease_expiry_notification_logs',
        'lease_landlord_application_events',
        'lease_landlord_applications',
        'lease_lifecycle_events',
        'lease_lifecycle_cases',
        'lease_tenant_workflow_events',
        'lease_tenant_workflows',
        'lease_agreements',
        'lease_statuses',
        'users',
    ] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->softDeletes();
        $table->timestamps();
    });

    Schema::create('lease_statuses', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name')->unique();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('lease_agreements', function (Blueprint $table) {
        $table->id();
        $table->string('zesco_role')->default('tenant');
        $table->string('lease_number')->nullable()->unique();
        $table->text('contract_description');
        $table->string('landlord_name')->nullable();
        $table->string('landlord_contact_phone', 30)->nullable();
        $table->string('tenant_name')->nullable();
        $table->string('tenant_contact_name')->nullable();
        $table->string('tenant_contact_phone', 30)->nullable();
        $table->string('tenant_contact_email')->nullable();
        $table->string('property_reference')->nullable();
        $table->string('town')->nullable();
        $table->unsignedBigInteger('province_id')->nullable();
        $table->string('lease_duration')->nullable();
        $table->decimal('lease_tenure_years', 6, 2)->nullable();
        $table->decimal('monthly_payment', 18, 2)->nullable();
        $table->decimal('annual_payment', 18, 2)->nullable();
        $table->string('payment_period', 24)->nullable();
        $table->decimal('payment_amount', 18, 2)->nullable();
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->unsignedBigInteger('lease_status_id')->nullable();
        $table->text('comment')->nullable();
        $table->unsignedBigInteger('responsible_officer_id')->nullable();
        $table->timestamps();
    });

    Schema::create('lease_expiry_notification_logs', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_agreement_id');
        $table->unsignedBigInteger('recipient_id')->nullable();
        $table->unsignedSmallInteger('days_before_end');
        $table->date('lease_end_date');
        $table->timestamp('queued_at');
        $table->timestamps();
        $table->unique(['lease_agreement_id', 'days_before_end', 'lease_end_date']);
    });

    Schema::create('lease_tenant_workflows', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_agreement_id');
        $table->string('department_name');
        $table->string('status', 48);
        $table->unsignedBigInteger('initiated_by');
        $table->timestamp('completed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lease_tenant_workflow_events', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_tenant_workflow_id');
        $table->string('action', 64);
        $table->string('from_status', 48)->nullable();
        $table->string('to_status', 48);
        $table->text('comment')->nullable();
        $table->unsignedBigInteger('acted_by')->nullable();
        $table->timestamp('acted_at');
        $table->timestamps();
    });

    Schema::create('tenant_documents', function (Blueprint $table) {
        $table->id();
        $table->string('documentable_type');
        $table->unsignedBigInteger('documentable_id');
        $table->string('document_type')->default('Supporting document');
        $table->string('original_name');
        $table->string('storage_path');
        $table->string('mime_type', 150)->nullable();
        $table->unsignedBigInteger('file_size')->nullable();
        $table->string('review_status')->default('Not Reviewed');
        $table->date('expires_at')->nullable();
        $table->text('review_comment')->nullable();
        $table->unsignedBigInteger('uploaded_by')->nullable();
        $table->unsignedBigInteger('reviewed_by')->nullable();
        $table->timestamp('uploaded_at')->nullable();
        $table->timestamp('reviewed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lease_lifecycle_cases', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_agreement_id');
        $table->string('case_type', 24);
        $table->string('status', 24);
        $table->text('reason')->nullable();
        $table->date('proposed_start_date')->nullable();
        $table->date('proposed_end_date')->nullable();
        $table->decimal('proposed_monthly_payment', 18, 2)->nullable();
        $table->date('proposed_effective_date')->nullable();
        $table->date('notice_date')->nullable();
        $table->json('details')->nullable();
        $table->unsignedBigInteger('created_by');
        $table->timestamp('submitted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lease_lifecycle_events', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_lifecycle_case_id');
        $table->string('action', 48);
        $table->string('from_status', 24)->nullable();
        $table->string('to_status', 24);
        $table->text('comment')->nullable();
        $table->unsignedBigInteger('actor_id')->nullable();
        $table->timestamp('occurred_at');
        $table->timestamps();
    });

    Schema::create('lease_landlord_applications', function (Blueprint $table) {
        $table->id();
        $table->string('reference_no', 32)->nullable()->unique();
        $table->string('origin', 32);
        $table->string('status', 24);
        $table->unsignedBigInteger('lease_agreement_id')->nullable();
        $table->string('external_tenant_name');
        $table->string('contact_name')->nullable();
        $table->string('contact_phone', 30)->nullable();
        $table->string('contact_email')->nullable();
        $table->string('property_reference')->nullable();
        $table->text('property_description');
        $table->date('proposed_start_date')->nullable();
        $table->date('proposed_end_date')->nullable();
        $table->decimal('proposed_monthly_rent', 18, 2)->nullable();
        $table->string('proposed_rent_period', 24)->nullable();
        $table->decimal('proposed_rent_amount', 18, 2)->nullable();
        $table->text('proposal_notes')->nullable();
        $table->unsignedBigInteger('created_by');
        $table->timestamp('submitted_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lease_landlord_application_events', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('lease_landlord_application_id');
        $table->string('action', 48);
        $table->string('from_status', 24)->nullable();
        $table->string('to_status', 24);
        $table->text('comment')->nullable();
        $table->unsignedBigInteger('actor_id')->nullable();
        $table->timestamp('occurred_at');
        $table->timestamps();
    });
});

function makeWorkflowUser(): User
{
    static $sequence = 0;
    $sequence++;

    return User::query()->create([
        'name' => 'Lease Officer',
        'email' => "lease-officer-{$sequence}@example.test",
        'password' => 'password',
    ]);
}

function makeTenantLease(): LeaseAgreement
{
    $status = LeaseStatus::create([
        'code' => 'ongoing',
        'name' => 'On-going',
        'is_active' => true,
    ]);

    return LeaseAgreement::create([
        'contract_description' => 'Shop lease at Levy Business Park',
        'landlord_name' => 'NAPSA',
        'zesco_role' => 'tenant',
        'lease_status_id' => $status->id,
    ]);
}

test('tenant-side process records its milestones without changing the imported lease status', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $service = app(LeaseTenantWorkflowService::class);

    $workflow = $service->start($lease, 'Customer Service');
    $workflow = $service->recordAction($workflow, 'department_willing');
    $workflow = $service->recordAction($workflow, 'landlord_lease_requested');
    $workflow = $service->recordAction($workflow, 'landlord_lease_received');
    $workflow = $service->recordAction($workflow, 'legal_review_completed');
    $workflow = $service->recordAction($workflow, 'md_signed_and_sent');
    $workflow = $service->recordAction($workflow, 'countersigned_copy_received');

    expect($workflow->status)->toBe(LeaseTenantWorkflow::STATUS_EXECUTED_COPY_RECEIVED)
        ->and($workflow->events)->toHaveCount(7)
        ->and($lease->fresh()->lease_status_id)->toBe($workflow->leaseAgreement->lease_status_id)
        ->and($workflow->leaseAgreement->leaseStatus->code)->toBe('ongoing');
});

test('department decline closes only the process and records a reason', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $service = app(LeaseTenantWorkflowService::class);
    $workflow = $service->start($lease, 'Customer Service');

    expect(fn () => $service->recordAction($workflow, 'department_declined'))
        ->toThrow(ValidationException::class);

    $workflow = $service->recordAction($workflow, 'department_declined', 'Department no longer requires the premises.');

    expect($workflow->status)->toBe(LeaseTenantWorkflow::STATUS_DEPARTMENT_DECLINED)
        ->and($workflow->events->last()->comment)->toBe('Department no longer requires the premises.')
        ->and($lease->fresh()->lease_status_id)->toBe($workflow->leaseAgreement->lease_status_id);
});

test('tenant-side process rejects leases where ZESCO is the landlord', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $lease->update(['zesco_role' => 'landlord']);

    expect(fn () => app(LeaseTenantWorkflowService::class)->start($lease, 'Customer Service'))
        ->toThrow(ValidationException::class);
});

test('lease files are stored privately and logged without advancing the process', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $workflow = app(LeaseTenantWorkflowService::class)->start($lease, 'Customer Service');
    Storage::fake('private');
    Gate::shouldReceive('authorize')->once()->with('lease_tenant_workflow.manage');

    $component = new TenantWorkflowPage();
    $component->selectedWorkflowId = $workflow->id;
    $component->documentType = 'Landlord lease';
    $component->supportingDocuments = [UploadedFile::fake()->create('landlord-lease.pdf', 100, 'application/pdf')];
    $component->uploadDocuments();

    $document = TenantDocument::query()->sole();
    $workflow->refresh();

    expect($document->documentable_type)->toBe($workflow->getMorphClass())
        ->and($document->documentable_id)->toBe($workflow->id)
        ->and($workflow->status)->toBe(LeaseTenantWorkflow::STATUS_DEPARTMENT_CONFIRMATION)
        ->and($workflow->events()->where('action', 'document_uploaded')->exists())->toBeTrue();

    Storage::disk('private')->assertExists($document->storage_path);
});

test('lease dashboard separates direction counts and queues only open tenant processes', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);

    $tenantLease = makeTenantLease();
    $tenantLease->update(['end_date' => today()->addDays(20)]);
    $landlordLease = LeaseAgreement::create([
        'contract_description' => 'ZESCO property lease to an external tenant',
        'landlord_name' => 'External tenant',
        'zesco_role' => 'landlord',
        'lease_status_id' => $tenantLease->lease_status_id,
        'end_date' => today()->addDays(45),
    ]);

    app(LeaseTenantWorkflowService::class)->start($tenantLease, 'Customer Service');

    Gate::shouldReceive('authorize')->once()->with('lease_tenant_workflow.view', []);
    $view = (new LeaseDashboard())->render();
    $data = $view->getData();

    expect($data['stats']['total'])->toBe(2)
        ->and($data['stats']['zesco_as_tenant'])->toBe(1)
        ->and($data['stats']['zesco_as_landlord'])->toBe(1)
        ->and($data['stats']['expiring_30'])->toBe(1)
        ->and($data['stats']['expiring_60'])->toBe(1)
        ->and($data['queue'])->toHaveCount(1)
        ->and($data['queue']->first()->lease_agreement_id)->toBe($tenantLease->id)
        ->and($landlordLease->zesco_role)->toBe('landlord');
});

test('renewal and termination requests are recorded separately without changing lease terms', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $service = app(LeaseLifecycleService::class);

    $renewal = $service->createCase($lease, LeaseLifecycleCase::TYPE_RENEWAL, [
        'reason' => 'Department wishes to continue occupancy.',
        'proposed_start_date' => '2027-01-01',
        'proposed_end_date' => '2029-12-31',
        'proposed_monthly_payment' => '24000.00',
    ], true);

    $termination = $service->createCase($lease, LeaseLifecycleCase::TYPE_TERMINATION, [
        'reason' => 'Department is relocating.',
        'proposed_effective_date' => '2027-03-31',
        'notice_date' => '2027-01-31',
    ], true);

    expect($renewal->status)->toBe(LeaseLifecycleCase::STATUS_SUBMITTED)
        ->and($termination->status)->toBe(LeaseLifecycleCase::STATUS_SUBMITTED)
        ->and($renewal->case_type)->not->toBe($termination->case_type)
        ->and(LeaseLifecycleCase::query()->count())->toBe(2)
        ->and($lease->fresh()->start_date)->toBeNull()
        ->and($lease->fresh()->end_date)->toBeNull()
        ->and($lease->fresh()->monthly_payment)->toBeNull()
        ->and($lease->fresh()->lease_status_id)->toBe($renewal->leaseAgreement->lease_status_id);
});

test('handover records inspection details without changing the lease status', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();

    $handover = app(LeaseLifecycleService::class)->createCase($lease, LeaseLifecycleCase::TYPE_HANDOVER, [
        'details' => [
            'handover_type' => 'move_out',
            'inspection_date' => '2026-10-28',
            'meter_readings' => 'Electricity: 12345',
            'keys_and_items' => 'Two entrance keys',
            'property_condition' => 'Needs paint in the main room.',
            'defects_issues' => 'Window latch damaged.',
            'acknowledgements' => 'Outgoing occupant and Real Estate officer',
        ],
    ], true);

    expect($handover->status)->toBe(LeaseLifecycleCase::STATUS_RECORDED)
        ->and($handover->details['handover_type'])->toBe('move_out')
        ->and($handover->events()->where('action', 'handover_recorded')->exists())->toBeTrue()
        ->and($lease->fresh()->lease_status_id)->toBe($handover->leaseAgreement->lease_status_id);
});

test('lifecycle records reject leases where ZESCO is the landlord', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $lease->update(['zesco_role' => 'landlord']);

    expect(fn () => app(LeaseLifecycleService::class)->createCase(
        $lease,
        LeaseLifecycleCase::TYPE_RENEWAL,
        ['reason' => 'Renewing the lease'],
        true
    ))->toThrow(ValidationException::class);
});

test('lifecycle documents are stored privately and linked to their case', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $lifecycleCase = app(LeaseLifecycleService::class)->createCase(
        $lease,
        LeaseLifecycleCase::TYPE_TERMINATION,
        ['reason' => 'Premises no longer required.', 'proposed_effective_date' => '2027-03-31'],
        true
    );
    Storage::fake('private');
    Gate::shouldReceive('authorize')->once()->with('lease_tenant_workflow.manage');

    $component = new LeaseLifecyclePage();
    $component->caseType = LeaseLifecycleCase::TYPE_TERMINATION;
    $component->selectedCaseId = $lifecycleCase->id;
    $component->documentType = 'Termination notice';
    $component->supportingDocuments = [UploadedFile::fake()->create('notice.pdf', 100, 'application/pdf')];
    $component->uploadDocuments();

    $document = TenantDocument::query()->sole();

    expect($document->documentable_type)->toBe($lifecycleCase->getMorphClass())
        ->and($document->documentable_id)->toBe($lifecycleCase->id)
        ->and($lifecycleCase->events()->where('action', 'document_uploaded')->exists())->toBeTrue();

    Storage::disk('private')->assertExists($document->storage_path);
});

test('landlord-side applications are separate from the lease register', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $originalLeaseStatusId = $lease->lease_status_id;
    $service = app(LeaseLandlordApplicationService::class);

    $data = [
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'ZESCO Customer Service',
        'property_description' => 'Office space in a ZESCO-owned property.',
        'proposed_start_date' => '2027-01-01',
        'proposed_end_date' => '2029-12-31',
        'proposed_rent_period' => 'monthly',
        'proposed_rent_amount' => '500000.00',
    ];
    $draft = $service->create($data, false);
    $submitted = $service->create([
        ...$data,
        'origin' => LeaseLandlordApplication::ORIGIN_ZESCO_OFFER,
        'external_tenant_name' => 'External applicant',
    ], true);

    expect($draft->reference_no)->toBe('ZLA-000001')
        ->and($draft->status)->toBe(LeaseLandlordApplication::STATUS_DRAFT)
        ->and($submitted->reference_no)->toBe('ZLA-000002')
        ->and($submitted->status)->toBe(LeaseLandlordApplication::STATUS_TENANT_REVIEW)
        ->and(LeaseAgreement::query()->count())->toBe(1)
        ->and($lease->fresh()->lease_status_id)->toBe($originalLeaseStatusId)
        ->and($submitted->events->first()->action)->toBe('sent_to_tenant');
});

test('landlord-side draft resumes under the same reference and submits without creating a lease', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $service = app(LeaseLandlordApplicationService::class);
    $draft = $service->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'Applicant draft',
        'property_description' => 'Proposed office space',
    ], false);

    $submitted = $service->updateDraft($draft, [
        'origin' => LeaseLandlordApplication::ORIGIN_ZESCO_OFFER,
        'external_tenant_name' => 'Applicant updated',
        'property_description' => 'Updated property proposal',
    ], true);

    expect($submitted->id)->toBe($draft->id)
        ->and($submitted->reference_no)->toBe($draft->reference_no)
        ->and($submitted->status)->toBe(LeaseLandlordApplication::STATUS_TENANT_REVIEW)
        ->and($submitted->events->pluck('action')->all())->toBe(['draft_saved', 'revised_draft_sent_to_tenant'])
        ->and(LeaseAgreement::query()->count())->toBe(0);

    expect(fn () => $service->updateDraft($submitted, [
        'origin' => LeaseLandlordApplication::ORIGIN_ZESCO_OFFER,
        'external_tenant_name' => 'Attempted post-submit edit',
        'property_description' => 'Not allowed',
    ], false))->toThrow(ValidationException::class);
});

test('landlord-side application follows tenant feedback and revision stages before legal review', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $service = app(LeaseLandlordApplicationService::class);
    $application = $service->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'Applicant',
        'property_description' => 'Proposed ZESCO premises',
    ], true);

    $application = $service->recordAction($application, 'tenant_feedback_received', 'Tenant requests a revised start date.');
    expect($application->status)->toBe(LeaseLandlordApplication::STATUS_TENANT_FEEDBACK);

    $application = $service->recordAction($application, 'revision_sent_to_tenant', 'Updated proposed start date and reissued draft.');
    expect($application->status)->toBe(LeaseLandlordApplication::STATUS_TENANT_REVIEW);

    $application = $service->recordAction($application, 'tenant_feedback_received', 'Tenant confirms the latest draft.');
    $application = $service->recordAction($application, 'tenant_confirmed_draft', 'Tenant confirmed; forwarded to ZESCO Legal.');

    expect($application->status)->toBe(LeaseLandlordApplication::STATUS_LEGAL_REVIEW)
        ->and($application->events->pluck('action')->all())->toBe([
            'sent_to_tenant',
            'tenant_feedback_received',
            'revision_sent_to_tenant',
            'tenant_feedback_received',
            'tenant_confirmed_draft',
        ])
        ->and(LeaseAgreement::query()->count())->toBe(0);

    expect(fn () => $service->recordAction($application, 'revision_sent_to_tenant', 'Out-of-order action.'))
        ->toThrow(ValidationException::class);
});

test('legacy submitted landlord applications can record their tenant feedback', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $application = app(LeaseLandlordApplicationService::class)->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'Legacy applicant',
        'property_description' => 'Existing submitted application',
    ], false);
    $application->update(['status' => LeaseLandlordApplication::STATUS_SUBMITTED]);

    $application = app(LeaseLandlordApplicationService::class)->recordAction(
        $application,
        'tenant_feedback_received',
        'Feedback received on a prior submission.'
    );

    expect($application->status)->toBe(LeaseLandlordApplication::STATUS_TENANT_FEEDBACK);
});

test('legal routing compares the amount for the stated rent period to the K600000 threshold', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    LeaseStatus::create(['code' => 'executed', 'name' => 'Executed', 'is_active' => true]);
    $service = app(LeaseLandlordApplicationService::class);

    $startAtLegalReview = function (string $period, string $amount) use ($service): LeaseLandlordApplication {
        $application = $service->create([
            'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
            'external_tenant_name' => 'Applicant for ' . $period,
            'property_description' => 'Proposed ZESCO premises',
            'proposed_rent_period' => $period,
            'proposed_rent_amount' => $amount,
        ], true);
        $application = $service->recordAction($application, 'tenant_feedback_received', 'Tenant feedback recorded.');

        return $service->recordAction($application, 'tenant_confirmed_draft', 'Tenant confirmed draft and sent to Legal.');
    };

    $exactThreshold = $startAtLegalReview('6_months', '600000.00');
    TenantDocument::create([
        'documentable_type' => $exactThreshold->getMorphClass(),
        'documentable_id' => $exactThreshold->id,
        'document_type' => 'Finalized lease',
        'original_name' => 'legal-finalized-lease.pdf',
        'storage_path' => 'landlord-applications/' . $exactThreshold->id . '/legal-finalized-lease.pdf',
    ]);
    $legalFinalized = $service->recordAction($exactThreshold, 'complete_legal_review', 'Legal finalized and sent the copy to Real Estate.');

    expect($legalFinalized->status)->toBe(LeaseLandlordApplication::STATUS_LEGAL_FINALIZED)
        ->and($legalFinalized->events->last()->action)->toBe('legal_review_completed_and_finalized')
        ->and($legalFinalized->leaseAgreement->zesco_role)->toBe('landlord')
        ->and($legalFinalized->leaseAgreement->tenant_name)->toBe('Applicant for 6_months')
        ->and($legalFinalized->leaseAgreement->payment_period)->toBe('6_months')
        ->and((float) $legalFinalized->leaseAgreement->payment_amount)->toBe(600000.0);

    $aboveThreshold = $startAtLegalReview('3_months', '600000.01');
    $sentToAg = $service->recordAction($aboveThreshold, 'complete_legal_review', 'Legal reviewed and sent the lease to AG.');

    expect($sentToAg->status)->toBe(LeaseLandlordApplication::STATUS_AG_REVIEW)
        ->and($sentToAg->events->last()->action)->toBe('legal_review_completed_sent_to_ag');

    expect(LeaseAgreement::where('zesco_role', 'landlord')->count())->toBe(1);

    TenantDocument::create([
        'documentable_type' => $sentToAg->getMorphClass(),
        'documentable_id' => $sentToAg->id,
        'document_type' => 'Finalized lease',
        'original_name' => 'ag-finalized-lease.pdf',
        'storage_path' => 'landlord-applications/' . $sentToAg->id . '/ag-finalized-lease.pdf',
    ]);

    $agFinalized = $service->recordAction($sentToAg, 'ag_finalization_received', 'AG finalized and returned the copy to ZESCO.');

    expect($agFinalized->status)->toBe(LeaseLandlordApplication::STATUS_AG_FINALIZED)
        ->and($agFinalized->leaseAgreement->zesco_role)->toBe('landlord')
        ->and(LeaseAgreement::where('zesco_role', 'landlord')->count())->toBe(2)
        ->and($agFinalized->leaseAgreement->payment_period)->toBe('3_months')
        ->and((float) $agFinalized->leaseAgreement->payment_amount)->toBe(600000.01);
});

test('legal routing requires rent period and amount', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $service = app(LeaseLandlordApplicationService::class);
    $application = $service->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'Applicant without rent basis',
        'property_description' => 'Proposed ZESCO premises',
    ], true);
    $application = $service->recordAction($application, 'tenant_feedback_received', 'Tenant confirms draft.');
    $application = $service->recordAction($application, 'tenant_confirmed_draft', 'Tenant confirms draft; sent to Legal.');

    expect(fn () => $service->recordAction($application, 'complete_legal_review', 'Legal review complete.'))
        ->toThrow(ValidationException::class);
});

test('landlord application documents are private and attached to the application', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $application = app(LeaseLandlordApplicationService::class)->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'External applicant',
        'property_description' => 'ZESCO commercial premises',
    ], false);
    Storage::fake('private');
    Gate::shouldReceive('authorize')->once()->with('lease_tenant_workflow.manage');

    $component = new LandlordApplicationPage();
    $component->selectedApplicationId = $application->id;
    $component->documentType = 'Tenant request';
    $component->supportingDocuments = [UploadedFile::fake()->create('request.pdf', 100, 'application/pdf')];
    $component->uploadDocuments();

    $document = TenantDocument::query()->sole();

    expect($document->documentable_type)->toBe($application->getMorphClass())
        ->and($document->documentable_id)->toBe($application->id)
        ->and($application->events()->where('action', 'document_uploaded')->exists())->toBeTrue();

    Storage::disk('private')->assertExists($document->storage_path);
});

test('global lease documents and history page combines lease process records', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);

    $lease = makeTenantLease();
    $workflow = app(LeaseTenantWorkflowService::class)->start($lease, 'Customer Service');
    app(LeaseLifecycleService::class)->createCase($lease, LeaseLifecycleCase::TYPE_TERMINATION, [
        'reason' => 'The premises are no longer required.',
    ], true);
    app(LeaseLandlordApplicationService::class)->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'External applicant',
        'property_description' => 'ZESCO-owned commercial property',
    ], true);
    TenantDocument::create([
        'documentable_type' => $workflow->getMorphClass(),
        'documentable_id' => $workflow->id,
        'document_type' => 'Landlord lease',
        'original_name' => 'signed-lease.pdf',
        'storage_path' => 'lease-workflows/' . $workflow->id . '/signed-lease.pdf',
        'uploaded_by' => $user->id,
        'uploaded_at' => now(),
    ]);

    Gate::shouldReceive('authorize')->once()->with('lease_tenant_workflow.view', []);
    $data = (new LeaseDocumentsHistory())->render()->getData();

    expect($data['documents']->total())->toBe(1)
        ->and($data['documents']->first()->original_name)->toBe('signed-lease.pdf')
        ->and($data['history']->total())->toBe(3);
});

test('landlord lease register lists only landlord-direction agreements', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $tenantLease = makeTenantLease();
    $landlordLease = LeaseAgreement::create([
        'zesco_role' => 'landlord',
        'contract_description' => 'ZESCO-owned office space leased to an external tenant',
        'landlord_name' => 'ZESCO Limited',
        'tenant_name' => 'Customer Service Ltd',
        'tenant_contact_name' => 'Tenant Contact',
        'tenant_contact_phone' => '0977000111',
        'property_reference' => 'ZL-PROP-100',
        'payment_period' => '3_months',
        'payment_amount' => '600000.00',
        'lease_status_id' => $tenantLease->lease_status_id,
    ]);

    Gate::shouldReceive('authorize')->once()->with('lease_agreements.view', []);
    $data = (new LandlordLeaseRegisterPage())->render()->getData();

    expect($data['totalLeases'])->toBe(1)
        ->and($data['leases']->total())->toBe(1)
        ->and($data['leases']->first()->id)->toBe($landlordLease->id)
        ->and($data['leases']->first()->tenant_name)->toBe('Customer Service Ltd')
        ->and($tenantLease->fresh()->zesco_role)->toBe('tenant');
});

test('lease expiry reminders notify the responsible officer once per reminder window', function () {
    $user = makeWorkflowUser();
    $this->actingAs($user);
    $lease = makeTenantLease();
    $lease->update([
        'responsible_officer_id' => $user->id,
        'end_date' => today()->addDays(30),
    ]);
    config(['lease_notifications.expiry_days' => [30]]);
    Notification::fake();

    $service = app(LeaseExpiryNotificationService::class);

    expect($service->sendDueReminders())->toBe(1)
        ->and($service->sendDueReminders())->toBe(0)
        ->and(LeaseExpiryNotificationLog::query()->count())->toBe(1);

    Notification::assertSentTo($user, LeaseExpiryNotification::class);
});

test('lease stage updates notify the initiator when another officer records the outcome', function () {
    Notification::fake();
    $initiator = makeWorkflowUser();
    $recorder = makeWorkflowUser();
    $this->actingAs($initiator);

    $application = app(LeaseLandlordApplicationService::class)->create([
        'origin' => LeaseLandlordApplication::ORIGIN_EXTERNAL_REQUEST,
        'external_tenant_name' => 'External applicant',
        'property_description' => 'ZESCO-owned property',
    ], true);

    $this->actingAs($recorder);
    app(LeaseLandlordApplicationService::class)->recordAction(
        $application,
        'tenant_feedback_received',
        'Tenant feedback recorded by another officer.'
    );

    Notification::assertSentTo($initiator, LeaseWorkflowUpdateNotification::class);
});

test('tenant-side stage updates notify the workflow initiator when recorded by another officer', function () {
    Notification::fake();
    $initiator = makeWorkflowUser();
    $recorder = makeWorkflowUser();
    $this->actingAs($initiator);
    $lease = makeTenantLease();
    $workflow = app(LeaseTenantWorkflowService::class)->start($lease, 'Customer Service');

    $this->actingAs($recorder);
    app(LeaseTenantWorkflowService::class)->recordAction($workflow, 'department_willing');

    Notification::assertSentTo($initiator, LeaseWorkflowUpdateNotification::class);
});

test('email connectivity test sends only to the signed-in user and uses mail channel', function () {
    Notification::fake();
    $user = makeWorkflowUser();
    $this->actingAs($user);

    (new NotificationPreferences())->sendTestEmail();

    Notification::assertSentOnDemand(
        EmailConnectivityTestNotification::class,
        fn ($notification, array $channels, $notifiable) => $channels === ['mail']
            && $notifiable->routeNotificationFor('mail') === $user->email
    );
});