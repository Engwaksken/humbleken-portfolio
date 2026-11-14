# humbleken-portfolio

IT Problem Solver is a configurable IT consultancy portfolio and customer support platform.
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

## Editing page content

In the admin panel, **Configuration → Pages** edits every home-page section and the fixed
pages (Services, Approach, Booking, Contact):

- **Home sections:** hero, getting-started card or hero image, services, "why work with us",
  recent work, and the call to action. Each can be shown or hidden.
- **Other pages:** a header image, steps with images, extra text, and a picture gallery.

Uploaded images are resized to at most 2000 px and compressed to WebP. They are served from
`/media/pages/...`, so no storage symlink is needed, and images that are no longer used are
deleted when the pages are saved.

## Currency, payments and notifications

- **Settings, Payments:** the site currency (Ugandan Shillings, UGX, by default) used for
  service prices, plus ioTec Pay credentials (the client secret is stored encrypted, and
  "Test ioTec connection" checks them), bank transfer details and a direct mobile money number.
- **Settings, Auto replies:** the email clients get straight after an enquiry or appointment
  request. Placeholders such as `{name}`, `{reference}` and `{payment_details}` are filled in.
- **Branding:** every email (alerts, auto replies, sign-up, password reset, sign-in codes)
  uses the logo, primary colour and brand name. Settings, Email has options to hide the
  logo, set the sign-off name and add footer text. Bell and push notifications use the
  brand colour and favicon.
- **Configuration, Notifications:** each administrator picks email, dashboard bell and
  browser push per alert type, and can turn push on per browser. Customers can turn off
  ticket reply emails from their account page.

Browser push needs HTTPS (or localhost) and the `minishlink/web-push` package:
`composer require minishlink/web-push`. Without it, email and the bell still work.

## Mobile app and API

A Flutter companion app lives in [`mobile/`](mobile/README.md). It uses the versioned JSON API
documented in [`docs/API.md`](docs/API.md) for public content, enquiries, bookings, customer
sign-in and support tickets.

## Tests

```powershell
php artisan test
```

## Production deployment checklist

Before exposing the application publicly:

- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the HTTPS site URL,
  and provide a unique `APP_KEY` through the deployment secret manager. Never
  regenerate the key for an existing installation.
- Configure a production database and run `php artisan migrate --force` during
  deployment. Set up backups and verify that a restore works.
- Configure a real mail transport and sender address. The example environment
  uses the `log` mailer and is not suitable for delivering email.
- Configure persistent storage and queue/cache services appropriate for the
  deployment. Run a supervised queue worker if queued jobs are enabled.
- Serve the app over HTTPS and set `SESSION_SECURE_COOKIE=true`. Ensure the web
  server points to Laravel's `public/` directory and that `storage/` and
  `bootstrap/cache/` are writable by the application process.
- Install frontend dependencies and build assets with `npm ci && npm run build`.
  The CI workflow checks this build, but deployment still needs to publish the
  generated `public/build` directory with the application release.
- Run `php artisan optimize` after deployment configuration is available, and
  verify the site and `/api/v1/health` endpoint from outside the host.

The repository does not include a hosting-specific deployment definition.
Validate these items against the selected host's secret management, database,
TLS, backups, process supervision, and rollback procedures before launch.

Product planning and scope are documented in [`docs/PROJECT_INITIATION.md`](docs/PROJECT_INITIATION.md).
