# humbleken-portfolio

SmartEngineer is a configurable IT consultancy portfolio and customer support platform.
The current foundation uses Laravel 12 and PHP 8.2+, with a versioned JSON API.

## Local setup

Requirements: PHP 8.2+, Composer, PHP `intl` and SQLite extensions for the local setup.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan filament:assets
php artisan serve
```

If using XAMPP on Windows, enable `extension=intl` in the active `php.ini` and
restart the terminal before running Composer or Artisan commands.

Create the first CMS administrator with `php artisan app:create-admin-user`.
The command prompts for a unique email and password and does not print the password.

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
