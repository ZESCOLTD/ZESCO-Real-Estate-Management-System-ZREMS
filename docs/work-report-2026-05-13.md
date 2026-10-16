# Work Report (May 13, 2026)

This document captures the work completed during the recent development + debugging session in this workspace. It is intended as an audit-style record you can share if asked “what changed and why”.

> Note: Some changes may already have been committed/merged earlier in the session. The **Key Changes** section below describes *everything we worked on* (features + fixes). The **Currently Uncommitted Changes** section is based on your current working tree.

## 1) Executive Summary

- Built/extended **Geomatics request uploads** (multi-file per job type), improved UX feedback, stored attachment paths in JSON, and added secure open/download.
- Updated **Acquisition Requests** business rules:
  - Budget Approved is simplified to **Yes/No**.
  - Budget Line is shown/required only when Budget Approved = **Yes**.
  - When Budget Approved = **No**, a single **virement** upload is required from approvers (HOD / Chief Accountant) before approval.
  - **Section F (Supporting Attachments)** is now mandatory on submit.
- Stabilized workflow + due diligence by aligning code expectations with database schema:
  - Added missing columns for Acquisition Requests and Due Diligence.
  - Added polymorphic workflow columns (`workflowable_id/type`) with backfill; kept legacy keys compatible.
- Fixed due diligence save/submit **403 Forbidden** caused by an incorrect section save action.

## 2) Key Changes (Features + Fixes)

### A) Acquisition Requests – Budget Approved + Budget Line behavior

**Goal**
- When “Is this a Budget Approved Request?” = **Yes** → show Budget Line and require it.
- When “No” → hide Budget Line, clear it (store NULL), and proceed with the virement requirement during approvals.

**Implementation**
- Create form now uses Livewire v4 reactive binding (`wire:model.live`) so UI updates immediately.
- Budget Line on create uses an AJAX TomSelect control that loads real budget codes from `/budget-lines/search`.

**Files**
- Create UI and Budget Line AJAX dropdown: [resources/views/livewire/acquisitions/requests/create.blade.php](../resources/views/livewire/acquisitions/requests/create.blade.php)
- Create component logic (conditional validation + clearing budget_line when No): [app/Livewire/Acquisitions/Requests/Create.php](../app/Livewire/Acquisitions/Requests/Create.php)
- Consistency updates to v4 binding:
  - [resources/views/livewire/acquisitions/requests/edit.blade.php](../resources/views/livewire/acquisitions/requests/edit.blade.php)
  - [resources/views/livewire/acquisitions/requests/form.blade.php](../resources/views/livewire/acquisitions/requests/form.blade.php)
  - [resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php](../resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php)

**Links / Dependencies**
- Budget codes endpoint: [routes/web.php](../routes/web.php) (route: `/budget-lines/search`)
- Budget codes data source model: [app/Models/VwBudgetCodesCombined.php](../app/Models/VwBudgetCodesCombined.php)

### B) Acquisition Requests – Virement upload requirement (Budget Approved = No)

**Goal**
- If Budget Approved = **No**, then at **HOD** or **Chief Accountant** approval steps:
  - Exactly one virement file can be uploaded.
  - Approval must be blocked until virement is uploaded.
  - Virement must be viewable/openable/downloadable.

**Implementation**
- Approvals screens include virement upload UI, and Livewire components enforce:
  - “one upload total”
  - only allowed at specific workflow steps
  - approval is blocked if missing.

**Files**
- Approvals logic + enforcement + upload: [app/Livewire/Acquisitions/Requests/Approvals.php](../app/Livewire/Acquisitions/Requests/Approvals.php)
- Request show logic (where applicable): [app/Livewire/Acquisitions/Requests/Show.php](../app/Livewire/Acquisitions/Requests/Show.php)
- Approvals show UI (includes virement section + error flash): [resources/views/livewire/acquisitions/requests/approvals/show.blade.php](../resources/views/livewire/acquisitions/requests/approvals/show.blade.php)
- Management approvals parity (because management approvals render similar request views): [app/Livewire/Management/Approvals.php](../app/Livewire/Management/Approvals.php)

### C) Acquisition Requests – Section F mandatory (Supporting Attachments)

**Goal**
- Section F must be mandatory when submitting for approval:
  - At least one attachment file must exist.
  - Draft save can remain permissive.

**Implementation**
- Enforced in Livewire submit actions by checking at least one row has a file (or existing file).
- Updated UI heading to show a required indicator and help text.

**Files**
- Enforced on submit:
  - [app/Livewire/Acquisitions/Requests/Create.php](../app/Livewire/Acquisitions/Requests/Create.php)
  - [app/Livewire/Acquisitions/Requests/Edit.php](../app/Livewire/Acquisitions/Requests/Edit.php)
  - [app/Livewire/Acquisitions/Requests/Form.php](../app/Livewire/Acquisitions/Requests/Form.php)
