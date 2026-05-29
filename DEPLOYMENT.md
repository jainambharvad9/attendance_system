# Deployment

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

## Required secrets / settings

- `APP_URL` should match the live domain.
- MySQL credentials must be set in Hostinger `.env`.
- Public storage must be linked with `php artisan storage:link`.