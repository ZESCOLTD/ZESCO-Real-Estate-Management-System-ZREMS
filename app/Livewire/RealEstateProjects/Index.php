<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $status = '';
    public string $scope = 'register';

    public function mount(): void
    {
        $this->scope = request()->routeIs('real-estate-projects.proposals') ? 'proposals' : 'register';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('real_estate_projects.view');

        $scope = $this->scope;
        $statusColumn = $scope === 'proposals' ? 'proposal_status' : 'lifecycle_status';
        $statusOptions = $scope === 'proposals'
            ? ['Draft', 'Under Review', 'Returned for Revision', 'Rejected', 'Approved']
            : ['Planned', 'Ongoing', 'On Hold', 'Completed', 'Cancelled'];

        $projects = RealEstateProject::query()
            ->when($scope === 'proposals', fn ($query) => $query->where('record_origin', 'proposal'))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('reference_no', 'like', '%' . $this->search . '%')
                        ->orWhere('project_title', 'like', '%' . $this->search . '%')
                        ->orWhere('property_name', 'like', '%' . $this->search . '%')
                        ->orWhere('property_reference', 'like', '%' . $this->search . '%')
                        ->orWhere('town_location', 'like', '%' . $this->search . '%')
                        ->orWhere('responsible_officer', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', fn ($query) => $query->where($statusColumn, $this->status))
            ->latest()
            ->paginate(15);

        return view('livewire.real-estate-projects.index', compact('projects', 'scope', 'statusOptions'))
            ->layout('layouts.master', ['title' => $scope === 'proposals' ? 'Project Proposals' : 'Project Register']);
    }
}
