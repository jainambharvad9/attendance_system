# Deployment

## Runtime requirements

- PHP 8.3 or newer is required.
- Composer installs will fail on PHP 8.1 because this app uses Laravel 13.

## GitHub

1. Create a GitHub repository under `jainambharvad9`.
2. Add the repo as the `origin` remote.
3. Push the `main` branch.

```bash
git remote add origin https://github.com/jainambharvad9/REPO_NAME.git
git branch -M main
git push -u origin main
```

## Hostinger webhook

1. Open Hostinger hPanel.
2. Go to your website's Git / Deploy section.
3. Connect the GitHub repository and select `main`.
4. Set the deploy path to your app folder.
5. Add deployment commands if Hostinger supports them:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan migrate --force`
   - `php artisan config:cache`
   - `php artisan route:cache`
6. Save and trigger a test deploy.

If your Hostinger plan is still using PHP 8.1, switch the site to PHP 8.3+ before running Composer or the deployment will stop with dependency errors.

## Required secrets / settings

- `APP_URL` should match the live domain.
- MySQL credentials must be set in Hostinger `.env`.
- Use the exact Hostinger database name, database user, and database password from hPanel.
- On Hostinger shared hosting, `DB_HOST` is often `localhost`; if `127.0.0.1` fails, switch to `localhost` and retry.
- `DB_PASSWORD` must be the real database password, not a placeholder or empty value.
- Public storage must be linked with `php artisan storage:link`.

## Database error 1045

If you see `SQLSTATE[HY000] [1045] Access denied for user`, fix the `.env` values first, then run:

```bash
/opt/alt/php83/usr/bin/php artisan config:clear
/opt/alt/php83/usr/bin/php artisan cache:clear
```

Then retry:

```bash
/opt/alt/php83/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
/opt/alt/php83/usr/bin/php artisan migrate --force
```
