# DD Scoping Demo UAT Results (Template)

Date: ____________
Tester(s): ____________
Environment: ____________
Build/Branch: ____________

## 1) Scenario Results

| # | Scenario | Result (Pass/Fail) | Evidence (Screenshot/URL/Ref) | Notes |
|---|---|---|---|---|
| 1 | PM starts scoping from DD page |  |  |  |
| 2 | PM submits selected departments |  |  |  |
| 3 | HOD assigns officers |  |  |  |
| 4 | Officer submits outputs |  |  |  |
| 5 | HOD approves outputs |  |  |  |
| 6 | PM consolidates and submits DD |  |  |  |
| 7A | DD gate blocks approval when not all services approved |  |  |  |
| 7B | DD approval progresses when all services approved |  |  |  |
| 8 | DD approvals page shows scoping outputs |  |  |  |

## 2) Defects Found

| ID | Severity (Blocker/High/Medium/Low) | Area | Steps to Reproduce | Expected | Actual | Assigned |
|---|---|---|---|---|---|---|
| UAT-001 |  |  |  |  |  |  |
| UAT-002 |  |  |  |  |  |  |
| UAT-003 |  |  |  |  |  |  |

## 3) Final Decision

- [ ] Approved for demo
- [ ] Approved with minor issues
- [ ] Not approved (fixes required)

Decision Notes:

---

## 4) Regression Confirmation

Command:
- `php artisan test --filter="ScopingWorkflowTest|DueDiligenceScopingGateTest"`

Result:
- [ ] Passed
- [ ] Failed

Output snippet/reference:

---

## 5) Sign-off

Tester Lead: ____________
Date: ____________
