<?php

namespace App\Livewire\FmsAssetsRegister;

use App\Jobs\SyncAllAssetsFromFmsJob;
use App\Models\FmsAssetRegister;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterCategory = '';

    public $expandedGroups = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategory' => ['except' => ''],
    ];
    public $syncRunning = false;
    public $syncedAssets = 0;
    public $totalSyncAssets = 0;
    public $syncPercentage = 0;
    /* ----------------------------
     | RESET PAGINATION ON CHANGE
     ---------------------------- */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    /* ----------------------------
     | TOGGLE GROUP
     ---------------------------- */
    public function toggleGroup($linkId)
    {
        if (in_array($linkId, $this->expandedGroups)) {
            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$linkId]));
        } else {
            $this->expandedGroups[] = $linkId;
        }
    }

    /* ----------------------------
     | DISTINCT CATEGORIES (DROPDOWN)
     ---------------------------- */
    public function getCategoriesProperty()
    {
        return FmsAssetRegister::query()
            ->select('major_category')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('major_category')
            ->groupBy('major_category')
            ->orderByDesc('total')
            ->get();
    }

    /* ----------------------------
     | GROUPED RESULTS (SMART SEARCH)
     ---------------------------- */
    public function getGroupsProperty()
    {
        $search = strtolower(trim($this->search));

        $baseQuery = FmsAssetRegister::query()
            ->when(strlen($search) >= 3, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(asset_number) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(tag_number) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->filterCategory, function ($q) {
                $q->where('major_category', $this->filterCategory);
            });

        $groups = (clone $baseQuery)
            ->select('link_id', DB::raw('COUNT(*) as total'))
            ->groupBy('link_id')
            ->orderByDesc('total')
            ->paginate(10);

        // attach smart group names
        $linkIds = $groups->pluck('link_id');

        $names = FmsAssetRegister::query()
            ->whereIn('link_id', $linkIds)
            ->whereNotNull('description')
            ->select('link_id', 'description')
            ->get()
            ->groupBy('link_id')
            ->map(fn ($items) => $items->countBy('description')->sortDesc()->keys()->first());

        foreach ($groups as $group) {
            $group->name = $names[$group->link_id] ?? 'Unnamed Group';
        }

        return $groups;
    }

    /* ----------------------------
     | CHILD ITEMS (LAZY)
     ---------------------------- */
    public function getGroupItems($linkId)
    {
        return FmsAssetRegister::query()
            ->where('link_id', $linkId)
            ->when(strlen($this->search) >= 3, function ($q) {
                $search = strtolower($this->search);

                $q->where(function ($q2) use ($search) {
                    $q2->whereRaw('LOWER(asset_number) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(tag_number) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($this->filterCategory, fn ($q) =>
            $q->where('major_category', $this->filterCategory)
            )
            ->limit(200)
            ->get();
    }

    /* ----------------------------
     | STATS
     ---------------------------- */
    public function getStatsProperty()
    {
        return [
            'total' => FmsAssetRegister::count(),
            'grouped' => FmsAssetRegister::distinct('link_id')->count('link_id'),
        ];
    }


    /* --------------------------------
    |SYNC WITH FIXED ASSETS
    ----------------------------------*/
    public function startFullAssetSync()
    {

        $this->syncRunning = true;

        Cache::put('fms_sync_total', FmsAssetRegister::count());
        Cache::put('fms_sync_count', 0);
        Cache::put('fms_sync_running', true);

        SyncAllAssetsFromFmsJob::dispatch();

        session()->flash('success', 'Background sync started.');
    }

    public function refreshAssetSyncProgress()
    {

        $this->syncRunning = Cache::get('fms_sync_running', false);

        $this->syncedAssets = Cache::get('fms_sync_count', 0);

        $this->totalSyncAssets = Cache::get('fms_sync_total', 0);

        $this->syncPercentage = $this->totalSyncAssets > 0
            ? round(($this->syncedAssets / $this->totalSyncAssets) * 100)
            : 0;
    }

    public function render()
    {
        return view('livewire.fms-assets-register.index', [
            'groups' => $this->groups,
        ])->layout('layouts.master');
    }
}
