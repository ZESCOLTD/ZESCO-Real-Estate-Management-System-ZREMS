# Work Report (May 14, 2026)

This document captures the work completed on May 14, 2026 in this workspace. It is intended as an audit-style record you can share if asked “what changed and why”.

> Scope note: This report focuses on the **Due Diligence Planner** change request (merge sections + support multiple entries), plus small related UI alignment touched during the same push.

## 1) Executive Summary

- Refactored **Due Diligence Planner** to support **multiple property/person entries** (repeatable rows) instead of a single set of fields.
- Merged the old **Planner Section C + Section D** into **one Planner section** (now titled **SECTION C**), where each entry contains both “potential property/person name” and all property details + evidence uploads.
- Added/expanded database support for the repeatable entries using the existing `acquisition_due_diligence_properties` table, including a one-time backfill from the legacy single-value columns.
- Updated the **Due Diligence Approvals review** screen to display the multiple Planner entries.
- Removed **Filter Viable Option** from the Planner UI and persistence.
- Renamed UI labels from **“Planner / Surveyor”** to **“Planner”** (without changing the underlying role key).

## 2) Key Changes (Features + Fixes)

### A) Due Diligence – Planner supports multiple entries (repeatable)

**Goal**
- Planner should be able to capture **multiple properties/people**, where each entry includes:
  - Name (formerly “Potential Properties”) and Offer Price
  - Findings report upload + comment
  - Property details: plot/parcel, coordinates, extent, location fields
  - Evidence uploads per entry (plot evidence, coordinates evidence, extent evidence)

**Implementation**
- Introduced a repeatable Livewire state array (`plannerSurveyorProperties`) that supports add/remove and per-entry uploads.
- Saving the Planner section persists each entry as a row in `acquisition_due_diligence_properties`.
- Kept backward compatibility by syncing the first entry back into the legacy single-value columns on `acquisition_due_diligences`.

**Files**
- Livewire persistence + add/remove + legacy sync:
  - [app/Livewire/Acquisitions/DueDiligence/DueDiligenceShow.php](../app/Livewire/Acquisitions/DueDiligence/DueDiligenceShow.php)
- Planner UI (repeatable entries):
  - [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
  - [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)

### B) Database – Expand due diligence properties table for Planner entry fields

**Goal**
- Store Planner per-entry fields and per-entry evidence file paths in a normalized way.

**Implementation**
- Expanded `acquisition_due_diligence_properties` with fields used by the repeatable Planner UI.
- Backfilled data: for any due diligence record that had legacy planner/surveyor data but no properties yet, inserted “Property 1” row into `acquisition_due_diligence_properties`.

**Files**
- Migration:
  - [database/migrations/2026_05_14_120000_expand_due_diligence_properties_for_planner_entries.php](../database/migrations/2026_05_14_120000_expand_due_diligence_properties_for_planner_entries.php)
- Model fillables updated:
  - [app/Models/AcquisitionDueDiligenceProperty.php](../app/Models/AcquisitionDueDiligenceProperty.php)

### C) Due Diligence Approvals – Display multiple Planner entries

**Goal**
- Approvers should see all Planner entries (not just the legacy single-value fields).

**Implementation**
- Loaded the `properties` relation for the selected due diligence.
- Rendered a card that lists each entry and shows evidence links when present.

**Files**
- Load properties relation:
  - [app/Livewire/Acquisitions/DueDiligence/Approvals.php](../app/Livewire/Acquisitions/DueDiligence/Approvals.php)
- Display multiple entries:
  - [resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php)

### D) Planner cleanup – Remove “Filter Viable Option”

**Change**
- Removed the field from:
  - Planner UI
  - Livewire validation
  - Save logic
  - Approvals display

**Files**
- [app/Livewire/Acquisitions/DueDiligence/DueDiligenceShow.php](../app/Livewire/Acquisitions/DueDiligence/DueDiligenceShow.php)
- [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
- [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)
- [resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php)

### E) UI naming – Rename “Planner / Surveyor” to “Planner”

**Change**
- Updated the visible UI labels to “Planner”.
- Kept the internal role key as `planner_surveyor` to avoid breaking existing permissions/workflow.

**Files**
- [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
- [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)
- [resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php)

## 3) Operational Notes

- `php artisan migrate` was required for the new Planner multi-entry support (migration adds columns + backfills).
- `php artisan optimize:clear` was run multiple times to ensure view updates appeared immediately.

## 4) Git Notes

- Changes were committed and pushed to `origin/ndola_breakway` on May 14.
- This work was pushed only to `ndola_breakway` (not to `main`).
- If required later, `origin/main` can be merged into `ndola_breakway` as a separate sync step.

---

Generated: 2026-05-14

---

## Addendum (July 15, 2026) — DD Scoping Demo Flow

This addendum captures the phased demo-first implementation completed after the original May 14 work.

### Completed Phases

1. DD scoping context + flow wiring
- Added DD services context constant and cross-context support:
  - [app/Models/ServiceRequest.php](../app/Models/ServiceRequest.php)
  - [app/Livewire/ServiceRequests/Show.php](../app/Livewire/ServiceRequests/Show.php)
  - [app/Livewire/ServiceForms/Preview.php](../app/Livewire/ServiceForms/Preview.php)

2. DD page scoping controls
- Replaced old team/signoff-driven left panel with:
  - `Start Scoping`
  - `Consolidate & Submit to DD Approvals`
  - scoping progress + outputs table
- Applied in both views:
  - [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
  - [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)

3. DD approvals guardrail
- DD approval action now checks scoping completion first.
- Approvals page now shows read-only scoping outputs.
  - [app/Livewire/Acquisitions/DueDiligence/Approvals.php](../app/Livewire/Acquisitions/DueDiligence/Approvals.php)
  - [resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/approvals/show.blade.php)

4. Services-side handoff notifications
- PM submission to departments now notifies selected HODs.
  - [app/Livewire/Services/Index.php](../app/Livewire/Services/Index.php)

5. Regression coverage and test results
- Existing scoping flow tests: [tests/Feature/ScopingWorkflowTest.php](../tests/Feature/ScopingWorkflowTest.php)
- New DD scoping gate tests: [tests/Feature/DueDiligenceScopingGateTest.php](../tests/Feature/DueDiligenceScopingGateTest.php)
- Latest combined run: **5 passed, 0 failed**.

6. UAT execution guide
- Added manual UAT checklist for PM -> HOD -> Officer -> PM -> DD approvers path:
  - [docs/DD_SCOPING_DEMO_UAT_CHECKLIST.md](DD_SCOPING_DEMO_UAT_CHECKLIST.md)

### Current Status

- Demo path is code-complete for the agreed scope.
- Automated regression for scoping + DD gate is green.
- Next practical step is manual UAT using configured test users.

### Immediate Next Step

- Execute the checklist in [docs/DD_SCOPING_DEMO_UAT_CHECKLIST.md](DD_SCOPING_DEMO_UAT_CHECKLIST.md).
- Log any UI/logic gaps and patch in the next phase.
- Capture outcomes using [docs/DD_SCOPING_DEMO_UAT_RESULTS_TEMPLATE.md](DD_SCOPING_DEMO_UAT_RESULTS_TEMPLATE.md).

Generated addendum: 2026-07-15
