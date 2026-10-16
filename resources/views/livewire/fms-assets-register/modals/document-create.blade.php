{{-- MODAL --}}
<div wire:ignore.self
     class="modal fade"
     id="addDocumentModal"
     tabindex="-1"
     style="z-index: 999999 !important;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Upload Document</h5>
                <button class="btn-close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" wire:model="title" class="form-control">
                </div>

                <div class="mb-3" wire:ignore>
                    <label>Document Type</label>

                    <select id="acquisition-type-select"
                            class="form-control ts-static"
                            data-model="document_type" wire:model="document_type">
                        <option value="">Select</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="mb-3">
                    <label>File</label>
                    <input type="file" wire:model="file" class="form-control">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                {{--                    <button wire:click="saveDocument" class="btn btn-primary">Upload</button>--}}

                <button type="button"
                        class="btn text-white"
                        style="background: {{config('theme.color_accent')}}"
                        wire:click="saveDocument"
                        wire:loading.attr="disabled">

                    <span wire:loading.remove wire:target="saveDocument">
                        <i class="fas fa-save mr-1"></i> Upload
                    </span>

                    <span wire:loading wire:target="saveDocument">
                        <span class="spinner-border spinner-border-sm"></span>
                    </span>

                </button>
            </div>

        </div>
    </div>
</div>
