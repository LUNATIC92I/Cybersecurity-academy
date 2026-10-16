# Deployment

How to run Cybersecurity-Academy on a production Linux server. This assumes you
have completed [`INSTALLATION.md`](INSTALLATION.md) and read
[`SECURITY.md`](SECURITY.md).

---

## 1. Topology

```
            HTTPS (443)
  Browser ───────────────▶  Apache / Nginx  ──▶  PHP-FPM  ──▶  MySQL
                                   │
                              document root
                             = public/ only
```

- Only `public/` is web-accessible. The front controller is
  `public/index.php`.
- `app/`, `config/`, `database/`, `lang/`, `resources/`, `routes/`,
  `storage/`, `vendor/` and `.env` sit **above or beside** the web root and
  must never be served.

---

## 2. Prepare the release

```bash
composer install --no-dev --optimize-autoloader
php bin/console migrate            # apply any new migrations
php bin/console translations:check # fail the deploy if French is incomplete
```

Set production values in `.env` (or real environment variables):

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://academy.example.org
SESSION_SECURE_COOKIE=true
```

`APP_DEBUG=false` is mandatory — it hides stack traces and switches error
pages to safe, generic responses.

---

## 3. Nginx + PHP-FPM

```nginx
server {
    listen 443 ssl http2;
    server_name academy.example.org;

    root /var/www/cybersecurity-academy/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/academy.example.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/academy.example.org/privkey.pem;

    # Serve real files, otherwise hand off to the front controller.
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Never serve dotfiles or storage.
    location ~ /\.(?!well-known) { deny all; }

    client_max_body_size 12m;   # a little above UPLOAD_MAX_BYTES
}

server {
    listen 80;
    server_name academy.example.org;
    return 301 https://$host$request_uri;
}
```

The application also emits a strict Content-Security-Policy and other security
headers itself, so you do not need to duplicate them in the server config.

---

## 4. Apache

Enable `mod_rewrite`, `mod_headers` and your TLS module, then:

```apache
<VirtualHost *:443>
    ServerName academy.example.org
    DocumentRoot /var/www/cybersecurity-academy/public

    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/academy.example.org/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/academy.example.org/privkey.pem

    <Directory /var/www/cybersecurity-academy/public>
        AllowOverride All      # honour the bundled public/.htaccess
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName academy.example.org
    Redirect permanent / https://academy.example.org/
</VirtualHost>
```

The shipped `public/.htaccess` routes non-file requests to `index.php` and
blocks access to dotfiles.

---

## 5. HTTPS

Use a trusted certificate (e.g. Let's Encrypt via certbot):

```bash
sudo certbot --nginx -d academy.example.org
```

With HTTPS in place, set `SESSION_SECURE_COOKIE=true` so session cookies are
only sent over TLS. The app sets `HttpOnly` and `SameSite` on cookies
regardless.

---

## 6. Payments go-live

Payments ship in **demo mode** (`PAYMENT_PROVIDER=demo`, `APP_DEMO_MODE=true`),
which labels checkout clearly and never charges anyone. To accept real
payments with the implemented Stripe provider:

1. Set `PAYMENT_PROVIDER=stripe`, `APP_DEMO_MODE=false`, and your
   `STRIPE_SECRET_KEY` / `STRIPE_WEBHOOK_SECRET`.
2. Configure a Stripe webhook endpoint pointing at the app's webhook route and
   copy its signing secret into `STRIPE_WEBHOOK_SECRET`.
3. **Verify an end-to-end test-mode checkout yourself.** The webhook signature
   verification is unit-tested, but this repository has **not** been run
   against a live Stripe account — do not sell access until you have confirmed
   a real checkout and webhook settlement in your own environment.

Enrolment is only ever granted server-side when a verified webhook confirms
payment, never on a browser redirect.

---

## 7. Scheduled jobs

```cron
0 * * * * cd /var/www/cybersecurity-academy && php bin/console maintenance:run >> storage/logs/cron.log 2>&1
```

Runs hourly housekeeping (session expiry, rate-limit pruning, cache cleanup).

---

## 8. Backups

Back up two things:

1. **The database** — contains accounts, progress, payments and certificate
   records.
2. **`storage/`** — generated certificate PDFs and uploads.

```bash
mysqldump --single-transaction cyberacademy | gzip > backup-$(date +%F).sql.gz
tar czf storage-$(date +%F).tgz storage
```

Also store your `APP_KEY` securely and separately: it signs certificates, and
restoring a database without the original key invalidates every signature.

---

## 9. Upgrades

```bash
git pull
composer install --no-dev --optimize-autoloader
php bin/console migrate
php bin/console translations:check
php bin/console schema:dump         # if you want database.sql refreshed
```

Migrations are tracked, so `migrate` only applies new ones. Review the
changelog before upgrading a production instance, and take a backup first.

---

## 10. Post-deploy checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] HTTPS enforced, `SESSION_SECURE_COOKIE=true`
- [ ] No demo accounts in the database
- [ ] A real super admin exists with a changed password
- [ ] `storage/` writable by the web user and not web-accessible
- [ ] `.env` not served (test `curl https://host/.env` → 403/404)
- [ ] SMTP configured and a test e-mail received
- [ ] Cron maintenance job installed
- [ ] Backups scheduled; `APP_KEY` stored safely
- [ ] `translations:check` and `phpunit` pass against the release
