# Product requirements — IT Problem Solver

**Status:** Phase 0 planning; approved direction, not an implementation claim. Working name only. See [PROJECT_INITIATION.md](PROJECT_INITIATION.md), retained as the discovery record.

## Product and users
Create a configurable professional portfolio and consultancy platform: public information and knowledge, with clear enquiry, booking-request and support paths. Users: prospective clients, public learners, customers, administrator, and later limited staff. Do not publish invented identity, credentials, client work, testimonials or contact details.

## MVP
- Responsive, accessible public home/profile, portfolio projects/case studies, services, searchable knowledge articles, contact/enquiry form.
- Admin-managed drafts and publishing for those content types, with restricted admin access.
- Customer account foundation and private booking requests and support tickets/replies; customers can access only their own records. Scope of booking availability and approvals needs owner confirmation.
- Email notification, SEO essentials, documented versioned API foundation for future Flutter client.
- No online payments, production AI, complex CRM, live chat, advanced analytics, or Flutter app of any kind in MVP. Flutter is later/post-MVP and requires separate prioritization.

## Acceptance criteria / release gate
1. Anonymous visitor can browse published content and submit a validated enquiry; unpublished content is not public.
2. Administrator can create/edit/publish core content and process enquiries; ordinary users cannot access admin functions.
3. Authenticated customer can submit a booking request, create/reply to own ticket, and cannot read or mutate another customer's records (negative cross-owner tests).
4. Uploads, if enabled, are type/size validated and private data downloads authorized; no client filename trust.
5. Core journeys work at mobile and desktop widths, keyboard-only, with labels, visible focus and actionable validation; manual assistive-technology review recorded. Do not claim WCAG conformance without audit.
6. As features are implemented, required feature/API tests cover validation, privacy, authorization and errors; deployment, backup/restore and API documentation are reviewed; secrets absent from source.

## Non-functional requirements
Privacy-by-default, minimal collection, configurable identity/brand and locale/timezone, graceful failure, semantic server-rendered web, maintainable modular monolith, documented operations. Performance and availability targets remain OPEN until hosting and usage assumptions are known.

## Risks and dependencies
See [ROADMAP.md](ROADMAP.md) and [SECURITY.md](SECURITY.md). Owner content, region/jurisdiction, booking rules, hosting, production DB and operating budget are unresolved. Never fabricate sample customer or portfolio data for production.
