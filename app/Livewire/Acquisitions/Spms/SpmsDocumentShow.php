<?php

namespace App\Livewire\Acquisitions\Spms;

use App\Models\SpmsDocumentAuditTrialView;
use App\Models\SpmsDocumentTrackerView;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SpmsDocumentShow extends Component
{
    public $searchTerm;

    public $documentReference;

    public $prReference;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->searchTerm = null;
    }

    //    public function updatingSearchTerm()
    //    {
    //        $this->resetPage();
    //    }

    public function render()
    {
        if (! $this->searchTerm) {
            $datasetAudit = null;
        } else {
            $datasetAudit = SpmsDocumentAuditTrialView::query()
//                ->whereIn('status', ['01', '22'])
                ->when($this->searchTerm, function ($query, $searchTerm) {
                    $searchTerm = strtolower(trim($searchTerm));

                    $query->where(function ($query) use ($searchTerm) {
                        $query->where(DB::raw('SUBSTR(LOWER(code_identification), 5)'), 'like', '%'.$searchTerm.'%')
                            ->orWhere(DB::raw('SUBSTR(LOWER(code_identification), 6)'), 'like', '%'.$searchTerm.'%')
                            ->orWhere(DB::raw('LOWER(code_identification)'), 'like', '%'.$searchTerm.'%');
                    });
                })
                ->orderBy(DB::raw('date_generation'), 'desc')
                ->first();
        }

        if (! $this->searchTerm) {
            $dataset = [];
        } else {
            $dataset = SpmsDocumentTrackerView::query()
                ->where(DB::raw('LOWER(document_no)'), 'like', '%'.strtolower($this->searchTerm).'%')
                ->get();
        }

        return view('livewire.acquisitions.spms.spms-document-show', compact('datasetAudit', 'dataset'))
            ->layout('layouts.master');
    }

    public function generateNumber(): void
    {
        $exitCode = Artisan::call('purchase:process');
        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            session()->flash('error', 'The purchase process command failed.');

            return;
        }

        $pattern = '/(?<office_code>[A-Z0-9]+)\s+DOCUMENT\s+NO:\s*'
            .'(?<document_no>[A-Z0-9]+)\s+PR\s+NO:\s*(?<pr_no>[A-Z0-9]+)/i';

        if (preg_match($pattern, $output, $matches) !== 1) {
            session()->flash('error', 'The command did not return document and PR numbers.');

            return;
        }

        $this->documentReference = sprintf(
            '%s DOCUMENT NO: %s',
            $matches['office_code'],
            $matches['document_no']
        );
        $this->prReference = 'PR NO: '.$matches['pr_no'];

        session()->flash('success', $this->documentReference.' '.$this->prReference);
    }
}
