<div class="container-fluid px-0 bg-light">

    @php
        $category = strtolower($asset->major_category ?? '');

        $icon = match(true) {
            str_contains($category, 'land') => '🌍',
            str_contains($category, 'building') => '🏢',
            str_contains($category, 'vehicle') => '🚗',
            str_contains($category, 'equipment') => '🛠️',
            default => '📦',
        };
    @endphp


    {{-- ================= HEADER ================= --}}
    <div class="position-relative bg-primary text-white"
         style="height:260px; overflow:hidden;">

        {{-- Background --}}
        <div class="position-absolute w-100 h-100"
             style="background-image:url('{{ asset('assets-dashboard/img/login_bg2.png') }}');
                    background-size:cover;
                    background-position:center;
                    opacity:0.25;">
        </div>

        {{-- Content --}}
        <div class="container h-100 d-flex align-items-center justify-content-between">

            {{-- LEFT: IDENTITY --}}
            <div class="d-flex align-items-center gap-3">

                <div style="
                    width:90px;height:90px;
                    border-radius:18px;
                    background:white;
                    color:#111;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:34px;
                    box-shadow:0 10px 25px rgba(0,0,0,0.15);
                ">
                    {{ $icon }}
                </div>

                <div class="ml-2">
                    <h4 class="fw-bold mb-1 text-uppercase">
                        {{ $asset->description }}
                    </h4>
                    <div class="opacity-75">
                        {{ $asset->town }}
                    </div>

                    {{-- meta chips --}}
                    <div class="mt-2 d-flex gap-2 flex-wrap">

                        <span class="badge bg-light text-dark">
                            {{ strtoupper($asset->major_category ?? 'ASSET') }}
                        </span>

{{--                        <span class="badge bg-success-subtle text-success border">--}}
{{--                            ● Active--}}
{{--                        </span>--}}

{{--                        <span class="badge bg-dark bg-opacity-25 text-white border border-light">--}}
{{--                            VIEW MODE--}}
{{--                        </span>--}}

                    </div>

                </div>

            </div>


            {{-- RIGHT: FACTS PANEL --}}
            <div class="text-end small opacity-75 d-none d-md-block">

                <div>Asset ID</div>
                <div class="fw-bold text-white">
                    {{ $asset->lams_asset_number ?? 'N/A' }}
                </div>

                <div class="mt-2">Department</div>
                <div class="fw-bold text-white">
                    {{ $asset->dept ?? 'N/A' }}
                </div>

            </div>

        </div>


        {{-- CURVE --}}
        <div class="position-absolute bottom-0 w-100">
            <svg viewBox="0 0 1440 120" style="display:block;">
                <path fill="#f8f9fa"
                      d="M0,64 C240,140 480,0 720,60 C960,120 1200,140 1440,40 L1440,120 L0,120 Z">
                </path>
            </svg>
        </div>

    </div>


    {{-- ================= FLOATING VIEW CARD ================= --}}
    <div class="container" style="margin-top:-60px; position:relative; z-index:5;">

        <div class="bg-white rounded-4 shadow-lg p-3 p-md-4">


            {{-- ================= READ-ONLY HEADER STRIP ================= --}}
            <div class="d-flex justify-content-between align-items-center mb-3">

{{--                <div class="text-muted small">--}}
{{--                    Asset Inspection View--}}
{{--                </div>--}}

                <div class="text-muted small">
                    Last updated: {{ optional($asset->updated_at)->diffForHumans() }}
                </div>

            </div>


            {{-- ================= TABS (READ ONLY STYLE) ================= --}}
            <ul class="nav nav-pills gap-2 mb-3">

                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#overview">
                        Overview
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#payments">
                        Payments
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#documents">
                        Documents
                    </a>
                </li>

            </ul>

            <hr class="mt-0">


            {{-- ================= CONTENT ================= --}}
            <div class="tab-content">

                {{-- OVERVIEW --}}
                <div class="tab-pane fade show active" id="overview">
                    <livewire:fms-assets-register.tabs.overview-tab :assetId="$asset->id" />
                </div>

                {{-- PAYMENTS --}}
                <div class="tab-pane fade" id="payments">
                    <livewire:fms-assets-register.tabs.payments-tab :assetId="$asset->id" />
                </div>

                {{-- DOCUMENTS --}}
                <div class="tab-pane fade" id="documents">
                    <livewire:fms-assets-register.tabs.documents-tab :assetId="$asset->id" />
                </div>

            </div>

        </div>
    </div>

</div>
