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

Deployment commands, worker counts, database settings, region and recovery objectives depend on the OPEN target and are intentionally not invented here. See [SECURITY.md](SECURITY.md) for operational controls.