- UI updated:
  - [resources/views/livewire/acquisitions/requests/create.blade.php](../resources/views/livewire/acquisitions/requests/create.blade.php)
  - [resources/views/livewire/acquisitions/requests/edit.blade.php](../resources/views/livewire/acquisitions/requests/edit.blade.php)
  - [resources/views/livewire/acquisitions/requests/form.blade.php](../resources/views/livewire/acquisitions/requests/form.blade.php)
  - [resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php](../resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php)

### D) Workflow system – Polymorphic workflow support + legacy compatibility

**Problem**
- Code expected polymorphic columns (`workflowable_id/type`) but DB had legacy `acquisition_request_id` only, and some constraints were non-null.

**Fix**
- Added `workflowable_id` and `workflowable_type` to both `workflow_instances` and `workflow_actions`.
- Backfilled values from legacy `acquisition_request_id`.
- Made legacy `acquisition_request_id` nullable to allow workflows for other models.
- Updated models to allow mass assignment of legacy and polymorphic fields.

**Files**
- Migrations:
  - [database/migrations/2026_05_12_210000_add_workflowable_morphs_to_workflow_tables.php](../database/migrations/2026_05_12_210000_add_workflowable_morphs_to_workflow_tables.php)
  - [database/migrations/2026_05_12_211000_make_workflow_legacy_acquisition_request_id_nullable.php](../database/migrations/2026_05_12_211000_make_workflow_legacy_acquisition_request_id_nullable.php)
- Models:
  - [app/Models/WorkflowInstance.php](../app/Models/WorkflowInstance.php)
  - [app/Models/WorkflowAction.php](../app/Models/WorkflowAction.php)

**Debug scripts used**
- Workflow column inspection: [scripts/db-debug-workflow-columns.php](../scripts/db-debug-workflow-columns.php)

### E) Due Diligence – Schema alignment + 403 fix

**Schema alignment**
- Due diligence form was writing fields not present in DB, producing “Unknown column …” SQL errors.
- We added the missing columns (nullable) so the Livewire form can save without runtime errors.

**Files**
- Due diligence missing columns:
  - [database/migrations/2026_05_12_212000_add_missing_due_diligence_columns.php](../database/migrations/2026_05_12_212000_add_missing_due_diligence_columns.php)
  - [database/migrations/2026_05_13_000000_expand_acquisition_due_diligences_for_form.php](../database/migrations/2026_05_13_000000_expand_acquisition_due_diligences_for_form.php)

**403 Forbidden fix (Valuation Officer)**
- The Valuation “Update Section” button incorrectly called `saveSection('wayleave')`, triggering authorization failure.

