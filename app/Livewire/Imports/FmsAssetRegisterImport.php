<?php
namespace App\Livewire\Imports;

use App\Jobs\ProcessAssetImportJob;
use App\Models\FmsAssetRegister;
use App\Models\ImportJob;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class FmsAssetRegisterImport extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $file;
    public $jobId = null;

    // Filters
    public $search = '';
    public $filterCategory = '';

    public $showTable = false; // Toggle to show/hide the data list
    public function toggleTable()
    {
        $this->showTable = !$this->showTable;
    }
    public function processImport()
    {
//        dd('UPLOAD HIT');
        try {
            $this->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB
            ]);
        $path = $this->file->store('imports');

        $job = ImportJob::create([
            'file_path' => $path,
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'failed_rows' => 0,
        ]);

        $this->jobId = $job->id;

        ProcessAssetImportJob::dispatch($job->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Something went wrong: ' . $e->getMessage());
        } finally {
            // This runs whether the code succeeded or crashed
            $this->reset('file');
            $this->showTable = true; // Show table when import starts
        }
    }


// This fetches the actual imported data
    public function getAssetsProperty()
    {
        return FmsAssetRegister::query()
            ->when($this->search, function($q) {
                $q->where('asset_number', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('tag_number', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterCategory, function($q) {
                $q->where('major_category', $this->filterCategory);
            })
            ->latest()
            ->paginate(10);
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    // ✅ ALWAYS FRESH DATA (THIS IS THE FIX)
    public function getJobProperty()
    {
        return $this->jobId
            ? ImportJob::find($this->jobId)
            : null;
    }

    public function render()
    {

        return view('livewire.imports.fms-asset-register-import')
            ->layout('layouts.master');
    }
}
