# IT Problem Solver — Project Initiation Report

**Status:** Phase 0 planning underway; direction approved, final decisions remain open
**Date:** 28 October 2026  
**Decision status:** Direction approved; confirm/amend remaining MVP details and architecture decisions before implementation.

## 1. Executive summary

IT Problem Solver is envisioned as a professional digital home and operating platform for an independent Computer Engineer and IT Consultant. It should combine a search-friendly public portfolio and service catalogue with practical customer workflows, a secure content-management experience, and a Flutter companion app. The guiding promise is **“Turning Complex IT Challenges into Simple, Reliable Solutions.”**

The platform should launch in useful increments. The first release should establish a trustworthy public presence and reliable ways to discover work, learn from troubleshooting content, make enquiries, request a consultation, and submit a basic support ticket. AI, payments, complex CRM, and advanced analytics should follow only when their requirements, operating costs, and safeguards are understood.

## 2. Discovery baseline and assumptions

- The supplied working directory contains an existing project/repository; its contents and conventions must be audited before implementation. Do not treat it as an empty directory.
- “IT Problem Solver” is the current working name. Name, domain, logo, owner identity, and brand settings must be configurable.
- Initial language is English. The product should be localization-ready without embedding Uganda-specific rules in core logic.
- A single engineer is the initial operating assumption; roles and assignments should accommodate a small support team later.
- Public content is anonymous and indexable where appropriate. Customer records and support content are private by default.
- Hosting, business jurisdiction, launch date, budget, and provider choices are not yet known. The choices below are proposals pending confirmation.

## 3. Product positioning and target users

### Positioning

A practical, accessible IT consultancy platform that demonstrates engineering capability, makes technical help easier to find, and provides a clear path from learning or discovery to professional support.

### Primary users

1. **Prospective clients** — individuals, small businesses, and organizations evaluating the engineer's expertise, services, and prior work.
2. **Customers** — people requesting a consultation or support, tracking appointments and tickets, and communicating securely.
3. **Public learners** — visitors looking for understandable, safe troubleshooting guidance and technical education.
4. **Engineer / administrator** — owner publishing content, managing enquiries, bookings, tickets, and service information.
5. **Future team members** — editors and technicians with limited, role-appropriate access.

### Core user outcomes

- Understand the engineer's expertise and credibility quickly.
- Browse projects, applications, services, and demonstrations.
- Find safe, useful technical guidance.
- Contact the engineer or request a booking/support without unnecessary account creation.
- Manage private requests securely after authentication.

## 4. Functional module breakdown

| Module | MVP | Later capability |
|---|---|---|
| Public website and configurable profile | Home, profile, contact, responsive navigation | Theme variants, advanced homepage composition |
| Portfolio | Projects, managed categories, case studies, images and approved video embeds | Upload/transcoding pipeline, comparison, public demos |
| Services | Managed categories and service pages | Pricing rules, service bundles, quotation links |
| Knowledge and publishing | Searchable articles, categories/tags, safety notes, SEO basics | Feedback, revisions, scheduled publishing, richer feeds |
| CMS | Secure admin, roles, content CRUD, media handling, drafts/publish | Approval workflow, revision recovery, advanced reports |
| Enquiries | Validated contact form, admin inbox, email notification | CRM automations and additional channels |
| Booking | Service request and booking workflow, configurable availability, confirmation | Payments, calendar/meeting integrations, reminders |
| Support | Basic private ticket create/reply/status workflow | SLA reporting, assignment automation, customer satisfaction |
| Customer account | Authentication and own bookings/tickets | Full customer portal, MFA, exports and retention controls |
| Mobile app | API foundation only; Flutter app is later/post-MVP | Public portfolio/services/articles/contact, then customer tickets/bookings, push, offline content |
| AI assistant | Not required for MVP; establish safe extension points only | Public knowledge assistant, source attribution, escalation, then authorized customer context |
| Billing and payments | Out of MVP | Quotes, invoices, payment adapters and verified webhooks |
| Analytics | Operational counts needed by admin | Privacy-conscious engagement, AI and revenue reporting |

## 5. Proposed architecture

### Recommendation for approval

