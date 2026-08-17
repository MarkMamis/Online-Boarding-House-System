<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boarding House Student Report - {{ $boardingHouseLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #111827;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #fff;
            font-size: 11pt;
            line-height: 1.4;
        }

        .report-header {
            border-bottom: 2px solid #166534;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .system-title {
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #166534;
            margin: 0 0 2px 0;
        }

        .document-title {
            font-size: 18pt;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: #111827;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px 20px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 9.5pt;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 2px;
        }

        .meta-value {
            font-weight: 600;
            color: #111827;
        }

        .meta-value-highlight {
            color: #166534;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 8px;
        }

        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.04em;
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            text-align: left;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: middle;
        }

        tr:nth-child(even) td {
            background-color: #fcfcfc;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: capitalize;
            border: 1px solid #d1d5db;
        }

        .report-footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: #6b7280;
        }

        .no-print-bar {
            background: #111827;
            color: #fff;
            padding: 10px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn-print {
            background: #166534;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #14532d;
        }

        .btn-close {
            background: #374151;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 9pt;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    {{-- Non-printing Action Bar --}}
    <div class="no-print-bar">
        <div>
            <strong>Print Preview Mode</strong> — Document is formatted for standard landscape printing.
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">Print Document</button>
            <button onclick="window.close()" class="btn-close">Close Window</button>
        </div>
    </div>

    {{-- Official Report Header --}}
    <div class="report-header">
        <div class="system-title">Online Boarding House System</div>
        <div class="document-title">Boarding House Student Report</div>
    </div>

    {{-- Metadata Summary Grid --}}
    <div class="meta-grid">
        <div class="meta-item">
            <span class="meta-label">Boarding House</span>
            <span class="meta-value meta-value-highlight">{{ $boardingHouseLabel }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Report Basis</span>
            <span class="meta-value">{{ $reportBasisLabel }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Reporting Period</span>
            <span class="meta-value">{{ $periodLabel ?: 'All Time / Current Monitoring' }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Unique Students</span>
            <span class="meta-value meta-value-highlight">{{ number_format($uniqueStudents) }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Total Boarding Records</span>
            <span class="meta-value">{{ number_format($totalRecords) }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Generated On</span>
            <span class="meta-value">{{ $generatedDate }}</span>
        </div>
    </div>

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 32px;" class="text-center">No.</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>College</th>
                <th>Program</th>
                <th>Boarding House</th>
                <th style="width: 70px;">Room</th>
                <th style="width: 90px;">Check-in</th>
                <th style="width: 90px;">Check-out</th>
                <th style="width: 80px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boardedStudents as $index => $boarding)
                @php
                    $monitoringStatus = $boarding->monitoringStatus(now(), $periodStart ?? null, $periodEnd ?? null, $dateBasis ?? 'stay');
                    $monitoringStatusLabel = match($monitoringStatus) {
                        'checked_out' => 'Checked Out',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($monitoringStatus),
                    };
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $boarding->student?->full_name ?? $boarding->student?->name ?? 'Unknown Student' }}</strong>
                    </td>
                    <td class="font-mono">{{ $boarding->student?->student_id ?: 'N/A' }}</td>
                    <td>{{ $boarding->student?->college ?: 'Not specified' }}</td>
                    <td>
                        {{ $boarding->student?->program ?: 'Not specified' }}
                        @if(!empty($boarding->student?->major))
                            <div style="font-size: 7.5pt; color: #6b7280;">Major: {{ $boarding->student->major }}</div>
                        @endif
                    </td>
                    <td>{{ $boarding->room?->property?->name ?? 'N/A' }}</td>
                    <td>Room {{ $boarding->room?->room_number ?? $boarding->room_id }}</td>
                    <td>{{ $boarding->check_in ? $boarding->check_in->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $boarding->check_out ? $boarding->check_out->format('M d, Y') : 'Open-ended' }}</td>
                    <td class="text-center">
                        <span class="badge-status">{{ $monitoringStatusLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 24px; color: #6b7280;">
                        No student records match the selected monitoring filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Report Footer --}}
    <div class="report-footer">
        <div>Online Boarding House System · Student Monitoring &amp; Institutional Reporting</div>
        <div>Page 1 of 1 · Total Records: {{ number_format($totalRecords) }}</div>
    </div>
</body>
</html>
