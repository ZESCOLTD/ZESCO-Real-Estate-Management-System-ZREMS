<div>
    <x-slot name="title">Asset {{ $asset->asset_number }}</x-slot>
    <x-slot name="pageHeader">Asset Register Record</x-slot>

    <div class="container-fluid px-0">
        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div><h3 class="mb-1">Asset {{ $asset->asset_number }}</h3><p class="text-muted mb-0">View the imported asset values. Asset Number is the source-system key and cannot be changed here.</p></div>
            <a href="{{ route('asset-register.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Asset Register</a>
        </div>

        <form wire:submit="save">
            <section class="card">
                <div class="card-header text-white" style="background: {{ config('theme.color_primary') }}"><h5 class="mb-0">Official Register Fields</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6"><label>Asset Number</label><input class="form-control" value="{{ $asset->asset_number }}" readonly></div>
                        <div class="form-group col-md-6"><label>Asset Description</label><input class="form-control" wire:model="form.description" @disabled(!$canEdit)></div>
                        @foreach([
                            ['business_unit', 'BU', $businessUnits],
                            ['cost_centre', 'CC', $costCentres],
                            ['area', 'Area', $areas],
                            ['location', 'Location', $locations],
                            ['sub_location', 'Sub Location', $subLocations],
                            ['major_category', 'Major Category', $majorCategories],
                            ['sub_major_category', 'Sub Major Category', $subMajorCategories],
                            ['minor_category', 'Minor Category', $minorCategories],
                            ['status', 'Status', $statuses],
                            ['reportline', 'Reportline', $reportlines],
                        ] as [$field, $label, $options])
                            <div class="form-group col-md-6">
                                <label for="asset-field-{{ $field }}">{{ $label }}</label>
                                <select id="asset-field-{{ $field }}" class="form-control" wire:model="form.{{ $field }}" @disabled(!$canEdit)>
                                    <option value="">—</option>
                                    @if($form[$field] !== '' && ! $options->contains($form[$field]))
                                        <option value="{{ $form[$field] }}">{{ $form[$field] }}</option>
                                    @endif
                                    @foreach($options as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error("form.{$field}") <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        @endforeach
                        <div class="form-group col-md-6"><label>Date Placed in Service</label><input type="date" class="form-control" wire:model="form.date_placed_in_service" @disabled(!$canEdit)>@error('form.date_placed_in_service') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label>Life in Months</label><input type="number" min="0" class="form-control" wire:model="form.life_in_months" @disabled(!$canEdit)>@error('form.life_in_months') <small class="text-danger">{{ $message }}</small> @enderror</div>
                        <div class="form-group col-md-6"><label>Cost</label><input type="number" min="0" step="0.01" class="form-control" wire:model="form.asset_cost" @disabled(!$canEdit)>@error('form.asset_cost') <small class="text-danger">{{ $message }}</small> @enderror</div>
                    </div>
                </div>
            </section>
            @if($canEdit)
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"><i class="fas fa-save mr-1"></i>Save Changes</button>
                </div>
            @endif
        </form>
    </div>
</div>
