<div>
    <x-slot name="title">Asset Register Configuration</x-slot>
    <x-slot name="pageHeader">Asset Register Configuration</x-slot>

    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div><h3 class="mb-1">Asset Register Configuration</h3><p class="text-muted mb-0">Manage dropdown options used by the Asset Register.</p></div>
            <a href="{{ route('asset-register.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Asset Register</a>
        </div>

        @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <section class="card mb-3">
            <div class="card-header text-white" style="background: {{ config('theme.color_primary') }}"><h5 class="mb-0">Add a dropdown option</h5></div>
            <div class="card-body">
                <form wire:submit="addValue">
                    <div class="row align-items-end">
                        <div class="form-group col-md-5">
                            <label for="asset-config-field">Field</label>
                            <select id="asset-config-field" class="form-control" wire:model.live="fieldKey">
                                @foreach($fields as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="asset-config-value">Option value</label>
                            <input id="asset-config-value" class="form-control" wire:model="newValue" maxlength="255">
                            @error('newValue') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group col-md-2"><button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i>Add</button></div>
                    </div>
                </form>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h5 class="mb-0">{{ $fields[$fieldKey] ?? 'Dropdown values' }}</h5></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: {{ config('theme.color_primary') }}; color: {{ config('theme.color_primary_text_light') }}"><tr><th>Value</th><th>Source</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($configured as $option)
                            <tr>
                                <td>{{ $option->value }}</td>
                                <td>Configuration</td>
                                <td>{{ $option->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="text-right"><button type="button" class="btn btn-sm btn-outline-secondary" wire:click="toggle({{ $option->id }})">{{ $option->is_active ? 'Deactivate' : 'Activate' }}</button></td>
                            </tr>
                        @endforeach
                        @foreach($importedOnlyValues as $value)
                            <tr>
                                <td>{{ $value }}</td>
                                <td>Existing asset data</td>
                                <td>Available</td>
                                <td></td>
                            </tr>
                        @endforeach
                        @if($configured->isEmpty() && $importedOnlyValues->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-4">No options exist for this field yet. Import data or add an option above.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
