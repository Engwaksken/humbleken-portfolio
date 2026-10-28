# Architecture Decision Records

## ADR-0001: Start as a Laravel modular monolith

- **Status:** Accepted for foundation; revisit if constraints change.
- **Context:** The product is initially operated by one engineer and needs public pages, administration, and customer workflows without unnecessary operational complexity.
- **Decision:** Build one Laravel application with domain boundaries, server-rendered public pages, and a versioned JSON API shared with Flutter. Keep authorization and business rules in the backend, not in CMS UI code.
- **Consequences:** One deployment and codebase reduce operational overhead. Boundaries and API contracts must be maintained so later clients or services do not duplicate business rules.

## ADR-0002: Laravel 12 and PHP 8.2 baseline

- **Status:** Accepted for initial scaffold.
- **Context:** The available environment has PHP 8.2.12; Laravel 12 supports this baseline.
- **Decision:** Scaffold Laravel 12, lock exact dependencies in `composer.lock`, and use PHP 8.2 in the initial CI job. Revisit the runtime baseline as part of production hosting selection and framework support review.
- **Consequences:** Local development can proceed in the available environment. Production must use a supported PHP/framework combination and should be upgraded deliberately.

## ADR-0003: Sanctum for first-party API authentication foundation

- **Status:** Accepted for foundation; authentication flows remain unimplemented.
- **Context:** The product needs a Laravel API consumed by its own Flutter application and web clients.
- **Decision:** Install Laravel Sanctum and use it as the initial first-party token/session authentication foundation. Protect customer resources with policies and ownership checks as those modules are implemented.
- **Consequences:** Sanctum's access tokens and middleware are available. The existence of the package is not evidence that production authentication, token lifecycle, or authorization is complete.

## ADR-0004: SQLite locally; PostgreSQL remains the production recommendation

- **Status:** Accepted for local development; production database pending hosting confirmation.
- **Context:** No database server or Docker is available in the current environment, while a lightweight local setup is useful for the foundation sprint.
- **Decision:** Use Laravel's SQLite default for local setup and automated tests. Retain PostgreSQL as the proposed production database; verify provider support, backups, extensions, and operational requirements before committing to production configuration.
- **Consequences:** Migrations and tests must remain portable. Before database-specific features are introduced, add CI coverage against the selected production database.

## ADR-0005: Keep AI and payments out of the first release foundation

- **Status:** Accepted for MVP planning.
- **Context:** AI provider costs/privacy controls and regional payment availability/merchant requirements are unconfirmed.
- **Decision:** Do not add provider-specific AI or payment dependencies until the owner approves a separate scope and operational review.
- **Consequences:** Initial releases focus on portfolio, knowledge, enquiries, booking requests, and basic support. Later integrations require security, cost, compliance, and failure-mode reviews.

## Open decisions

- Confirm production host, database, object storage, queue and mail services.
- Validate Filament and Livewire against accessibility and admin workflow needs before selecting/adding packages.
- Select the Flutter state-management library during the mobile foundation spike.
- Define business timezone and booking rules before scheduling implementation.
