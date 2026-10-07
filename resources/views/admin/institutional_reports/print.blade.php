<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $presetMeta['title'] ?? 'Institutional Report' }} - MinSU OBHS</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #111827;
        }

        body {
            margin: 0;
            padding: 16px;
            background: #ffffff;
            font-size: 10pt;
            line-height: 1.4;
        }

        /* ── Header ── */
        .report-header {
            border-bottom: 2px solid #166534;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .system-title {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #166534;
            margin: 0 0 2px 0;
        }

        .document-title {
            font-size: 16pt;
            font-weight: 700;
            margin: 0 0 4px 0;
            color: #111827;
        }

        .document-subtitle {
            font-size: 9pt;
            color: #4b5563;
            margin: 0;
        }

        /* ── Metadata Grid ── */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 9pt;
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

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 6px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.04em;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 5px 8px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* ── Summary & Signatures ── */
        .summary-strip {
            display: flex;
            gap: 12px;
            margin-top: 14px;
            padding: 8px 12px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 8.5pt;
        }

        .signatory-section {
            margin-top: 28px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            page-break-inside: avoid;
        }

        .signatory-box {
            font-size: 9pt;
        }

        .signatory-line {
            border-bottom: 1px solid #111827;
            height: 32px;
            margin-bottom: 4px;
        }

        .signatory-title {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
        }

        .print-actions {
            margin-bottom: 14px;
        }

        @media print {
            .print-actions {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" style="padding: 6px 14px; font-weight: 600; cursor: pointer; background: #166534; color: #fff; border: none; border-radius: 4px;">
            🖨️ Print Report / Save as PDF
        </button>
        <button onclick="window.close()" style="padding: 6px 14px; font-weight: 600; cursor: pointer; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; margin-left: 8px;">
            Close Window
        </button>
    </div>

    {{-- Report Header --}}
    <div class="report-header">
        <div class="system-title">Mindoro State University · Online Boarding House System (MinSU OBHS)</div>
        <h1 class="document-title">{{ $presetMeta['title'] ?? 'Institutional Report' }}</h1>
        <p class="document-subtitle">{{ $presetMeta['description'] ?? 'Official institutional accommodation and monitoring record.' }}</p>
    </div>

    {{-- Metadata Grid --}}
    <div class="meta-grid">
        <div class="meta-item">
            <span class="meta-label">Reporting Period</span>
            <span class="meta-value">{{ $periodLabel }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Boarding House</span>
            <span class="meta-value">{{ $propertyLabel }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">College / Academic Unit</span>
            <span class="meta-value">{{ $collegeLabel }}</span>
        </div>
        <div class="meta-item">
            <span class="meta-label">Generated Date &amp; Time</span>
            <span class="meta-value">{{ now()->format('F d, Y · h:i A') }}</span>
        </div>
    </div>

    {{-- Summary Metric Badges --}}
    @if(!empty($reportData['summary']))
        <div class="summary-strip">
            <strong>Key Metrics:</strong>
            @foreach($reportData['summary'] as $sumLabel => $sumVal)
                <span>{{ $sumLabel }}: <strong>{{ $sumVal }}</strong></span>
                @if(!$loop->last) · @endif
            @endforeach
        </div>
    @endif

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">#</th>
                @foreach($reportData['columns'] as $col)
                    <th>{{ $col }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($reportData['rows'] as $index => $row)
                <tr>
                    <td style="text-align: center; color: #6b7280;">{{ $index + 1 }}</td>
                    @foreach((is_array($row) ? $row : (array) $row) as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($reportData['columns']) + 1 }}" style="text-align: center; padding: 24px; color: #6b7280;">
                        No records match the selected institutional reporting parameters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signatory Section --}}
    <div class="signatory-section">
        <div class="signatory-box">
            <span class="signatory-title">Generated &amp; Prepared By:</span>
            <div class="signatory-line"></div>
            <strong>{{ auth()->user()->full_name ?: auth()->user()->name }}</strong><br>
            <span style="font-size: 8pt; color: #4b5563;">OBHS System Administrator</span>
        </div>
        <div class="signatory-box">
            <span class="signatory-title">Certified Correct / Received By:</span>
            <div class="signatory-line"></div>
            <span style="font-size: 8pt; color: #4b5563;">Office of Student Affairs / Unit Coordinator</span>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Slight delay to ensure layout and fonts render cleanly
            setTimeout(function() {
                window.print();
            }, 350);
        };
    </script>
</body>
</html>
