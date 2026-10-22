<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantRegister as TenantRecord;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $stationLocation = '';
    public string $category = '';
    public string $employer = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStationLocation(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingEmployer(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('tenant_register.view');

        $query = TenantRecord::query()
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%' . $this->search . '%';
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('current_tenant', 'like', $search)
                        ->orWhere('man_no', 'like', $search)
                        ->orWhere('employer', 'like', $search)
                        ->orWhere('physical_address', 'like', $search)
                        ->orWhere('station_location', 'like', $search);
                });
            })
            ->when($this->stationLocation !== '', fn (Builder $query) => $query->where('station_location', $this->stationLocation))
            ->when($this->category !== '', fn (Builder $query) => $query->where('category', $this->category))
            ->when($this->employer !== '', fn (Builder $query) => $query->where('employer', $this->employer));

        return view('livewire.tenant-register.index', [
            'tenants' => (clone $query)->orderBy('station_location')->orderBy('category')->orderBy('physical_address')->paginate(10),
            'totalTenants' => TenantRecord::count(),
            'vacantUnits' => TenantRecord::query()->whereRaw('LOWER(TRIM(current_tenant)) = ?', ['vacant'])->count(),
            'stations' => TenantRecord::query()->whereNotNull('station_location')->distinct()->orderBy('station_location')->pluck('station_location'),
            'categories' => TenantRecord::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'employers' => TenantRecord::query()->whereNotNull('employer')->where('employer', '<>', '')->distinct()->orderBy('employer')->pluck('employer'),
        ])->layout('layouts.master', ['title' => 'ZESCO Tenants Register']);
    }
}