<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExistingProjectForm extends Component
{
    public array $form = [
        'project_title' => '',
        'project_category' => '',
        'other_project_category' => '',
        'property_name' => '',
        'property_reference' => '',
        'town_location' => '',
        'responsible_officer' => '',
        'lifecycle_status' => 'Ongoing',
        'indicative_cost' => '',
        'approved_budget' => '',
        'start_date' => '',
        'expected_completion_date' => '',
    ];

    public function mount(): void
    {
        $this->authorize('real_estate_projects.create');
    }

    public function save()
    {
        $this->authorize('real_estate_projects.create');

        $validated = $this->validate([
            'form.project_title' => ['required', 'string', 'max:255'],
            'form.project_category' => ['nullable', 'in:New development,Rehabilitation,Office reconfiguration / partitioning,Extension,Conversion of use,Infrastructure improvement,Other'],
            'form.other_project_category' => ['nullable', 'string', 'max:255'],
            'form.property_name' => ['nullable', 'string', 'max:255'],
            'form.property_reference' => ['nullable', 'string', 'max:255'],
            'form.town_location' => ['nullable', 'string', 'max:255'],
            'form.responsible_officer' => ['nullable', 'string', 'max:255'],
            'form.lifecycle_status' => ['required', 'in:Planned,Ongoing,On Hold,Completed,Cancelled'],
            'form.indicative_cost' => ['nullable', 'numeric', 'min:0'],
            'form.approved_budget' => ['nullable', 'numeric', 'min:0'],
            'form.start_date' => ['nullable', 'date'],
            'form.expected_completion_date' => ['nullable', 'date', 'after_or_equal:form.start_date'],
        ])['form'];

        $category = $validated['project_category'];
        if ($category === 'Other') {
            $category = $validated['other_project_category'] ?: 'Other';
        }

        $project = RealEstateProject::create([
            'record_origin' => 'existing',
            'lifecycle_status' => $validated['lifecycle_status'],
            'proposal_status' => null,
            'project_title' => $validated['project_title'],
            'project_category' => $category,
            'property_name' => $validated['property_name'] ?: null,
            'property_reference' => $validated['property_reference'] ?: null,
            'town_location' => $validated['town_location'] ?: null,
            'responsible_officer' => $validated['responsible_officer'] ?: null,
            'indicative_cost' => $validated['indicative_cost'] ?: null,
            'approved_budget' => $validated['approved_budget'] ?: null,
            'start_date' => $validated['start_date'] ?: null,
            'expected_completion_date' => $validated['expected_completion_date'] ?: null,
            'created_by' => Auth::id(),
        ]);

        session()->flash('success', 'Existing project added to the register.');

        return redirect()->route('real-estate-projects.show', $project);
    }

    public function render()
    {
        return view('livewire.real-estate-projects.existing-project-form')
            ->layout('layouts.master', ['title' => 'Register Existing Project']);
    }
}
