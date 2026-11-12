<?php

namespace App\Livewire\TenantRegister;

use App\Models\TenantDocument;
use App\Models\TenantOnboardingApplication;
use App\Models\TenantRegister as TenantRecord;
use App\Services\TenantHistoryRecorder;
use App\Models\WorkflowInstance;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $reviewStatus = '';
    public array $reviewComments = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingReviewStatus(): void
    {
        $this->resetPage();
    }

    public function review(int $documentId, string $status, TenantHistoryRecorder $history): void
    {
        $this->authorize('tenant_register.verify');

        if (! in_array($status, ['Accepted', 'Needs Replacement'], true)) {
            throw ValidationException::withMessages(['review' => 'Choose an allowed document review status.']);
        }

        $document = TenantDocument::query()->findOrFail($documentId);
        $owner = $document->documentable;
        if (! $owner) {
            abort(404);
        }

        if ($owner instanceof TenantOnboardingApplication) {
            abort_unless($owner->status === 'Pending Verification', 403);
            $workflow = WorkflowInstance::query()
                ->where('workflowable_type', $owner->getMorphClass())
                ->where('workflowable_id', $owner->id)
                ->where('status', 'In Progress')
                ->latest('id')
                ->firstOrFail();
            abort_unless(in_array((int) auth()->id(), array_map('intval', $workflow->current_approver_ids ?? []), true), 403);
        }

        $comment = trim((string) ($this->reviewComments[$documentId] ?? ''));
        if ($status === 'Needs Replacement' && $comment === '') {
            $this->addError("reviewComments.{$documentId}", 'A comment is required when requesting a replacement.');

            return;
        }

        $document->update([
            'review_status' => $status,
            'review_comment' => $comment ?: null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $history->record($owner, 'document_reviewed', 'A supporting document was reviewed.', [
            'document_id' => $document->id,
            'document_type' => $document->document_type,
            'review_status' => $status,
            'comment' => $comment ?: null,
        ]);

        unset($this->reviewComments[$documentId]);
        session()->flash('success', 'Document review saved.');
    }

    public function render()
    {
        $this->authorize('tenant_register.documents.view');

        $documents = TenantDocument::query()
            ->with(['documentable', 'uploader', 'reviewer'])
            ->when($this->search !== '', function ($query): void {
                $search = '%' . $this->search . '%';
                $query->where(function ($query) use ($search): void {
                    $query->where('original_name', 'like', $search)
                        ->orWhere('document_type', 'like', $search)
                        ->orWhere('review_comment', 'like', $search);
                });
            })
            ->when($this->reviewStatus !== '', fn ($query) => $query->where('review_status', $this->reviewStatus))
            ->latest('uploaded_at')
            ->paginate(20);

        return view('livewire.tenant-register.documents', [
            'documents' => $documents,
            'totalDocuments' => TenantDocument::count(),
        ])->layout('layouts.master', ['title' => 'Tenant Documents']);
    }
}