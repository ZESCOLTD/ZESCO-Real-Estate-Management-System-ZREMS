<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProposalForm extends Component
{
    public ?RealEstateProject $project = null;

    public array $form = [];

    public function mount(?RealEstateProject $project = null): void
    {
        $this->authorize($project ? 'real_estate_projects.update' : 'real_estate_projects.create');
        $this->ensureProposalCanBeEdited($project);
        $this->project = $project;
        $this->form = array_replace(
            $this->defaultForm(),
            $project?->proposal_data ?? []
        );

        $this->form['date_prepared'] ??= today()->toDateString();
    }

    public function addAlternative(): void
    {
        $this->form['alternatives'][] = [
            'option' => '',
            'advantage' => '',
            'limitation' => '',
        ];
    }

    public function removeAlternative(int $index): void
    {
        if (isset($this->form['alternatives'][$index])) {
            unset($this->form['alternatives'][$index]);
            $this->form['alternatives'] = array_values($this->form['alternatives']);
        }
    }

    public function addInitialRisk(): void
    {
        $this->form['initial_risks'][] = [
            'concern' => '',
            'consequence' => '',
            'further_assessment' => '',
        ];
    }

    public function removeInitialRisk(int $index): void
    {
        if (isset($this->form['initial_risks'][$index])) {
            unset($this->form['initial_risks'][$index]);
            $this->form['initial_risks'] = array_values($this->form['initial_risks']);
        }
    }

    public function saveDraft()
    {
        $this->authorize($this->project ? 'real_estate_projects.update' : 'real_estate_projects.create');
        $this->ensureProposalCanBeEdited($this->project);
        $this->validate($this->proposalValidationRules());

        $formData = Arr::only($this->form, array_keys($this->defaultForm()));
        $primaryIntervention = $formData['primary_intervention'] ?: null;
        if ($primaryIntervention === 'Other') {
            $primaryIntervention = $formData['other_intervention'] ?: 'Other';
        }

        $projectData = [
            'record_origin' => 'proposal',
            'lifecycle_status' => $this->project?->lifecycle_status ?? 'Proposed',
            'proposal_status' => $this->project?->proposal_status ?? 'Draft',
            'project_title' => $formData['proposed_project_title'] ?: null,
            'project_category' => $primaryIntervention,
            'property_name' => $formData['property_name'] ?: null,
            'property_reference' => $formData['property_reference'] ?: null,
            'town_location' => $formData['town_location'] ?: null,
            'responsible_officer' => $formData['real_estate_officer_preparing']
                ?: ($formData['proposing_officer_name'] ?: null),
            'indicative_cost' => $formData['indicative_cost'] ?: null,
            'proposal_data' => $formData,
        ];

        if ($this->project) {
            $this->project->fill($projectData)->save();
        } else {
            $this->project = RealEstateProject::create([
                ...$projectData,
                'created_by' => Auth::id(),
            ]);
        }

        session()->flash('success', 'Proposal draft saved.');

        return redirect()->route('real-estate-projects.show', $this->project);
    }

    public function render()
    {
        return view('livewire.real-estate-projects.proposal-form')
            ->layout('layouts.master', ['title' => $this->project?->reference_no ?? 'New Project Proposal']);
    }

    private function proposalValidationRules(): array
    {
        $rules = [];
        $arrayFields = [
            'alternatives',
            'initial_risks',
            'e2_land_status',
            'e2_layout_information',
            'stakeholders',
            'supporting_information',
        ];
        $dateFields = ['date_prepared', 'proposing_officer_date', 'date_received'];

        foreach ($this->defaultForm() as $field => $value) {
            $rules["form.{$field}"] = in_array($field, $arrayFields, true)
                ? ['nullable', 'array']
                : ['nullable', 'string', 'max:5000'];
        }

        foreach ($dateFields as $field) {
            $rules["form.{$field}"] = ['nullable', 'date'];
        }

        $rules['form.indicative_cost'] = ['nullable', 'numeric', 'min:0'];
        $rules['form.primary_intervention'] = ['nullable', 'in:New development,Rehabilitation,Office reconfiguration / partitioning,Extension,Conversion of use,Infrastructure improvement,Other'];
        $rules['form.next_step_options'] = ['nullable', 'in:Preliminary site assessment,Technical consultation,Options assessment,Feasibility study,Preliminary costing,Stakeholder engagement,Further information from requesting unit,Other'];
        $rules['form.alternatives.*.option'] = ['nullable', 'string', 'max:255'];
        $rules['form.alternatives.*.advantage'] = ['nullable', 'string', 'max:5000'];
        $rules['form.alternatives.*.limitation'] = ['nullable', 'string', 'max:5000'];
        $rules['form.initial_risks.*.concern'] = ['nullable', 'string', 'max:5000'];
        $rules['form.initial_risks.*.consequence'] = ['nullable', 'string', 'max:5000'];
        $rules['form.initial_risks.*.further_assessment'] = ['nullable', 'string', 'max:5000'];

        return $rules;
    }

    private function ensureProposalCanBeEdited(?RealEstateProject $project): void
    {
        if ($project && ! in_array($project->proposal_status, ['Draft', 'Returned for Revision', 'Rejected'], true)) {
            abort(403, 'Only draft or revision-needed proposals can be edited.');
        }
    }

    private function defaultForm(): array
    {
        return [
            'date_prepared' => null,
            'proposed_project_title' => '',
            'property_name' => '',
            'property_reference' => '',
            'town_location' => '',
            'building_structure_name' => '',
            'originating_department' => '',
            'contact_officer' => '',
            'real_estate_officer_preparing' => '',
            'primary_intervention' => '',
            'other_intervention' => '',
            'intervention_description' => '',
            'current_situation' => '',
            'operational_need' => '',
            'no_action_consequences' => '',
            'present_arrangement_alternative' => '',
            'main_objective' => '',
            'expected_benefits' => '',
            'current_capacity' => '',
            'expected_capacity' => '',
            'beneficiary_departments' => '',
            'existing_condition' => '',
            'principal_work_elements' => '',
            'areas_affected' => '',
            'supporting_technical_evidence' => '',
            'known_constraints' => '',
            'technical_investigations' => '',
            'statutory_safety_environmental_considerations' => '',
            'facility_type' => '',
            'proposed_site_plot_location' => '',
            'e2_land_status' => [],
            'other_land_arrangement' => '',
            'development_size_capacity' => '',
            'site_access_infrastructure' => '',
            'site_conditions_constraints' => '',
            'e2_layout_information' => [],
            'further_site_investigations' => '',
            'planning_environmental_safety_requirements' => '',
            'alternatives' => [
                ['option' => '', 'advantage' => '', 'limitation' => ''],
                ['option' => '', 'advantage' => '', 'limitation' => ''],
                ['option' => '', 'advantage' => '', 'limitation' => ''],
            ],
            'preferred_approach_reason' => '',
            'indicative_cost' => '',
            'cost_basis' => '',
            'resource_requirements' => '',
            'funding_information' => '',
            'desired_timeframe' => '',
            'timing_urgency_reason' => '',
            'implementation_dependencies' => '',
            'initial_risks' => [
                ['concern' => '', 'consequence' => '', 'further_assessment' => ''],
                ['concern' => '', 'consequence' => '', 'further_assessment' => ''],
                ['concern' => '', 'consequence' => '', 'further_assessment' => ''],
            ],
            'stakeholders' => [],
            'other_stakeholder' => '',
            'consultations_undertaken' => '',
            'matters_for_investigation' => '',
            'supporting_information' => [],
            'other_supporting_information' => '',
            'proposal_summary' => '',
            'next_step_options' => '',
            'other_next_step' => '',
            'specific_next_action' => '',
            'proposing_officer_name' => '',
            'proposing_officer_designation' => '',
            'proposing_officer_department' => '',
            'proposing_officer_date' => null,
            'proposing_officer_signature' => '',
            'acknowledged_by' => '',
            'date_received' => null,
            'acknowledgement_remarks' => '',
        ];
    }
}
