# Architecture Decision Records

Planning decisions below reflect the approved direction, not proof that the associated product capability is implemented. See [PROJECT_INITIATION.md](PROJECT_INITIATION.md) for the original initiation baseline.

## ADR-0001: Laravel modular monolith
- **Status:** Accepted direction. Laravel 12 is present; application boundaries and product features remain to be built.
- **Decision:** One Laravel application, Blade/Livewire web experience, Filament administration, versioned JSON API for future Flutter client. Keep business rules and authorization outside admin UI.
- **Consequences:** Simple deployment; API and domain contracts need tests.

## ADR-0002: Runtime and persistence
- **Status:** Laravel 12 / PHP ^8.2 dependencies are present. Local SQLite and production PostgreSQL are proposed, not universally selected.
- **Decision:** Retain SQLite for local/testing where suitable. **Production database OPEN**; PostgreSQL is a candidate pending host compatibility, operations, backup and CI validation.

## ADR-0003: Authentication foundation
- **Status:** Sanctum dependency and personal access token migration exist. Customer authentication and authorization flows are not implemented.
- **Decision:** Sanctum is the candidate first-party API foundation; implement lifecycle, ownership policies, revocation and tests before exposing private resources.

## ADR-0004: Admin foundation
- **Status:** Filament dependency and initial admin panel/provider exist; not a completed CMS.
- **Decision:** Use Filament for administration, with explicit provisioning and least privilege. Do not treat panel visibility as domain authorization.

## ADR-0005: Defer AI and payments
- **Status:** Accepted for MVP planning.
- **Decision:** No production AI or payment integration in MVP. Require separate business, security, privacy, cost and provider approval.

## ADR-0006: Public enquiry handling
- **Status:** Implemented for MVP; retention period **OPEN**.
- **Decision:** Anonymous visitors submit name, email, optional phone, optional active service and message. Submissions are validated, rate-limited (5/minute per IP) and screened with a honeypot field; IP addresses are not stored. Status (new, in progress, closed) and account association can only be set by administrators. Admin users receive a queued email notification that names the sender and links to the admin panel; the message itself stays out of email.
- **Consequences:** Requires a running queue worker and configured mail in deployed environments. A retention/deletion rule for enquiries must be agreed before production launch.

## ADR-0007: Portfolio projects
- **Status:** Implemented for MVP; project categories and media **deferred**.
- **Decision:** Projects have draft/published status and a publish date. Publishing without a date publishes immediately; a future date schedules the project. Public pages only resolve projects that are published and whose date has passed; anything else returns 404. Bodies are Markdown rendered with raw HTML stripped and unsafe links removed. The Projects navigation link and homepage section appear only when at least one project is public. No sample projects are seeded, so no client work is invented.
- **Consequences:** Project categories and images (`Project *──* Category`, `Project *──* MediaAsset` in DATABASE.md) wait for the media/storage decision.

## ADR-0008: Knowledge articles, search and SEO
- **Status:** Implemented for MVP.
- **Decision:** Articles share the projects' draft/scheduled publishing and safe Markdown rendering through a `Publishable` trait and a common `PublicationStatus` enum. Each article has at most one category and any number of tags. Search is a case-insensitive keyword match (`LIKE`) on title, excerpt and body, combined with category and tag filters; it is portable across SQLite and PostgreSQL. A full-text index can replace it once the production database is chosen and content volume justifies it. `/sitemap.xml` lists only public pages. Pages carry description, canonical and Open Graph tags; articles add published and modified times.
- **Consequences:** `public/robots.txt` does not yet reference the sitemap because the production domain is open; add `Sitemap: https://<domain>/sitemap.xml` at launch.

## ADR-0009: Customer accounts and support tickets
- **Status:** Implemented for MVP. Booking requests and ticket attachments **deferred** pending owner decisions (booking rules, timezone, storage).
- **Decision:** Customer sign-in uses Laravel Fortify (registration, sign-in, password reset, password change) with Blade views. Registration is open, but customers must confirm their email before using account pages. Sign-up never grants admin access. Login is limited to 5 attempts per minute per email and IP; registration to 10 accounts per hour per IP; ticket submissions to 10 per minute per user. Two-factor sign-in and passkeys are not enabled yet.
- **Ownership:** Customer ticket pages always look tickets up through the signed-in user, so another customer's ticket is "not found". Admins work tickets only in the admin panel. Staff can add internal notes that customers never see. Owner and status are never taken from request input.
- **Notifications:** Admins receive queued emails for new tickets and customer replies; customers receive one when staff reply. Emails contain a link, not the ticket content.
- **Consequences:** Switching to invite-only registration means removing the registration feature in `config/fortify.php`. Two-factor sign-in for admin accounts is recommended before launch. Ticket retention is **OPEN**.

## Open decisions
- Final brand/name/domain, owner-approved public profile and content.
- Hosting target, production database, storage, queue, mail, monitoring and region/provider choices; all configurable and **OPEN**.
- Business jurisdiction, audience regions, timezone, retention obligations and booking rules.
- Flutter release platforms and state-management choice.
- Accessibility testing results and final scope/timing after owner content and scheduling needs are known.
