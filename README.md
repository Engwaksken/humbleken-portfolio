# humbleken-portfolio

SmartEngineer is a configurable IT consultancy portfolio and customer support platform.
The current foundation uses Laravel 12 and PHP 8.2+, with a versioned JSON API.

## Local setup

Requirements: PHP 8.2+, Composer, and the PHP SQLite extension for the default local database.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Visit `http://127.0.0.1:8000` for the web application and
`http://127.0.0.1:8000/api/v1/health` for the versioned API health check.

The default local configuration uses SQLite. Production database, mail, storage,
and queue settings must be supplied through the deployment environment; never
commit `.env` or production credentials.

## Tests

```powershell
php artisan test
```

Product planning and scope are documented in [`docs/PROJECT_INITIATION.md`](docs/PROJECT_INITIATION.md).
