# Society Management SaaS

Multi-tenant housing-society management platform built on Laravel 12. Two panels today:

- **Super Admin** (`/superadmin`) — societies, subscription plans & subscriptions, platform billing, users, notices, support tickets, settings.
- **Society Admin** (`/society`) — members, units, maintenance billing, collections, expenses, assets, accounting, vendors, AMC, tenders, documents, support.

Server-rendered Blade, custom CSS in `public/css/app.css`, no JS build step.

## Local setup (Laravel Herd)

```bash
composer install
cp .env.example .env          # then set APP_URL=http://society-management.test
php artisan key:generate
php artisan migrate --seed    # DemoSeeder: full demo data for society_id=1
```

Demo logins are created by `database/seeders/UserSeeder.php`.

Tests run on in-memory SQLite:

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

## Mail

`MAIL_MAILER=log` writes mail to `storage/logs`. For real delivery either set the
`MAIL_*` values in `.env` or fill in **Settings → SMTP** in the super-admin panel —
that row overrides `.env` at runtime (`AppServiceProvider::applySmtpSettings()`).
Invitation, ticket and reminder mails are queued, so a queue worker must be running.

## Queue & scheduler

`QUEUE_CONNECTION=database` (jobs table already migrated). Scheduled commands are
registered in `routes/console.php`:

| Command | Schedule |
|---------|----------|
| `subscriptions:refresh-status` | daily |
| `subscriptions:send-renewal-alerts` | daily |
| `invoices:mark-overdue` | daily |
| `bills:mark-overdue` | daily |
| `bills:apply-late-fees` | daily |
| `bills:send-reminders` | daily |
| `amc:send-expiry-alerts` | daily |
| `announcements:dispatch-scheduled` | every minute |

## Deployment runbook (Hostinger shared hosting)

```bash
cd domains/<domain>/public_html/society-management
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

First deploy only:

```bash
php artisan db:seed --class=ProductionSeeder --force   # roles, permissions, masters, one super admin
```

Cron (hPanel → Advanced → Cron Jobs), both every minute:

```
cd /home/<user>/domains/<domain>/public_html/society-management && php artisan schedule:run >> /dev/null 2>&1
cd /home/<user>/domains/<domain>/public_html/society-management && php artisan queue:work --stop-when-empty --max-time=50 --tries=3 >> /dev/null 2>&1
```

Point the document root at `public/` (or the existing `.htaccess` rewrite). Set
`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>/society-management`.

## Conventions

See `CLAUDE.md` / `AGENTS.md`. In short: Form Requests for validation, Pest feature
tests per module, society scoping through `Controller::currentSociety()` and the
`BelongsToSociety` model trait, driver-agnostic date SQL via `App\Support\DateSql`.
