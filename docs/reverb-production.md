# Reverb deployment for neova.ir

The browser connects to `wss://neova.ir/app/...`; Nginx terminates TLS and proxies both Reverb's `/app` WebSocket endpoint and `/apps` API endpoint to a single process on `127.0.0.1:8080`.

1. Copy the variables from `deploy/neova.reverb.env.example` into the production `.env`. Generate independent credentials, for example with `openssl rand -hex 16`. Never commit the resulting secret.
2. Install and build from the release directory:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   ```

3. Include `deploy/nginx/neova-reverb.conf` inside the existing `listen 443 ssl` server block. The location must be evaluated before a generic PHP/front-controller location. Then validate and reload:

   ```bash
   sudo nginx -t
   sudo systemctl reload nginx
   ```

4. Install and start the service:

   ```bash
   sudo install -m 0644 deploy/systemd/neova-reverb.service /etc/systemd/system/neova-reverb.service
   sudo systemctl daemon-reload
   sudo systemctl enable --now neova-reverb
   sudo systemctl status neova-reverb
   ```

After every deployment that changes application code or configuration, run `php artisan reverb:restart`. Check server output with `journalctl -u neova-reverb -f`. In the browser, the WebSocket request under `/app/{REVERB_APP_KEY}` should return HTTP 101. Private channel authentication uses the normal Laravel session at `/broadcasting/auth`.

This setup intentionally runs one Reverb process and caps the configured application at 1,000 connections. If usage approaches that limit, enable Reverb scaling with Redis and run multiple processes behind Nginx.
