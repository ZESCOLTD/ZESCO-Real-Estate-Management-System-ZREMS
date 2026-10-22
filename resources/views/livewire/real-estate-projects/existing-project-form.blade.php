<div>
    <x-slot name="title">Register Existing Project</x-slot>
    <x-slot name="pageHeader">Register Existing Project</x-slot>

    <div class="container-fluid px-0">
        <div class="mb-3">
            <a href="{{ route('real-estate-projects.register') }}" class="btn btn-outline-secondary btn-sm mb-2"><i class="fas fa-arrow-left mr-1"></i> Project Register</a>
            <h3 class="mb-1">Register Existing Project</h3>
            <p class="text-muted mb-0">Add an ongoing, completed or otherwise existing project without creating a proposal.</p>
        </div>

        <form wire:submit="save">
            <section class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Project Details</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="project_title">Project title</label>
                        <input id="project_title" class="form-control" wire:model="form.project_title">
                        @error('form.project_title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="project_category">Project category</label>
                            <select id="project_category" class="form-control" wire:model.live="form.project_category">
                                <option value="">Select category</option>
                                <option>New development</option>
                                <option>Rehabilitation</option>
                                <option>Office reconfiguration / partitioning</option>
                                <option>Extension</option>
                                <option>Conversion of use</option>
                                <option>Infrastructure improvement</option>
                                <option>Other</option>
                            </select>
                            @error('form.project_category') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        @if($form['project_category'] === 'Other')
                            <div class="form-group col-md-6">
                                <label for="other_project_category">Other category</label>
                                <input id="other_project_category" class="form-control" wire:model="form.other_project_category">
                            </div>
                        @endif
                        <div class="form-group col-md-6">
                            <label for="lifecycle_status">Project status</label>
                            <select id="lifecycle_status" class="form-control" wire:model="form.lifecycle_status">
                                <option>Planned</option>
                                <option>Ongoing</option>
                                <option>On Hold</option>
                                <option>Completed</option>
                                <option>Cancelled</option>
                            </select>
                            @error('form.lifecycle_status') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Property and Responsibility</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label for="property_name">Property name</label><input id="property_name" class="form-control" wire:model="form.property_name"></div>
                        <div class="form-group col-md-6"><label for="property_reference">Property reference, if available</label><input id="property_reference" class="form-control" wire:model="form.property_reference"></div>
                        <div class="form-group col-md-6"><label for="town_location">Town / location</label><input id="town_location" class="form-control" wire:model="form.town_location"></div>
                        <div class="form-group col-md-6"><label for="responsible_officer">Responsible officer</label><input id="responsible_officer" class="form-control" wire:model="form.responsible_officer"></div>
                    </div>
                </div>
            </section>

            <section class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Budget and Timeline</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label for="indicative_cost">Indicative budget</label><input id="indicative_cost" type="number" min="0" step="0.01" class="form-control" wire:model="form.indicative_cost">@error('form.indicative_cost') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label for="approved_budget">Approved budget</label><input id="approved_budget" type="number" min="0" step="0.01" class="form-control" wire:model="form.approved_budget">@error('form.approved_budget') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label for="start_date">Start date</label><input id="start_date" type="date" class="form-control" wire:model="form.start_date">@error('form.start_date') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label for="expected_completion_date">Expected completion date</label><input id="expected_completion_date" type="date" class="form-control" wire:model="form.expected_completion_date">@error('form.expected_completion_date') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    </div>
                </div>
            </section>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="fas fa-save mr-1"></i> Add to Project Register</span>
                    <span wire:loading><span class="spinner-border spinner-border-sm mr-1"></span> Saving...</span>
                </button>
            </div>
        </form>
    </div>
</div>
