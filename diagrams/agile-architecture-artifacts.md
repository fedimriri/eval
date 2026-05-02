# Architecture Summary

- Architecture style: custom PHP MVC with front controller (`index.php`), custom router (`app/Core/App.php`), base controller/model abstractions, and PDO-backed data access (`app/Core/Database.php`).
- Main bounded contexts: Access & Governance, Organization Structure, Evaluation Template Management, Evaluation Execution (manual + AI), Reporting & Export.
- Core domain hierarchy: Business Unit -> Activity -> Agent -> Evaluation Template -> Criteria -> Subcriteria -> Evaluation Results.
- Evaluation lifecycle: select agent/activity/template -> score subcriteria (C/NC/SI/PC/NE) -> compute total score -> persist evaluation + subcriteria results -> report/export.
- AI workflow: audio upload -> Groq Whisper transcription -> Groq chat diarization/evaluation -> structured results persisted through evaluation flow.
- Security and governance: session auth, role-based access (admin/manager), manager data scope via `manager_business_units` mapping.
- Data model: relational schema with strong FK constraints and cascade/set-null semantics for dependent records.
- External integrations: Groq API via Guzzle, export via PhpSpreadsheet + mPDF.

## Assumptions

- The intended primary actors are Admin and Manager; Agent is evaluated but not an authenticated actor in this version.
- AI evaluation is an accelerator over the same template/criteria model used by manual evaluations.
- Sprint scope uses the existing architecture (no framework migration).

## Product Backlog

| ID | Epic | User Story | Priority (MoSCoW) | Story Points | Acceptance Criteria |
|---|---|---|---|---:|---|
| US-01 | Access & Identity | As a user, I want to log in/out and manage my profile so that I can securely access the platform. | Must Have | 5 | Given valid credentials, When I submit login, Then I am redirected to dashboard with session initialized. |
| US-02 | Access & Identity | As an admin/manager, I want role-based access control so that users only see permitted data and actions. | Must Have | 8 | Given a manager user, When opening protected modules, Then only assigned business-unit data is returned. |
| US-03 | Organization Setup | As an admin, I want to manage business units, activities, and agents so that evaluation scope is structured. | Must Have | 8 | Given admin access, When I create/update/delete BU/activity/agent, Then records are persisted with valid relationships. |
| US-04 | Access Governance | As an admin, I want to assign managers to business units so that operational ownership is enforced. | Must Have | 3 | Given an admin assignment action, When a manager is linked to a BU, Then scoped access is immediately effective. |
| US-05 | Template Management | As a quality lead, I want to build weighted templates (criteria/subcriteria) so that evaluations are standardized. | Must Have | 13 | Given a template builder form, When I define criteria and subcriteria weights, Then structure and ordering are saved correctly. |
| US-06 | Evaluation Execution | As a manager, I want to complete manual evaluations so that agent performance is scored reliably. | Must Have | 13 | Given an active template, When I submit notations for each subcriteria, Then total score and detailed results are stored. |
| US-07 | Evaluation Execution | As a manager/admin, I want to list and filter evaluations so that I can review performance by date, BU, activity, and agent. | Should Have | 5 | Given filter selections, When I apply filters, Then the evaluation list returns only matching and authorized records. |
| US-08 | AI Evaluation | As a manager, I want transcription and AI-assisted scoring so that QA throughput increases. | Should Have | 13 | Given an uploaded audio file and template, When AI evaluation runs, Then transcript, diarization, and scored output are returned. |
| US-09 | Reporting & Export | As a stakeholder, I want reports and exports so that QA outcomes are shareable and auditable. | Should Have | 8 | Given report filters, When I generate and export, Then aggregated metrics and downloadable files are produced. |

## Sprint Plan

| Sprint | Goal | User Stories | Total Points |
|---|---|---|---:|
| Sprint 1 | Establish secure access control and organizational master data. | US-01, US-02, US-03, US-04 | 24 |
| Sprint 2 | Deliver template-driven manual evaluation with searchable history. | US-05, US-06, US-07 | 31 |
| Sprint 3 | Deliver AI-assisted evaluation and business reporting/export. | US-08, US-09 | 21 |

## Diagram Files

- `use-case-global.drawio`
- `class-diagram-global.drawio`
- `sprint-1-use-case.drawio`
- `sprint-1-class.drawio`
- `sprint-1-sequence.drawio`
- `sprint-2-use-case.drawio`
- `sprint-2-class.drawio`
- `sprint-2-sequence.drawio`
- `sprint-3-use-case.drawio`
- `sprint-3-class.drawio`
- `sprint-3-sequence.drawio`