- **Backend:** Supported Laravel release with a supported PHP version, modular monolith, PostgreSQL, versioned REST JSON API, Laravel Sanctum for first-party/mobile authentication, policies for authorization, and queued notifications where warranted.
- **Web:** Laravel Blade with Livewire for interactive workflows. This keeps public pages server-rendered for SEO and bandwidth-conscious use, while reducing separate frontend deployment and API duplication. Use semantic HTML and progressive enhancement; avoid making JavaScript essential for core tasks.
- **CMS/admin:** Evaluate and, unless a proof-of-fit reveals a blocker, use Filament for authenticated administration. Keep domain rules in application services/models and policies rather than admin resources so web and API clients share behavior.
- **Mobile (later/post-MVP):** Flutter application consuming the same versioned API. A feature-oriented structure with presentation, application/state, domain, and data boundaries; choose Riverpod or an equivalent maintained state solution during the Flutter foundation spike. Use secure platform token storage.
- **Infrastructure:** Environment-based configuration; Docker Compose for reproducible local development if the selected host supports the same container model; object storage abstraction for media; Redis only when queue/cache/rate-limit scale justifies operating it. Start with database-backed queues if operational simplicity is preferable.
- **API contract:** OpenAPI documentation, consistent validation/error envelopes, pagination, explicit resource authorization, and contract/feature tests. Public content endpoints separated from authenticated customer endpoints.

This is a modular monolith, not a microservice architecture. It limits operational overhead for a small business while preserving clear boundaries for future extraction if demonstrated need arises.

### Laravel application boundaries

Suggested domain areas: Identity & Access, Content/Portfolio, Services, Knowledge, Enquiries, Scheduling, Support, Media, Notifications, and (later) AI/Billing. Avoid premature package-per-domain separation. Filament is an admin interface, not the source of business authorization or rules.

## 6. Database design approach

Use a relational schema with migrations, foreign keys, explicit ownership, and indexes on public lookup/filter columns and customer/status/time queries. Model many-to-many relations for project categories/technologies and article tags only where the content needs them. Use private storage references for ticket attachments and public media metadata separately. Keep audit events and payment/webhook records append-oriented where appropriate.

Initial entity groups: users/roles/permissions and customer profile; projects/categories/technologies/media; service categories/services; articles/categories/tags; bookings/availability; tickets/replies/attachments; enquiries; notifications; site settings; audit events. Quotes, invoices, payments, AI conversations, and vector/index records are later domains and should not be added to the MVP schema without approved use cases.

Before migrations, produce an ERD that records cardinality, ownership/privacy, deletion/retention behavior, indexes, and any soft-delete requirement. Avoid storing derived or duplicated values without a clear reporting/performance need.

## 7. AI integration strategy (later phase)

Keep AI out of the initial launch scope. When approved, introduce a server-side provider interface, administrator-configured provider/model allowlist, secrets stored only in deployment secrets, budget/rate controls, and an auditable request boundary. Retrieve only published and approved public knowledge for unauthenticated users. Any customer-aware retrieval must first enforce tenant/user ownership and least privilege; never pass unrelated records to a model.

Treat user messages and retrieved documents as untrusted input. Require source links for grounded answers, communicate uncertainty and automated-assistant identity, offer human escalation, redact sensitive data where feasible, and do not expose tools capable of arbitrary command execution. Evaluate quality, privacy, prompt-injection resistance, latency, and per-conversation cost before enabling a provider in production.

## 8. Accessibility plan

Target WCAG 2.2 AA for core web journeys and platform accessibility guidance for Flutter. Establish semantic templates, heading/landmark patterns, keyboard operation, visible focus, contrast, zoom/reflow, labels and actionable validation, accessible dialogs, reduced-motion behavior, captions/transcripts, and adequately sized touch targets as design-system acceptance criteria. Keep chatbot and booking flows usable without speech or motion.

Test critical journeys manually with keyboard-only use and screen readers (web and mobile); automated checks are supporting evidence, not proof of compliance. Include accessibility review in each feature's definition of done and record known limitations rather than claiming unverified conformance.

## 9. Security and privacy strategy

- Enforce authorization in Laravel policies and API boundaries; test object ownership for every customer resource.
- Use framework protections, validation, output encoding, CSRF protections where applicable, throttling, secure cookies/session settings, verified email as needed, and secure password reset flows.
- Validate uploads by content/type/size, store private attachments outside the public web root, authorize downloads, and avoid trusting client filenames.
- Keep secrets out of source control; use environment-specific secret management and rotate credentials.
- Maintain dependency scanning, audit logging for sensitive administration, backups with restoration tests, and an incident/retention plan.
- Use idempotent, signature-verified webhooks and an adapter boundary for any future payments; never store payment-card data.
- Minimize collection, define retention/deletion rules, disclose AI processing before collecting sensitive diagnostics, and confirm applicable jurisdictional obligations with the owner before launch.
- Perform threat modeling for authentication, customer data isolation, media, booking abuse, and future AI/payment boundaries before those features ship.

