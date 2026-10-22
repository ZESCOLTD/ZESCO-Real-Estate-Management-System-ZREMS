<?php

namespace App\Livewire\RealEstateProjects;

use App\Models\RealEstateProject;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $this->authorize('real_estate_projects.view');

        $stats = [
            'total' => RealEstateProject::count(),
            'draft' => RealEstateProject::where('record_origin', 'proposal')->where('proposal_status', 'Draft')->count(),
            'under_review' => RealEstateProject::where('record_origin', 'proposal')->where('proposal_status', 'Under Review')->count(),
            'approved' => RealEstateProject::where('proposal_status', 'Approved')->count(),
            'implementation' => RealEstateProject::where('lifecycle_status', 'Ongoing')->count(),
            'completed' => RealEstateProject::where('lifecycle_status', 'Completed')->count(),
            'indicative_budget' => RealEstateProject::sum('indicative_cost'),
        ];

        $recentProjects = RealEstateProject::query()->latest()->limit(8)->get();

        return view('livewire.real-estate-projects.dashboard', compact('stats', 'recentProjects'))
            ->layout('layouts.master', ['title' => 'Project Dashboard']);
    }
}
