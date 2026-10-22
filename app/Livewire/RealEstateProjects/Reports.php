<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $status = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function export()
    {
        $this->authorize('real_estate_projects.export');

        $projects = $this->queryProjects()->get();

        return response()->streamDownload(function () use ($projects): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Project reference',
                'Project title',
                'Category',
                'Record origin',
                'Property reference',
                'Location',
                'Responsible officer',
                'Start date',
                'Expected completion date',
                'Indicative budget',
                'Approved budget',
                'Lifecycle status',
                'Proposal status',
                'Created',
            ]);

            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->reference_no,
                    $project->project_title,
                    $project->project_category,
                    $project->record_origin,
                    $project->property_reference,
                    $project->town_location,
                    $project->responsible_officer,
                    $project->start_date?->toDateString(),
                    $project->expected_completion_date?->toDateString(),
                    $project->indicative_cost,
                    $project->approved_budget,
                    $project->lifecycle_status,
                    $project->proposal_status,
                    $project->created_at?->toDateString(),
                ]);
            }

            fclose($file);
        }, 'real-estate-projects.csv');
    }

    public function render()
    {
        $this->authorize('real_estate_projects.view');

        return view('livewire.real-estate-projects.reports', [
            'projects' => $this->queryProjects()->paginate(15),
            'statuses' => RealEstateProject::query()->select('lifecycle_status')->distinct()->orderBy('lifecycle_status')->pluck('lifecycle_status'),
            'totalBudget' => RealEstateProject::query()
                ->when($this->status !== '', fn ($query) => $query->where('lifecycle_status', $this->status))
                ->sum('indicative_cost'),
        ])->layout('layouts.master', ['title' => 'Project Reports']);
    }

    private function queryProjects()
    {
        return RealEstateProject::query()
            ->when($this->status !== '', fn ($query) => $query->where('lifecycle_status', $this->status))
            ->latest();
    }
}
