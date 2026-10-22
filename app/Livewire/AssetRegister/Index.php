<?php

namespace App\Livewire\AssetRegister;

use App\Models\AssetRegister;
use App\Models\AssetRegisterConfiguration;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $businessUnit = '';
    public string $costCentre = '';
    public string $area = '';
    public string $location = '';
    public string $subLocation = '';
    public string $majorCategory = '';
    public string $status = '';
    public string $reportline = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBusinessUnit(): void
    {
        $this->resetPage();
    }

    public function updatingCostCentre(): void
    {
        $this->resetPage();
    }

    public function updatingArea(): void
    {
        $this->resetPage();
    }

    public function updatingLocation(): void
    {
        $this->resetPage();
    }

    public function updatingSubLocation(): void
    {
        $this->resetPage();
    }

    public function updatingMajorCategory(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingReportline(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('asset_register.view');

        $assets = AssetRegister::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query->where('asset_number', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->businessUnit !== '', fn ($query) => $query->where('business_unit', $this->businessUnit))
            ->when($this->costCentre !== '', fn ($query) => $query->where('cost_centre', $this->costCentre))
            ->when($this->area !== '', fn ($query) => $query->where('area', $this->area))
            ->when($this->location !== '', fn ($query) => $query->where('location', $this->location))
            ->when($this->subLocation !== '', fn ($query) => $query->where('sub_location', $this->subLocation))
            ->when($this->majorCategory !== '', fn ($query) => $query->where('major_category', $this->majorCategory))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->reportline !== '', fn ($query) => $query->where('reportline', $this->reportline))
            ->orderBy('asset_number')
            ->paginate(10);

        $stats = [
            'total' => AssetRegister::count(),
            'locations' => AssetRegister::query()->whereNotNull('location')->distinct()->count('location'),
        ];
        $categoryCounts = AssetRegister::query()
            ->select('major_category')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('major_category')
            ->where('major_category', '<>', '')
            ->groupBy('major_category')
            ->orderByDesc('total')
            ->get();

        return view('livewire.asset-register.index', [
            'assets' => $assets,
            'stats' => $stats,
            'categoryCounts' => $categoryCounts,
            'businessUnits' => AssetRegisterConfiguration::valuesFor('business_unit'),
            'costCentres' => AssetRegisterConfiguration::valuesFor('cost_centre'),
            'areas' => AssetRegisterConfiguration::valuesFor('area'),
            'locations' => AssetRegisterConfiguration::valuesFor('location'),
            'subLocations' => AssetRegisterConfiguration::valuesFor('sub_location'),
            'majorCategories' => AssetRegisterConfiguration::valuesFor('major_category'),
            'statuses' => AssetRegisterConfiguration::valuesFor('status'),
            'reportlines' => AssetRegisterConfiguration::valuesFor('reportline'),
        ])->layout('layouts.master', ['title' => 'Asset Register']);
    }
}
