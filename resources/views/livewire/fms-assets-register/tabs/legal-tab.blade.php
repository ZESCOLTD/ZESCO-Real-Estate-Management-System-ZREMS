<div class="tab-pane fade" id="legal">

    {{-- Stakeholders --}}
    <div class="card mb-3">
        <div class="card-header">Stakeholders</div>
        <div class="card-body">

            @foreach($parcel->stakeholders as $s)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $s->name }}</span>
                    <span class="badge bg-secondary">{{ $s->pivot->role }}</span>
                </div>
            @endforeach

        </div>
    </div>

    {{-- Wayleaves --}}
    <div class="card mb-3">
        <div class="card-header">Wayleaves</div>
        <div class="card-body">

            @foreach($parcel->wayleaves as $w)
                <p>{{ $w->type }} - {{ $w->status }}</p>
            @endforeach

        </div>
    </div>

</div>
