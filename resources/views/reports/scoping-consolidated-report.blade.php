<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scoping Consolidated Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; }
        h1, h2, h3 { margin: 0; }
        .header { border-bottom: 3px solid #eab308; padding-bottom: 10px; margin-bottom: 14px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; vertical-align: middle; }
        .logo { max-height: 58px; }
        .title { text-align: right; }
        .title-main { font-size: 18px; font-weight: bold; color: #111827; }
        .title-sub { color: #6b7280; font-size: 11px; }
        .muted { color: #6b7280; }
        .section { margin-top: 14px; }
        .section-title {
            background: #fff7cc;
            border: 1px solid #f3de88;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .ok { background: #dcfce7; color: #166534; }
        .warn { background: #fef3c7; color: #92400e; }
        .service-card {
            border: 1px solid #d1d5db;
            margin-top: 10px;
            padding: 8px;
            page-break-inside: avoid;
        }
        .service-title { font-size: 12px; font-weight: bold; margin-bottom: 6px; }
        .attachment { border: 1px solid #e5e7eb; padding: 6px; margin-top: 6px; }
        .preview-img { width: 100%; max-height: 260px; object-fit: contain; border: 1px solid #d1d5db; }
        .mono { font-family: DejaVu Sans Mono, monospace; white-space: pre-wrap; font-size: 10px; }
        .footer { margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 30%;">
                    @if(!empty($logoDataUri))
                        <img src="{{ $logoDataUri }}" alt="ZESCO Logo" class="logo">
                    @else
                        <strong>ZESCO</strong>
                    @endif
                </td>
                <td class="title" style="width: 70%;">
                    <div class="title-main">Scoping Consolidated Report</div>
                    <div class="title-sub">Application: {{ $request->reference_no ?? ('REQ-' . $request->id) }}</div>
                    <div class="title-sub">Generated: {{ $generatedAt?->format('Y-m-d H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Initial Application Details</div>
        <table>
            <tr>
                <th style="width: 35%;">Applicant</th>
                <td>{{ $request->applicant_name ?? ($request->user->name ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $request->email ?? ($request->user->email ?? 'N/A') }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $request->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Project</th>
                <td>{{ $request->project_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Description</th>
                <td>{{ $request->description ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Location</th>
                <td>{{ $request->location ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Acquisition Type</th>
                <td>{{ $request->acquisition_type?->value ?? $request->acquisition_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Expected Start Date</th>
                <td>{{ optional($request->expected_start_date)->format('Y-m-d') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Expected End Date</th>
                <td>{{ optional($request->expected_end_date)->format('Y-m-d') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Current Workflow Stage</th>
                <td>{{ $request->workflow_stage ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if(!empty($applicationAttachments))
        <div class="section">
            <div class="section-title">Initial Application Attachments (Embedded Content)</div>
            @foreach($applicationAttachments as $att)
                <div class="attachment">
                    <strong>{{ $att['name'] ?? 'Attachment' }}</strong>

                    @if(!empty($att['image_preview']))
                        <div style="margin-top:6px;">
                            <img class="preview-img" src="{{ $att['image_preview'] }}" alt="Application Attachment">
                        </div>
                    @endif

                    @if(!empty($att['pdf_text']))
                        <div style="margin-top:6px;">
                            <div class="muted">PDF Content Preview</div>
                            <div class="mono">{{ $att['pdf_text'] }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="section">
        <div class="section-title">Scoping Services Summary</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 13%;">Department</th>
                    <th>Service</th>
                    <th style="width: 16%;">Assignee</th>
                    <th style="width: 14%;">Service Status</th>
                    <th style="width: 14%;">Form Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['department_code'] }}</td>
                        <td>{{ $item['service_name'] }}</td>
                        <td>{{ $item['assigned_to'] }}</td>
                        <td>
                            @if(($item['status'] ?? '') === 'approved')
                                <span class="badge ok">Approved</span>
                            @else
                                <span class="badge warn">{{ ucfirst(str_replace('_', ' ', $item['status'] ?? 'n/a')) }}</span>
                            @endif
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item['submission_status'] ?? 'n/a')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detailed Scoping Content (All Services)</div>
        @foreach($items as $index => $item)
            <div class="service-card">
                <div class="service-title">
                    {{ $index + 1 }}. {{ $item['service_name'] }} ({{ $item['department_code'] }})
                </div>
                <div class="muted" style="margin-bottom: 6px;">
                    Assignee: {{ $item['assigned_to'] }} | Service Status: {{ ucfirst(str_replace('_', ' ', $item['status'] ?? 'n/a')) }} | Form Status: {{ ucfirst(str_replace('_', ' ', $item['submission_status'] ?? 'n/a')) }}
                </div>

                @if(!empty($item['field_rows']))
                    <table>
                        <thead>
                        <tr>
                            <th style="width: 24%;">Field</th>
                            <th style="width: 36%;">Value</th>
                            <th style="width: 40%;">Evidence / Attachments</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($item['field_rows'] as $row)
                            <tr>
                                <td>{{ $row['label'] ?? 'Field' }}</td>
                                <td>
                                    {{ $row['value'] ?? 'N/A' }}
                                    @if(!empty($row['comment']))
                                        <div class="muted" style="margin-top:5px;">Comment: {{ $row['comment'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($row['file_name']))
                                        <div><strong>{{ $row['file_name'] }}</strong></div>
                                    @endif

                                    @if(!empty($row['image_preview']))
                                        <div style="margin-top:6px;">
                                            <img class="preview-img" src="{{ $row['image_preview'] }}" alt="Scoping Evidence Image">
                                        </div>
                                    @endif

                                    @if(!empty($row['pdf_text']))
                                        <div style="margin-top:6px;">
                                            <div class="muted">PDF Content Preview</div>
                                            <div class="mono">{{ $row['pdf_text'] }}</div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="muted">No scoping form field values captured for this service.</div>
                @endif
            </div>
        @endforeach
    </div>

    @if(!empty($request->remarks))
        <div class="section">
            <div class="section-title">Consolidation Notes / Remarks</div>
            <div style="border:1px solid #d1d5db; padding:8px; white-space: pre-wrap;">{{ $request->remarks }}</div>
        </div>
    @endif

    <div class="footer">
        Generated at {{ $generatedAt?->format('Y-m-d H:i') }} by {{ $generatedBy ?? 'System' }}
    </div>
</body>
</html>
