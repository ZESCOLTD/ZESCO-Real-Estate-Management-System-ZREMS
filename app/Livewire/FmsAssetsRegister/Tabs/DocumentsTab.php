<?php

namespace App\Livewire\FmsAssetsRegister\Tabs;

use App\Models\AssetDocument;
use App\Models\DocumentType;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentsTab extends Component
{
    use WithFileUploads;

    public $assetId;
    public $documents = [];

    // form fields
    public $document_type, $title, $file;
    public $documentTypes = [];
    public function mount($assetId)
    {
        $this->assetId = $assetId;
        $this->documentTypes = DocumentType::
        orderBy('name')
            ->get();
        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        $this->documents = AssetDocument::where('asset_id', $this->assetId)
            ->latest()
            ->get();
    }

    public function saveDocument()
    {

        $this->validate([
            'title' => 'required',
            'file' => 'required|file|max:10240',
            'document_type' => 'required',
        ]);

        $path = $this->file->store('documents', 'public');
//dd($this);
        AssetDocument::create([
            'asset_id' => $this->assetId,
            'document_type' => $this->document_type,
            'title' => $this->title,
            'file_path' => $path,
            'mime_type' => $this->file->getMimeType(),
            'file_size' => $this->file->getSize(),
        ]);

        $this->reset(['document_type', 'title', 'file']);

        $this->loadDocuments();

        $this->dispatch('close-modal');
    }

    public function render()
    {
        return view('livewire.fms-assets-register.tabs.documents-tab');
    }
}
