# Task attachment production settings

Task attachments are deliberately stored on Laravel's private `local` disk. Preview and download requests pass through authenticated project routes; do not expose `storage/app/private/task-attachments` from Nginx.

Set the production application URL:

```dotenv
APP_URL=https://neova.ir
FILESYSTEM_DISK=local
```

The application accepts at most 10 files per action, 25 MiB per file, and 100 MiB total. The web server and PHP limits must be larger than the application-level request limit.

1. Include `deploy/nginx/neova-uploads.conf` in the HTTPS `server` block, then validate and reload Nginx:

   ```bash
   sudo nginx -t
   sudo systemctl reload nginx
   ```

2. Configure the PHP-FPM pool's active `php.ini` (or a pool override), then restart the matching PHP-FPM service:

   ```ini
   upload_max_filesize = 25M
   post_max_size = 110M
   max_file_uploads = 20
   ```

3. Deploy the migration and refresh cached configuration:

   ```bash
   php artisan migrate --force
   php artisan optimize:clear
   php artisan config:cache
   ```

The local CLI currently reports `upload_max_filesize=2M` and `post_max_size=8M`; production must be updated before testing large uploads.
