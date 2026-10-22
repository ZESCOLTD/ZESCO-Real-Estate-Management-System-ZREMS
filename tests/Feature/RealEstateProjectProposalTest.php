<?php

use App\Livewire\RealEstateProjects\ExistingProjectForm;
use App\Livewire\RealEstateProjects\ProposalForm;
use App\Models\RealEstateProject;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

test('saving the proposal form creates one draft project with a generated reference', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate([
        'name' => 'real_estate_projects.create',
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo($permission);

    $this->actingAs($user);

    Livewire::test(ProposalForm::class)
        ->set('form.proposed_project_title', 'Regional office rehabilitation')
        ->set('form.property_name', 'Central Office')
        ->set('form.property_reference', 'PROP-1024')
        ->set('form.town_location', 'Lusaka')
        ->set('form.primary_intervention', 'Rehabilitation')
        ->set('form.operational_need', 'Existing space is no longer fit for use.')
        ->set('form.indicative_cost', '250000.00')
        ->set('form.alternatives.0.option', 'Maintain the present arrangement')
        ->set('form.initial_risks.0.concern', 'Operational disruption')
        ->set('form.next_step_options', 'Feasibility study')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $project = RealEstateProject::query()->sole();

    expect($project->reference_no)->toMatch('/^REP-' . now()->format('Y') . '-\d{3,}$/')
        ->and($project->record_origin)->toBe('proposal')
        ->and($project->lifecycle_status)->toBe('Proposed')
        ->and($project->proposal_status)->toBe('Draft')
        ->and($project->project_title)->toBe('Regional office rehabilitation')
        ->and($project->proposal_data['operational_need'])->toBe('Existing space is no longer fit for use.')
        ->and($project->proposal_data['alternatives'][0]['option'])->toBe('Maintain the present arrangement')
        ->and($project->proposal_data['initial_risks'][0]['concern'])->toBe('Operational disruption')
        ->and($project->proposal_data['next_step_options'])->toBe('Feasibility study');
});

test('existing projects can be registered without a proposal', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate([
        'name' => 'real_estate_projects.create',
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo($permission);

    $this->actingAs($user);

    Livewire::test(ExistingProjectForm::class)
        ->set('form.project_title', 'Existing completed office project')
        ->set('form.lifecycle_status', 'Completed')
        ->set('form.start_date', '2024-01-15')
        ->call('save')
        ->assertHasNoErrors();

    $project = RealEstateProject::query()->sole();

    expect($project->reference_no)->toMatch('/^REP-' . now()->format('Y') . '-\d{3,}$/')
        ->and($project->record_origin)->toBe('existing')
        ->and($project->lifecycle_status)->toBe('Completed')
        ->and($project->proposal_status)->toBeNull()
        ->and($project->proposal_data)->toBeNull();
});
