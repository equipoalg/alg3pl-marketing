# ALG3PL Marketing Platform — Deploy Guide

> **Host:** BanaHosting cPanel (shared) · **Access:** cPanel Terminal (no SSH) · **PHP:** 8.3 (ea-php83) · **DB:** MySQL (cPanel)
>
> **Primary URL:** https://marketing.alg3pl.com/admin

---

## Quick Reference — Daily Deploy

### On your Mac (local)
```bash
git add .
git commit -m "descripción corta del cambio"
git push origin main
```

### On the server (cPanel → Terminal)
```bash
cd /home/vdkzvusa/marketing.alg3pl.com
git pull origin main
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan cache:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
/opt/cpanel/ea-php83/root/usr/bin/php artisan filament:optimize
```

### When the commit changes `composer.json`
Add after `git pull`:
```bash
/opt/cpanel/ea-php83/root/usr/bin/composer install --no-dev --optimize-autoloader
```

### When the commit adds migrations
Add at the end:
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
```

### Verify
Open https://marketing.alg3pl.com/admin in an incognito window. Should load the login screen (or dashboard if cookie-authenticated).

---

## First-Time Setup (new install)

### 1. cPanel subdomain
- cPanel → Subdomains → create `marketing.alg3pl.com`
- Document Root: `/home/vdkzvusa/marketing.alg3pl.com/public`

### 2. Clone the repo
In cPanel Terminal:
```bash
cd /home/vdkzvusa
rm -rf marketing.alg3pl.com   # wipe default Apache files
git clone https://github.com/equipoalg/alg3pl-marketing.git marketing.alg3pl.com
cd marketing.alg3pl.com
```

### 3. Install dependencies
```bash
/opt/cpanel/ea-php83/root/usr/bin/composer install --no-dev --optimize-autoloader
```

### 4. MySQL database
cPanel → MySQL Databases:
- Create database (e.g. `vdkzvusa_alg3pl`)
- Create user + grant all privileges
- Note credentials for `.env`

### 5. Configure `.env`
Copy `.env.example` to `.env` and fill:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://marketing.alg3pl.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=vdkzvusa_alg3pl
DB_USERNAME=vdkzvusa_xxx
DB_PASSWORD=xxx

MAIL_MAILER=smtp
MAIL_HOST=mail.alg3pl.com
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=marketing@alg3pl.com
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS=marketing@alg3pl.com

# Integrations (fill as needed)
GOOGLE_APPLICATION_CREDENTIALS=/home/vdkzvusa/marketing.alg3pl.com/storage/google-credentials.json
META_APP_ID=
META_PAGE_ACCESS_TOKEN=
WHATSAPP_API_TOKEN=
ANTHROPIC_API_KEY=
```

Generate APP_KEY:
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan key:generate
```

### 6. Run migrations + seed
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php83/root/usr/bin/php artisan db:seed --class=CountrySeeder
```

### 7. Create admin user
Interactive:
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan make:filament-user
```

Or scripted (edit email/password first):
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan tinker --execute="\
\$u = \App\Models\User::firstOrNew(['email' => 'roberto@diaztercero.com']);\
\$u->name = 'Roberto Diaz';\
\$u->password = \Hash::make('REPLACE_WITH_STRONG_PASSWORD');\
\$u->is_super_admin = true;\
\$u->role = 'super_admin';\
\$u->save();\
echo 'User ID: ' . \$u->id . PHP_EOL;"
```

### 8. Optimize for production
```bash
/opt/cpanel/ea-php83/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan view:cache
/opt/cpanel/ea-php83/root/usr/bin/php artisan filament:optimize
```

### 9. Scheduler cron (cPanel → Cron Jobs)
Run every minute:
```
* * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home/vdkzvusa/marketing.alg3pl.com/artisan schedule:run >> /dev/null 2>&1
```

### 10. Queue worker (cPanel → Cron Jobs)
Every 5 minutes (shared hosting, no daemon):
```
*/5 * * * * /opt/cpanel/ea-php83/root/usr/bin/php /home/vdkzvusa/marketing.alg3pl.com/artisan queue:work --once --timeout=120 >> /dev/null 2>&1
```

---

## Troubleshooting

### 500 error on /admin
1. Check PHP version used by handler:
   - cPanel → MultiPHP Manager → confirm `marketing.alg3pl.com` is on PHP 8.3
   - If recently changed, `touch public/.htaccess` to force worker recycle
2. Tail the log:
   ```bash
   tail -100 /home/vdkzvusa/marketing.alg3pl.com/storage/logs/laravel.log
   ```
3. Clear caches:
   ```bash
   /opt/cpanel/ea-php83/root/usr/bin/php artisan config:clear
   /opt/cpanel/ea-php83/root/usr/bin/php artisan cache:clear
   /opt/cpanel/ea-php83/root/usr/bin/php artisan view:clear
   ```

### PHP version verification
One-shot check — upload `storage/diagnostics/_ping.php` to `public/` temporarily, hit the URL, then delete:
```bash
cp storage/diagnostics/_ping.php public/_ping.php
# browser: https://marketing.alg3pl.com/_ping.php
rm public/_ping.php
```

### "Credenciales incorrectas" at /admin login
The admin user doesn't exist or password is wrong. Re-run step 7 (create admin user) with a new password.

### Git pull reports local changes
If you edited files directly on server and need to discard them:
```bash
git stash
git pull origin main
```
Or, to FORCE overwrite local with remote:
```bash
git reset --hard origin/main
git pull origin main
```
**Do this carefully** — it destroys uncommitted changes.

### Credentials file missing
`.env` and `storage/google-credentials.json` are gitignored. They must exist on server but never in the repo. Recreate locally from `.env.example` if needed.

---

## Safety Rules

- **NEVER** commit `.env`, `storage/google-credentials.json`, `*.key`, `*.pem`, `auth.json`
- **NEVER** commit files to `public/` that start with `_` or `deploy` or `fix-` or `diag` (they are gitignored)
- **NEVER** force-push to `main` without explicit need
- **ALWAYS** test locally before pushing (at minimum: `php artisan config:clear && php artisan serve`)
- **ALWAYS** clear caches after deploy (see Quick Reference)
