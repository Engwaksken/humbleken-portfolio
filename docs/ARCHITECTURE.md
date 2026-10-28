# Architecture

## Evidence and current state
Repository inspection found Laravel 12 and PHP ^8.2 in composer.json; Filament ^5 and Sanctum ^4 dependencies; an initial admin panel/provider, a user model, `is_admin` migration, personal access token migration, SQLite-default Laravel setup and `/api/v1/health` route. Root web route currently returns Laravel's welcome view. There are no portfolio, booking, ticket, CMS content or Flutter product modules evidenced. Dependencies and scaffolding are not completed product features.

## Approved direction
Laravel modular monolith. Blade with Livewire for progressive-enhancement web flows; Filament administration; domain policies and services own rules, not panel resources. Versioned JSON REST API (`/api/v1`) serves explicitly public content and separately authenticated customer operations. Sanctum is a candidate first-party API auth mechanism, not a statement that flows exist. Flutter is a planned client, not present implementation. OpenAPI and contract tests should accompany API implementation.

Logical areas: identity/access; content/portfolio/media; services; knowledge; enquiries; scheduling; support; notifications. AI and billing are deferred. Avoid premature service split. Maintain public/private storage separation and provider adapters only when an approved integration exists.

## Decisions and open configuration
See [DECISIONS.md](DECISIONS.md). Hosting target, production database (PostgreSQL proposed, OPEN), region, object storage, mail, queue/cache, monitoring and providers are OPEN. Keep environment-specific values configurable; no credentials in docs/source. SQLite may support local development; production compatibility must be tested before database-specific behavior.

## Quality boundaries
Policies enforce record ownership at every entry point; validation and response contracts are shared across web/API; tests exercise negative access cases. Server-render public content for discoverability. Accessibility and security requirements are in [PRD.md](PRD.md) and [SECURITY.md](SECURITY.md). Deployment is described in [DEPLOYMENT.md](DEPLOYMENT.md).
