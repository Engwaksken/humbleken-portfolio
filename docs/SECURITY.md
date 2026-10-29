# Security and privacy plan

Planning baseline, not a security certification. Current scaffolding includes an admin flag/panel gate and Sanctum dependency; product authorization, customer isolation, upload workflow, monitoring and operational controls remain unverified/unimplemented.

## Controls required before release
- Threat-model admin compromise, account recovery, enumeration, public-form abuse, cross-customer access, uploads/downloads, booking spam, supply chain and deployment secrets.
- Enforce least privilege and policy authorization on every web/API action; test cross-account read/write denial. Admin provisioning remains explicit; never document credentials.
- Framework validation, output encoding, CSRF/session protections, secure cookies/TLS, throttling, safe password reset and appropriate email verification. Define token expiry/revocation and device-loss behavior before mobile auth.
- Validate upload size/content and authorization; store private attachments outside public web access; use generated storage keys, scan where operationally available, and avoid trusting filenames.
- Keep secrets in deployment secret management; no `.env`, API keys or owner credentials in docs/repository. Patch dependencies, log security events without unnecessary message/PII capture.
- Define data classification, purpose, retention/deletion, backup encryption/access and restore drill; confirm jurisdictional obligations and processor terms with owner before collecting customer data.

## Implemented controls (as of 2026-10-29)
Covered by automated tests; not a substitute for an independent review or penetration test.

- **Admin access:** panel limited to `is_admin` users, which only the console command can create; authenticator-app two-factor required, with encrypted secrets and recovery codes.
- **Customer isolation:** ticket pages look records up through the signed-in owner (others get 404); internal staff notes never reach customers; owner and status are never mass-assignable from requests.
- **Accounts:** email verification required; login 5/min per email+IP; registration 10/hour per IP; password reset gives the same answer for unknown emails.
- **Public forms:** validation, CSRF, per-IP rate limits, enquiry honeypot.
- **Output:** Blade escaping; Markdown rendered with raw HTML stripped and unsafe links removed; admin-entered social links limited to `http(s)` URLs.
- **Headers:** `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS.
- **Email:** notifications carry links, not enquiry or ticket content.
- **Supply chain:** CI runs `composer audit`, `npm audit` (high and above), style checks and the test suite.

**Not yet done:** a Content-Security-Policy (the admin panel's inline scripts need a nonce-based policy and testing); finer-grained admin roles and policies (all admins are currently equal); retention/deletion jobs; incident contacts; an independent security review.

## AI boundary (deferred)
No production AI in MVP. Any later AI requires explicit approval, server-side provider interface, reviewed provider/model and region, budget/rate controls, secret management, data minimization, retention disclosure and evaluation. Anonymous retrieval must be restricted to approved published public sources. Customer-aware retrieval requires authorization before retrieval and strict ownership filtering. Treat prompts/documents as untrusted, defend against prompt injection, cite sources, signal uncertainty and automation, offer human escalation, and prohibit arbitrary privileged tools. No sensitive data sent without informed basis and review.

## Incident readiness
Before launch define incident contacts/ownership (without inventing them here), response and notification obligations, log access/retention, backup restoration responsibilities, and vulnerability reporting path. Hosting/provider and jurisdiction are OPEN.
