# DD Scoping Demo UAT Checklist

## Scope
Validate end-to-end demo path:
PM -> Services scoping -> HOD assignment/approval -> PM consolidation -> DD approvals.

## Test Data Prerequisites
- One approved acquisition request with PM `approved_by` set.
- One due diligence record linked to that acquisition.
- At least 2 service departments and services configured in Services Setup.
- Accounts:
  - PM (same user who approved acquisition)
  - Department HOD
  - Assigned officer
  - DD approver step 1 and step 2

## 1) PM starts scoping from DD page
1. Open DD show page.
2. Click **Start Scoping**.
3. Confirm redirect to Services Hub with DD context.

Expected:
- Services request is opened/created for `context_type=acquisition_due_diligence`.
- PM can select departments/services.

## 2) PM submits selected departments
1. In Services Hub, select departments/services.
2. Submit to departments.

Expected:
- Selected department HODs receive notification.
- DD page scoping summary shows non-zero total services.

## 3) HOD assigns officers
1. Login as HOD.
2. Open service request and assign officers.

Expected:
- Service statuses move to assigned/in progress.
- Assigned officer appears in DD Scoping Outputs table.

## 4) Officer submits outputs
1. Login as assigned officer.
2. Fill service form and submit.

Expected:
- Service status changes to submitted.
- HOD can review submission.

## 5) HOD approves outputs
1. Login as HOD.
2. Approve all submitted services.

Expected:
- Each service status becomes approved.
- DD page Scoping Progress shows all approved.

## 6) PM consolidates and submits DD
1. Login as PM.
2. Open DD page.
3. Click **Consolidate & Submit to DD Approvals**.

Expected:
- Success message appears.
- DD workflow starts (moves from draft to under review/current step set).

## 7) DD approval gate behavior
A) Negative path:
- If any service not approved, attempt DD approve.
- Expected: approval does not progress.

B) Positive path:
- With all services approved, DD approver step 1 approves.
- Expected: workflow moves to step 2.

## 8) DD approvals page visibility
1. Open DD approvals show page.

Expected:
- Scoping Outputs section visible.
- Department/service/status/officer data displayed read-only.

## Pass Criteria
- All expected outcomes above are met.
- No runtime errors in browser logs or app logs.
- DD cannot progress before scoping completion.

## Regression Quick Check
- `php artisan test --filter="ScopingWorkflowTest|DueDiligenceScopingGateTest"`
- Expect all tests to pass.
