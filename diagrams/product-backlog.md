# QA Platform — Product Backlog & Sprint Plan

## Product Backlog

| ID | Epic | User Story | Priority | Points | Acceptance Criteria |
|----|------|------------|----------|--------|---------------------|
| US-01 | Access & Identity | As a user, I want to log in/out  so that I can securely access the platform. | Must Have | 5 | Given valid credentials, When I submit login, Then I am redirected to dashboard with session initialized. |
| US-02 | Access & Identity | As an admin/manager, I want role-based access control so that users only see permitted data and actions. | Must Have | 8 | Given a manager user, When opening protected modules, Then only assigned business-unit data is returned. |
| US-03 | Organization Setup | As an admin, I want to manage business units, activities, and as a manager i want to manage agents so that evaluation scope is structured. | Must Have | 8 | Given admin access, When I create/update/delete BU/activity/agent, Then records are persisted with valid relationships. |
| US-04 | Template Management | As an admin , I want to build weighted templates (criteria/subcriteria) so that evaluations are standardized. | Must Have | 13 | Given a template builder form, When I define criteria and subcriteria weights, Then structure and ordering are saved correctly. |
| US-05 | Evaluation Execution | As a manager, I want to complete manual evaluations so that agent performance is scored reliably. | Must Have | 13 | Given an active template, When I submit notations for each subcriteria, Then total score and detailed results are stored. |
| US-06 | Evaluation Execution | As an admin/manager, I want to list and filter evaluations so that I can review performance by date, BU, activity, and agent. | Should Have | 5 | Given filter selections, When I apply filters, Then the evaluation list returns only matching and authorized records. |
| US-07 | AI Evaluation | As a manager, I want transcription and AI-assisted scoring so that QA throughput increases. | Should Have | 13 | Given an uploaded audio file and template, When AI evaluation runs, Then transcript, diarization, and scored output are returned. |
| US-08 | Reporting & Export | As an admin/manager , I want reports and exports so that QA outcomes are shareable and auditable. | Should Have | 8 | Given report filters, When I generate and export, Then aggregated metrics and downloadable files are produced. |

---

## Sprint Plan

| Sprint | Goal | User Stories | Total Points |
|--------|------|--------------|:------------:|
| Sprint 1 | Establish secure access control (authentication + authorization) and organizational master data. | US-01, US-02, US-03 | 21 |
| Sprint 2 | Deliver template-driven manual evaluation with searchable history. | US-04, US-05, US-06 | 31 |
| Sprint 3 | Deliver AI-assisted evaluation and business reporting/export. | US-07, US-08 | 21 |
