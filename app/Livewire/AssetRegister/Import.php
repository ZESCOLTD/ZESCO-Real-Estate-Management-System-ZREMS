<?php

namespace App\Livewire\AssetRegister;

use App\Jobs\ProcessOfficialAssetRegisterImportJob;
use App\Models\ImportError;
use App\Models\ImportJob;
use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    public $file;
    public ?int $jobId = null;

    public function mount(): void
    {
        $this->authorize('asset_register.create');
    }

    public function processImport(): void
    {
        $this->authorize('asset_register.create');
        $validated = $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:51200'],
        ]);

        $path = $validated['file']->store('asset-register-imports');
        $job = ImportJob::create([
            'file_path' => $path,
            'status' => 'pending',
            'total_rows' => 0,
            'processed_rows' => 0,
            'failed_rows' => 0,
        ]);

        $this->jobId = $job->id;
        ProcessOfficialAssetRegisterImportJob::dispatch($job->id);
        $this->reset('file');
    }

    public function getJobProperty(): ?ImportJob
    {
        return $this->jobId ? ImportJob::find($this->jobId) : null;
    }

    public function getErrorsProperty()
    {
        return $this->jobId
            ? ImportError::query()->where('import_job_id', $this->jobId)->orderBy('id')->get()
            : collect();
    }

    public function render()
    {
        return view('livewire.asset-register.import')
            ->layout('layouts.master', ['title' => 'Import Asset Register']);
    }
}