**Files**
- Fixed section save wiring:
  - [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
  - [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)

### F) Acquisition Requests – Business unit support

**Problem**
- Code referenced `business_unit` but DB was missing it.

**Fix**
- Added the column and ensured Livewire create/edit/form handle it.

**Files**
- Migration: [database/migrations/2026_05_12_200000_add_business_unit_to_acquisition_requests_table.php](../database/migrations/2026_05_12_200000_add_business_unit_to_acquisition_requests_table.php)
- Components updated:
  - [app/Livewire/Acquisitions/Requests/Create.php](../app/Livewire/Acquisitions/Requests/Create.php)
  - [app/Livewire/Acquisitions/Requests/Edit.php](../app/Livewire/Acquisitions/Requests/Edit.php)
  - [app/Livewire/Acquisitions/Requests/Form.php](../app/Livewire/Acquisitions/Requests/Form.php)

### G) Geomatics module (feature work done earlier in session)

**Goal**
- Multi-file uploads for Geomatics requests per job type (Wayleave/Cadastral/Engineering), clear upload UX (“Uploading…”, “Ready: N files”), and secure viewing/downloading of stored attachments.

**High-level linkage**
- Livewire create component stores attachments under private storage and persists paths in JSON (services field).
- Controller serves attachments via authenticated routes (open/download).
- Index “View” navigates to a new Show page which lists attachments.

**Representative files (from session work)**
- Livewire create: [app/Livewire/Geomatics/Create.php](../app/Livewire/Geomatics/Create.php)
- Create UI: [resources/views/livewire/geomatics/create.blade.php](../resources/views/livewire/geomatics/create.blade.php)
- Livewire show: [app/Livewire/Geomatics/Show.php](../app/Livewire/Geomatics/Show.php)
- Show UI: [resources/views/livewire/geomatics/show.blade.php](../resources/views/livewire/geomatics/show.blade.php)
- Secure attachment controller: [app/Http/Controllers/Geomatics/GeomaticsRequestAttachmentController.php](../app/Http/Controllers/Geomatics/GeomaticsRequestAttachmentController.php)
- Routes: [routes/web.php](../routes/web.php)

## 3) Currently Uncommitted Changes (from working tree)

This section is based on the current diff in your repo at the time this report was generated.

### Modified files (high value)

- Acquisition Requests:
  - [app/Livewire/Acquisitions/Requests/Create.php](../app/Livewire/Acquisitions/Requests/Create.php)
  - [app/Livewire/Acquisitions/Requests/Edit.php](../app/Livewire/Acquisitions/Requests/Edit.php)
  - [app/Livewire/Acquisitions/Requests/Form.php](../app/Livewire/Acquisitions/Requests/Form.php)
  - [app/Livewire/Acquisitions/Requests/Show.php](../app/Livewire/Acquisitions/Requests/Show.php)
  - [app/Livewire/Acquisitions/Requests/Approvals.php](../app/Livewire/Acquisitions/Requests/Approvals.php)
  - [resources/views/livewire/acquisitions/requests/create.blade.php](../resources/views/livewire/acquisitions/requests/create.blade.php)
  - [resources/views/livewire/acquisitions/requests/edit.blade.php](../resources/views/livewire/acquisitions/requests/edit.blade.php)
  - [resources/views/livewire/acquisitions/requests/form.blade.php](../resources/views/livewire/acquisitions/requests/form.blade.php)
  - [resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php](../resources/views/livewire/acquisitions/requests/partials/form-fields.blade.php)
  - [resources/views/livewire/acquisitions/requests/approvals/show.blade.php](../resources/views/livewire/acquisitions/requests/approvals/show.blade.php)

- Due Diligence:
  - [resources/views/livewire/acquisitions/due-diligence/create.blade.php](../resources/views/livewire/acquisitions/due-diligence/create.blade.php)
  - [resources/views/livewire/acquisitions/due-diligence/show.blade.php](../resources/views/livewire/acquisitions/due-diligence/show.blade.php)

- Workflow models:
  - [app/Models/WorkflowInstance.php](../app/Models/WorkflowInstance.php)
  - [app/Models/WorkflowAction.php](../app/Models/WorkflowAction.php)

- Management approvals parity:
  - [app/Livewire/Management/Approvals.php](../app/Livewire/Management/Approvals.php)

### New files (migrations / scripts)

- Migrations:
  - [database/migrations/2026_05_12_200000_add_business_unit_to_acquisition_requests_table.php](../database/migrations/2026_05_12_200000_add_business_unit_to_acquisition_requests_table.php)
  - [database/migrations/2026_05_12_210000_add_workflowable_morphs_to_workflow_tables.php](../database/migrations/2026_05_12_210000_add_workflowable_morphs_to_workflow_tables.php)
  - [database/migrations/2026_05_12_211000_make_workflow_legacy_acquisition_request_id_nullable.php](../database/migrations/2026_05_12_211000_make_workflow_legacy_acquisition_request_id_nullable.php)
  - [database/migrations/2026_05_12_212000_add_missing_due_diligence_columns.php](../database/migrations/2026_05_12_212000_add_missing_due_diligence_columns.php)
  - [database/migrations/2026_05_13_000000_expand_acquisition_due_diligences_for_form.php](../database/migrations/2026_05_13_000000_expand_acquisition_due_diligences_for_form.php)

- Debug scripts:
  - [scripts/debug-budget-line-rules.php](../scripts/debug-budget-line-rules.php)
  - [scripts/db-debug-workflow-columns.php](../scripts/db-debug-workflow-columns.php)

## 4) Commands Run / Operational Notes

Typical operational commands used during the session:

- `php artisan migrate` (apply pending migrations)
- `php artisan db:seed` (run seeders, e.g., workflows)
- `php artisan optimize:clear` (cleared view/config/route cache repeatedly after changes)

Git operations performed during the session:
- Used `git stash`, `git pull`, branch merges between `main` and `ndola_breakway`, and later `git push` after verification.
- Ensured `.env` is not committed.

## 5) How Everything Links (At a Glance)

- Acquisition request lifecycle:
  - Create request (Livewire) → saves `acquisition_requests` + attachment records → starts workflow instance → approvers act via approvals screens.
- Budget Approved logic:
  - Create form controls visibility/validation of `budget_line`.
  - If not approved, workflow step gating forces a virement upload by allowed approvers before approving.
- Due diligence lifecycle:
  - Team signoff is collected first; once complete, a workflow instance begins.
  - Approvals page uses workflow step approvers; form sections are gated by assigned team role.
- Workflow system:
  - DB now supports both legacy `acquisition_request_id` and polymorphic `workflowable_id/type` so different models can share the engine.

---

Generated: 2026-05-13
