# ALG3PL Marketing Platform — cPanel Deployment Guide

## 1. cPanel Subdomain Setup
- Login to cPanel → Subdomains
- Create: `marketing.alg3pl.com` pointing to `/public_html/marketing`
- Document Root: `/home/[user]/marketing/public`

## 2. Upload Files
Upload the entire project (excluding `vendor/` and `node_modules/`) to:
`/home/[user]/marketing/`

Then SSH in and run:
```bash
cd /home/[user]/marketing
composer install --no-dev --optimize-autoloader
```

## 3. MySQL Database
In cPanel → MySQL Databases:
- Create database: `alg3pl_marketing`
- Create user + assign all privileges

## 4. Configure .env
Edit `.env` with production values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://marketing.alg3pl.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=alg3pl_marketing
DB_USERNAME=[cpanel_db_user]
DB_PASSWORD=[password]

# Fill in Google API credentials
GOOGLE_APPLICATION_CREDENTIALS=/home/[user]/marketing/storage/google-service-account.json
ANTHROPIC_API_KEY=[your_key]
```

## 5. Google Service Account
- Go to Google Cloud Console → IAM → Service Accounts
- Create account, download JSON key
- Upload to: `/home/[user]/marketing/storage/google-service-account.json`
- Grant access to: GA4 Data API + Search Console API

## 6. Run Migrations & Seed
```bash
php artisan migrate --force
php artisan db:seed --class=CountrySeeder
php artisan make:filament-user  # Create admin account
```

## 7. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

## 8. Cron Job (cPanel)
Add to cPanel → Cron Jobs (every minute):
```
* * * * * /usr/local/bin/php /home/[user]/marketing/artisan schedule:run >> /dev/null 2>&1
```

## 9. Queue Worker
If cPanel supports background processes or supervisord:
```bash
php artisan queue:work --sleep=3 --tries=3
```
Otherwise, use a cron every 5 minutes:
```
*/5 * * * * /usr/local/bin/php /home/[user]/marketing/artisan queue:work --once >> /dev/null 2>&1
```

## 10. Admin Panel URL
`https://marketing.alg3pl.com/admin`

Login with the account created in step 6.
