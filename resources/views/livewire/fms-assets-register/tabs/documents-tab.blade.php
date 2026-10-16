<div>


    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Documents</span>

{{--            <button class="btn btn-sm btn-primary"--}}
{{--                    data-toggle="modal"--}}
{{--                    data-target="#addDocumentModal">--}}
{{--                + Upload--}}
{{--            </button>--}}
            <button
                class="btn btn-sm text-white bg-warning mr-2"
                data-toggle="modal"
                data-target="#addDocumentModal">
                <i class="fas fa-plus mr-1"></i>
                Upload
            </button>
        </div>

        <div class="card-body">

            @forelse($documents as $doc)
                <div class="d-flex justify-content-between border-bottom py-2 align-items-center">

                    <div>
                        <strong>{{ $doc->title }}</strong><br>
                        <small class="text-muted">{{ $doc->documentType->name ?? "-" }}</small>
                    </div>

{{--                    <div class="d-flex gap-2">--}}
{{--                        <a href="{{ Storage::url($doc->file_path) }}"--}}
{{--                           target="_blank"--}}
{{--                           class="btn btn-sm btn-outline-primary">--}}
{{--                            View--}}
{{--                        </a>--}}
{{--                    </div>--}}
                    <div class="text-center">
                        <div class="input-group-prepend">
                            <button type="button"
                                    class="btn btn-outline-warning dropdown-toggle"
                                    data-toggle="dropdown">
                                Action <span class="caret"></span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-right">

                                <a class="dropdown-item"
                                   href="{{ Storage::url($doc->file_path) }}"  target="_blank">
                                    View
                                </a>


                            </div>
                        </div>
                    </div>

                </div>
            @empty
                <p class="text-muted text-center">No documents uploaded</p>
            @endforelse

        </div>
    </div>

    @include('fms-assets-register.modals.document-create')

</div>
