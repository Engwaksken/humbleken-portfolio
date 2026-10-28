# Roadmap and estimates

Estimates are directional person-weeks for one experienced engineer, exclude owner content/procurement and are not calendar commitments. Validate after the first executable sprint.

| Phase | Outcome | Estimate |
|---|---|---:|
| 0 Discovery (current) | Product journeys to confirm/validate, ERD, architecture/security decisions and prioritized backlog | 2–3 |
| 1 Foundation | Laravel environments, CI/tests, auth/admin baseline, settings/media conventions, API contract | 3–5 |
| 2 Portfolio/CMS | Public profile, projects, services, admin publishing, enquiry flow | 4–6 |
| 3 Knowledge | Articles, taxonomy/search, SEO and accessibility review | 3–5 |
| 4 Consultancy workflows | Customer accounts, booking requests, private tickets, notifications and isolation tests | 4–7 |
| 5 Hardening/release | UAT, security/accessibility/performance review, operations and restore drill | 3–5 |
| Later: Flutter | Public client then approved authenticated workflows | 4–7 |
| Later: AI | Separately approved safe public knowledge assistant | 4–8 |
| Later: billing | Quotes/payments only after business/provider review | 4–8+ |

Indicative web MVP without Flutter: 19-31 person-weeks, the sum of the listed web MVP phase ranges (Phases 0-5), not a separate overlap-adjusted estimate. Overlapping discovery/foundation assumptions may affect actual effort. The initiation report's original 20-33 estimate included a basic Flutter foundation as a broader master-brief aspiration, not the approved MVP. AI/payments excluded. Flutter and AI are both later/post-MVP; no ordering between them is approved.

## First executable sprint
Goal: close product/operational unknowns and make implementation-ready work; no feature coding before approval. Confirm or defer identity/brand, audience/region, hosting/database, jurisdiction/timezone, booking rules, mobile release expectations, budget and verified content. Validate core journeys (browse, enquire, request booking, own ticket, admin publish); review conceptual ERD/cardinality and retention; record choices in DECISIONS; create threat/accessibility checklist and prioritized backlog with acceptance criteria. Then, if approved, first implementation sprint establishes reproducible local setup, CI baseline, Laravel skeleton and deployable health check.

Sprint exit: decisions/deferred items recorded; ERD and backlog reviewable; no fabricated content or credentials; no product migrations until domain outline is accepted.

## Risks
Scope creep; owner content not ready; unknown jurisdiction/retention; booking timezone and cancellation ambiguity; private record/attachment leakage; provider availability/cost for AI/payments; hosting/backup/mail choice late; accessibility deferred. Mitigate via release gates, no AI/payment MVP, ownership tests, early owner decisions and deployment/restore validation.