## 10. MVP scope and acceptance outline

### Included

Laravel foundation; secure admin/CMS; configurable professional profile; portfolio projects and case studies; managed service catalogue; searchable troubleshooting/article content; contact enquiries; basic appointment request/availability workflow; basic customer accounts and private support tickets; responsive accessible public website; versioned API foundation for a future Flutter client; email notifications; SEO essentials; automated tests and deployment documentation. Flutter is later/post-MVP.

### Explicitly deferred

Production AI responses, customer-context chatbot, voice AI, payment collection, full quotes/invoices, complex CRM, live support, advanced analytics, multi-technician SLA automation, video transcoding, and broad multilingual translation. The architecture should leave extension points but not create speculative modules.

### MVP release gate

Critical public journeys work on mobile and desktop; owner can publish/update core content; customers can submit enquiries and manage only their own bookings/tickets; authorization and upload tests pass; key journeys have keyboard/screen-reader review; API docs and backup/deploy instructions exist; no secrets are committed; known limitations are documented.

## 11. Phase-by-phase roadmap

| Phase | Outcomes / exit gate |
|---|---|
| 0 — Discovery and planning | Confirm audience, operating model, brand/region assumptions, MVP, architecture decisions, ERD outline, threat/accessibility plans, backlog and acceptance criteria. **Current phase.** |
| 1 — Foundation | Laravel setup, environments, database, auth/roles, admin shell, settings/media foundations, API conventions, CI and baseline tests. |
| 2 — Public portfolio and CMS | Public profile, project catalogue/case studies, media/video embeds, services, contact and admin publishing workflow. |
| 3 — Knowledge platform | Articles/troubleshooting, taxonomy, search/filter, safety content, SEO and accessibility review. |
| 4 — Consultancy | Customer access, booking availability/request flow, support tickets, notifications, admin workflows and ownership tests. |
| Later — AI assistance | Only after approval: provider adapter, public retrieval, source attribution, safety/abuse controls, escalation, cost monitoring; customer-aware mode separately gated. |
| Later — Flutter (post-MVP) | Public screens against API, then authenticated booking/ticket workflows, secure storage, accessibility and release preparation. No ordering between Flutter and AI is approved. |
| 7 — Business enhancements | Evaluate regional/international payment providers; implement approved quote/invoice/payment scope and reporting. |
| 8 — Production hardening | Security/accessibility/performance testing, UAT, deployment/monitoring, restore drill, documentation, launch gate. |

Phases may be sequenced with parallel design/API work after dependencies are agreed, but each release must remain demonstrable and tested.

## 12. Relative effort estimate

Planning estimates are relative person-weeks for one experienced full-stack engineer, excluding owner content preparation, procurement delays, and provider approval. They are directional, not a delivery commitment.

| Work area | Relative effort |
|---|---:|
| Discovery, design decisions, backlog and ERD | 2–3 person-weeks |
| Laravel foundation, auth, admin, CI/API | 3–5 |
| Public site, CMS content and portfolio/media | 4–6 |
| Services, articles, knowledge search and SEO | 3–5 |
| Booking, customer accounts, tickets, notifications | 4–7 |
| Flutter public companion and API integration (later/post-MVP) | 4–7 |
| AI assistant (separate later scope) | 4–8 |
| Quotes/payments/financial workflows (separate later scope) | 4–8+ |
| Hardening, accessibility, UAT and production readiness | 3–5 |

The web MVP is approximately **19-31 person-weeks**, the sum of the listed web MVP work-area ranges (excluding Flutter), not a separate overlap-adjusted estimate. The broader option including a basic Flutter companion was approximately **20-33 person-weeks** in the original initiation estimate; it is not an approved sequencing decision. These are planning ranges, not commitments. The range is wide because content, booking rules, hosting, and the depth of admin workflows remain unconfirmed. A solo developer's calendar duration will be longer than person-weeks where review or operational work is concurrent. Flutter and AI are both later/post-MVP, with no approved ordering between them.

## 13. Major risks and mitigations

