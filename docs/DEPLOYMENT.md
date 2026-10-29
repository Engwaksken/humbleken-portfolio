# Deployment plan

No production host or production database is selected. Hosting target, production DB, region and service/provider choices are **OPEN** and must remain configurable. PostgreSQL is a proposal, not a commitment. This document is a readiness checklist, not provider-specific runbook.

## Before selecting a target
Verify supported PHP/Laravel runtime, web root and HTTPS, database compatibility, queue/scheduler operation, persistent/private object storage, mail delivery, secret management, backups/restore, logs/monitoring, region/data processing, cost and recovery capabilities. Choose staging and production environments. Test against the chosen production database in CI before relying on database-specific features.

## Release checklist
1. Build from locked dependencies; run automated tests, static/style checks and dependency/security review.
2. Configure environment secrets outside source control; set production debug off and canonical URL/timezone/session settings only after owner decisions.
3. Provision least-privilege database/storage credentials and private media access. Enforce HTTPS and secure cookies.
4. Back up database and media; document retention and perform a restore drill. Define queue/scheduler failure visibility and log retention.
5. Run reviewed migrations with a rollback/forward recovery plan; no unreviewed destructive schema changes. Confirm health check and smoke-test public/private paths.
6. Confirm admin provisioning is performed securely; do not seed passwords or publish credentials.

## What this application needs on any host
These follow from the code and hold whatever target is chosen.

- **Runtime:** PHP 8.2+ with the `intl`, `mbstring` and PDO extensions for the chosen database. Composer cannot install dependencies without `intl`. Node is needed only to build assets.
- **Queue worker:** enquiry and support-ticket emails are queued. Without a running worker (`php artisan queue:work`, kept alive by the host's process manager) no notification emails are sent. Failed jobs land in `failed_jobs`; decide who checks them.
- **Scheduler:** no scheduled tasks exist yet. Add the scheduler when retention or clean-up jobs are introduced.
- **Mail:** a real mailer is required for email verification, password reset and notifications. `MAIL_MAILER=log` only writes to the log.
- **Environment settings:** `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` set to the canonical HTTPS address, `SESSION_SECURE_COOKIE=true`, `LOG_LEVEL` no lower than `warning`. Leave `ADMIN_MFA_REQUIRED` unset (it defaults to on); it is switched off only in `phpunit.xml`.
- **Build and release commands:** `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php artisan migrate --force`, `php artisan optimize`, `php artisan filament:optimize`. Restart queue workers after each release (`php artisan queue:restart`).
- **First administrator:** `php artisan app:create-admin-user` (interactive; 12-character minimum password). On first panel sign-in the administrator must set up an authenticator app and should store the recovery codes offline.
- **Health checks:** `/up` (framework) and `/api/v1/health` (JSON).
- **Search engines:** once the domain is known, add `Sitemap: https://<domain>/sitemap.xml` to `public/robots.txt`.

Deployment commands specific to a host, worker counts, database settings, region and recovery objectives depend on the OPEN target and are intentionally not invented here. See [SECURITY.md](SECURITY.md) for operational controls.
