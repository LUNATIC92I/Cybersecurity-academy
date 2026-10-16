# Installation

This guide covers installing Cybersecurity-Academy for **local development** and
for a **production Linux server**. For hardening and go-live checks see
[`SECURITY.md`](SECURITY.md); for server topology and process management see
[`DEPLOYMENT.md`](DEPLOYMENT.md).

---

## 1. Requirements

| Component | Minimum | Notes |
|---|---|---|
| PHP | 8.2 | 8.3 recommended |
| MySQL | 8.0 | MariaDB 10.6+ also works |
| Composer | 2.x | dependency management |
| Web server | Apache 2.4 / Nginx 1.18 | or PHP's built-in server for dev |

**Required PHP extensions:** `pdo_mysql`, `mbstring`, `gd`, `intl`,
`fileinfo`, `openssl`, `json`, `ctype`.

Verify with:

```bash
php -v
php -m | grep -E 'pdo_mysql|mbstring|gd|intl|fileinfo|openssl'
composer --version
```

On Debian/Ubuntu the extensions are typically:

```bash
sudo apt install php8.3-cli php8.3-mysql php8.3-mbstring php8.3-gd \
    php8.3-intl php8.3-curl php8.3-xml unzip
```

---

## 2. Get the code

```bash
git clone <your-repository-url> cybersecurity-academy
cd cybersecurity-academy
composer install            # add --no-dev --optimize-autoloader in production
```

---

## 3. Configure the environment

Copy the example file and generate an application key:

```bash
cp .env.example .env
php bin/console key:generate      # prints APP_KEY=base64:...
```

Copy the printed `APP_KEY=` line into `.env`. The key signs certificate
HMAC signatures and other secrets — **keep it stable** once certificates are
issued, because changing it invalidates every existing signature.

Edit `.env`:

```ini
APP_ENV=local                 # 'production' on a live server
APP_DEBUG=true                # MUST be false in production
APP_URL=http://127.0.0.1:8000 # the public URL in production (https://...)
APP_KEY=base64:...            # from key:generate
APP_DEMO_MODE=true            # labels payments as demo; false when a real provider is live

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cyberacademy
DB_USERNAME=academy
DB_PASSWORD=change-me

SESSION_SECURE_COOKIE=false   # true in production (HTTPS only)

MAIL_DRIVER=log               # writes mail to storage/logs; set smtp for production
PAYMENT_PROVIDER=demo         # demo | stripe
```

`.env` is git-ignored and must never be committed. In production you may inject
these as real environment variables instead of a file — the loader lets real
environment variables override the file.

---

## 4. Create the database

```sql
CREATE DATABASE cyberacademy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'academy'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON cyberacademy.* TO 'academy'@'localhost';
FLUSH PRIVILEGES;
```

Then apply the migrations and seed the content:

```bash
php bin/console migrate            # creates all tables
php bin/console seed               # reference data + curriculum + labs + questions
php bin/console seed --demo        # ALSO creates demo accounts (dev only)
```

`seed` is idempotent for reference content. `seed --demo` prints three demo
accounts (student, instructor, admin) and a one-time generated password.
**Never run `--demo` on a server that will hold real users** — delete those
accounts before go-live if you do.

`migrate:fresh` drops every table and re-migrates — convenient in development,
destructive in production:

```bash
php bin/console migrate:fresh --seed --demo    # DEV ONLY
```

---

## 5. Create your administrator

Create a real super administrator rather than relying on a demo account:

```bash
php bin/console make:admin --email=admin@example.org --name="Platform Admin"
```

The command prints a one-time password. Log in and change it immediately from
the account settings page.

---

## 6. File permissions

The web server user (e.g. `www-data`) must be able to write to `storage/` only:

```bash
chown -R www-data:www-data storage
find storage -type d -exec chmod 775 {} \;
find storage -type f -exec chmod 664 {} \;
```

`storage/` holds logs, the file-based cache, sessions, uploads and generated
certificate PDFs. It lives **outside the web root** (`public/`) and must never
be served directly. Nothing else in the project needs to be writable by the web
server.

---

## 7. Serve the application

### Local development

```bash
php -S 127.0.0.1:8000 -t public bin/dev-router.php
```

Open <http://127.0.0.1:8000>. The router script serves static assets and sends
everything else to the front controller.

### Production

Point your web server's document root at `public/` and route all non-file
requests to `public/index.php`. Full Apache and Nginx configurations are in
[`DEPLOYMENT.md`](DEPLOYMENT.md).

---

## 8. Scheduled maintenance (cron)

Housekeeping (expiring stale sessions, pruning rate-limit rows, cache cleanup)
runs via a single command. Add it to cron to run hourly:

```cron
0 * * * * cd /var/www/cybersecurity-academy && php bin/console maintenance:run >> storage/logs/cron.log 2>&1
```

---

## 9. E-mail

By default `MAIL_DRIVER=log` writes every outgoing message to
`storage/logs/` so you can develop without an SMTP server. For production set:

```ini
MAIL_DRIVER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yourdomain.org
MAIL_FROM_NAME=Cybersecurity-Academy
```

Send a verification e-mail to yourself (register a test account) to confirm
delivery before launch.

---

## 10. Verify the install

```bash
php bin/console translations:check     # must report no missing French keys
php vendor/bin/phpunit                 # requires a test database (see below)
```

If both pass and the home page loads, the installation is healthy. Proceed to
[`SECURITY.md`](SECURITY.md) before exposing the site publicly.

### Test database (optional, for running the suite)

```sql
CREATE DATABASE cyberacademy_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON cyberacademy_test.* TO 'academy'@'localhost';
```

Create a `.env.testing` alongside `.env` with the test database name:

```ini
APP_ENV=testing
DB_DATABASE=cyberacademy_test
```

The suite refuses to run against any database whose name is not clearly a test
database, and each test runs inside a rolled-back transaction.