| Risk | Mitigation |
|---|---|
| Broad scope delays a useful launch | Protect the MVP boundary; defer AI, payments and advanced CRM; agree release gates. |
| Owner content/brand is unavailable | Use configurable placeholders and a content checklist; do not fabricate credentials, testimonials, or client work. |
| Booking rules and timezone expectations are unclear | Confirm business timezone, working hours, approval/cancellation and on-site location rules before booking implementation. |
| Private ticket data or attachments leak | Explicit ownership policies, private storage, download authorization and cross-account tests. |
| AI produces unsafe or costly output | Keep out of MVP; provider adapter, retrieval allowlist, limits, logging, evaluation and human escalation before launch. |
| Regional payment selection proves difficult | Research provider availability/merchant obligations and fees when payments are approved; keep adapter design provider-neutral. |
| Media/video increases bandwidth and hosting costs | Start with optimized images and approved embeds; evaluate upload/transcoding after scale and budget are known. |
| Accessibility is treated as late polish | Add criteria to templates and feature acceptance; manual test core journeys throughout. |
| Hosting/operations are chosen too late | Confirm deployment target in Phase 0; validate queue, storage, backups, mail and staging in foundation. |
| Regulatory/privacy requirements vary by jurisdiction | Confirm operating and customer regions with owner; review retention, consent and processor terms before collecting sensitive data. |

## 14. Questions requiring owner input

These answers refine scope; safe defaults allow planning to continue.

1. **Owner identity and business name?** Required before public launch; use configurable placeholder content meanwhile.
2. **Final platform/brand name, domain, logo, colours?** Required for launch polish; default working name and proposed navy/blue/teal palette.
3. **Existing repository or code to import?** An existing project/repository is present in the supplied working directory; audit its state, structure, and conventions before proposing implementation changes.
4. **Hosting preference and operational constraints?** Needed before environment/deployment work; default to a managed Linux host supporting PHP, PostgreSQL, HTTPS, queues and object storage.
5. **Initial audience and service region/business jurisdiction?** Needed for localization, timezone, privacy and payment decisions; default English-first, region-flexible.
6. **Mobile platforms?** Confirm whether Android and iOS both must launch together; default Flutter source supports both, with store release timing decided later.
7. **Payment needs at launch?** Default no online payments in MVP; collect requests and confirm manually.
8. **AI provider preference and budget?** Default no production AI in MVP; provider remains unselected until cost/privacy review.
9. **Solo engineer or support team at launch?** Default one engineer, with role model ready for a small team.
10. **Are project examples, approved images/videos, bio, credentials and testimonials available?** Required for credible launch content; publish only verified and authorized material.
11. **Development budget and target launch date?** Needed to prioritize delivery and provider/hosting choices; the effort range above is not a schedule commitment.
12. **Business timezone, working hours, booking lead time, cancellation and on-site coverage rules?** Needed before booking behavior is finalized.

## 15. First executable development sprint (proposed)

**Sprint goal:** Close the highest-impact discovery gaps and prepare an approved, testable implementation backlog—without building production features before the architecture is accepted.

1. Owner confirms (or explicitly defers) items 1–5, 8–12 above; capture decisions and assumptions.
2. Confirm the MVP user journeys: browse project, find service/article, submit enquiry, request consultation, create/reply to own ticket, admin publishes content.
3. Produce the initial ERD and data classification/retention notes for MVP entities.
4. Record architecture decisions: Laravel + Blade/Livewire + Filament fit, PostgreSQL vs MySQL, auth/API approach, hosting/storage/queue baseline, Flutter state approach.
5. Create threat model and accessibility acceptance checklist for the selected journeys.
6. Prioritize a dependency-aware backlog with acceptance criteria and Definition of Done.
7. Once the owner approves the architecture and repository location is confirmed, start Sprint 1 foundation with a reproducible local setup, CI baseline, Laravel skeleton, and a deployable health check.

**Sprint acceptance:** Owner decisions and deferred assumptions are recorded; proposed ERD/backlog and architecture are reviewable; risks have owners or explicit deferrals; no application implementation begins until approval is given.

## 16. Approval requested

Please review and approve or amend:

1. The MVP boundary and deliberate deferral of AI and payments.
2. Laravel modular monolith with Blade/Livewire public web, Filament admin, PostgreSQL and versioned REST API; Flutter is a later/post-MVP client.
3. Solo-engineer, English-first and no-online-payment launch assumptions.
4. The proposed discovery sprint and effort ranges as planning baselines. Direction is approved; final brand, hosting, and production database choices remain open.

No major application feature implementation is authorized by this report alone.
