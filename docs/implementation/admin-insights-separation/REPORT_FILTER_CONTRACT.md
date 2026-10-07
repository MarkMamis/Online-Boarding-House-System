# Institutional Report Filter Contract

This contract defines the supported filters, normalized parameter parsing, and consistency guarantees across Report Previews, CSV Exports, and Dedicated Print Views.

---

## 1. Supported Parameters

| Parameter | Type | Default | Description | Supported Presets |
| :--- | :--- | :--- | :--- | :--- |
| `report_preset` | string | `active_boarders` | Report preset identifier (`active_boarders`, `property_distribution`, `academic_distribution`, `occupancy_availability`, `compliance_summary`, `incident_summary`) | All |
| `period_preset` | string | `all` | Period preset (`all`, `this_month`, `last_month`, `this_year`, `custom`, `specific_month`) | All |
| `date_from` | string (Y-m-d) | null | Start date for custom period preset | All |
| `date_to` | string (Y-m-d) | null | End date for custom period preset | All |
| `month` | integer (1-12) | null | Month index for specific_month preset | All |
| `year` | integer | null | Year for specific_month / this_year presets | All |
| `date_basis` | string | `stay` | Date evaluation mode (`stay` = physical overlap, `check_in` = started boarding) | `active_boarders`, `property_distribution`, `academic_distribution` |
| `property_id` | integer | null | Scopes report to a specific boarding house | `active_boarders`, `property_distribution`, `occupancy_availability`, `compliance_summary` |
| `college` | string | null | Scopes report to an academic college (e.g., `CCS`, `CBA`, `COED`) | `active_boarders`, `academic_distribution` |
| `program` | string | null | Scopes report to a degree program | `active_boarders`, `academic_distribution` |

---

## 2. Shared Data Resolution Pipeline

All three output mechanisms (`index` preview, `exportCsv`, and `printReport`) execute through the identical pipeline:

```text
HTTP Request
  ├── resolveRequestFilters()
  │     ├── resolveReportingPeriod() via BoardingMonitoringService
  │     └── normalize entity filters (property_id, college, program)
  │
  ├── AdminReportingService::getReportData($selectedPreset, $filters)
  │
  ├── Preview View: resources/views/admin/institutional_reports/index.blade.php
  ├── CSV Stream: fputcsv with UTF-8 BOM and formula injection protection
  └── Dedicated Print: resources/views/admin/institutional_reports/print.blade.php
```

This guarantees 100% data fidelity between previewed rows, downloaded CSVs, and printed documents.
