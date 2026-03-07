# LoomCraft Deployment Runbook (Hostinger VPS)

Last updated: 2026-02-24  
Target: Ubuntu 24.04 VPS (`31.97.51.24`)  
Domain: `loomcraft.work` (Namecheap) with `www` redirect to apex  
Web server: Nginx  
App stack: Laravel 12 + PHP 8.4 + MariaDB (local only)  
Deploy mode: GitHub Actions auto-deploy from `main`  

---

## 1) Fixed Deployment Decisions

- GitHub Actions is the CI/CD pipeline source.
- Deploy user is `deploy` (SSH key only).
- App path is `/var/www/loom-craft`.
- `www.loomcraft.work` must redirect to `loomcraft.work`.
- HTTPS via Let's Encrypt (Certbot) is required.
- PHP-FPM should run on 8.4.
- Queue worker and scheduler must run in production.
- Frontend build must happen in CI (not on the VPS).
- MariaDB runs on same VPS and must remain local/private only.

---

## 2) DNS (Namecheap)

Create/update these records:

- `A` record: host `@` -> `31.97.51.24`
- `A` record: host `www` -> `31.97.51.24`

After propagation, verify both resolve before issuing SSL.

---

## 3) VPS Bootstrap (Ubuntu 24.04)

Run as root once:

```bash
apt update && apt upgrade -y
apt install -y software-properties-common ca-certificates lsb-release curl unzip git nginx mariadb-server redis-server certbot python3-certbot-nginx
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.4-fpm php8.4-cli php8.4-common php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl php8.4-gd php8.4-redis
```

Install Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
```

Create deploy user (if missing) and app directories:

```bash
adduser --disabled-password --gecos "" deploy
usermod -aG www-data deploy
mkdir -p /var/www/loom-craft/{releases,shared,repo}
chown -R deploy:www-data /var/www/loom-craft
chmod -R 775 /var/www/loom-craft
```

Add GitHub Actions public key to `/home/deploy/.ssh/authorized_keys`.

---

## 4) MariaDB (Local-Only)

Secure and create database:

```bash
mysql_secure_installation
mysql -u root -p
```

```sql
CREATE DATABASE loom_craft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'loomcraft'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON loom_craft.* TO 'loomcraft'@'localhost';
FLUSH PRIVILEGES;
```

Keep MariaDB bound to localhost (default expected). Confirm in MariaDB config:

- `bind-address = 127.0.0.1`

---

## 5) Shared Runtime Files

Create persistent shared paths:

```bash
mkdir -p /var/www/loom-craft/shared/storage
mkdir -p /var/www/loom-craft/shared/bootstrap/cache
touch /var/www/loom-craft/shared/.env
chown -R deploy:www-data /var/www/loom-craft/shared
chmod -R 775 /var/www/loom-craft/shared/storage /var/www/loom-craft/shared/bootstrap/cache
```

Set production `.env` at `/var/www/loom-craft/shared/.env` with at least:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://loomcraft.work`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=loom_craft`
- `DB_USERNAME=loomcraft`
- `DB_PASSWORD=...`
- `PAYPAL_CLIENT_ID=...`
- `PAYPAL_CLIENT_SECRET=...`
- `PAYPAL_BASE_URL=https://api-m.paypal.com` (or sandbox if needed)

Generate app key once per environment:

```bash
cd /var/www/loom-craft/repo
cp .env.example /tmp/loomcraft-env-example
php artisan key:generate --show
```

Place that generated key into shared `.env` as `APP_KEY=...`.

---

## 6) Nginx Site Config

Create `/etc/nginx/sites-available/loomcraft.work`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name loomcraft.work www.loomcraft.work;

    root /var/www/loom-craft/current/public;
    index index.php index.html;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ ^/index\.php(/|$) {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable + reload:

```bash
ln -s /etc/nginx/sites-available/loomcraft.work /etc/nginx/sites-enabled/loomcraft.work
nginx -t
systemctl reload nginx
```

Issue HTTPS cert and force redirect:

```bash
certbot --nginx -d loomcraft.work -d www.loomcraft.work --redirect -m YOUR_EMAIL --agree-tos --no-eff-email
systemctl status certbot.timer
```

Ensure generated TLS server config redirects `www` to apex `loomcraft.work`.

---

## 7) Queue Worker + Scheduler

Create `/etc/systemd/system/loomcraft-queue.service`:

```ini
[Unit]
Description=LoomCraft Laravel Queue Worker
After=network.target

[Service]
User=deploy
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/loom-craft/current/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/loom-craft/current
StandardOutput=append:/var/www/loom-craft/shared/storage/logs/queue-worker.log
StandardError=append:/var/www/loom-craft/shared/storage/logs/queue-worker-error.log

[Install]
WantedBy=multi-user.target
```

Enable service:

```bash
systemctl daemon-reload
systemctl enable --now loomcraft-queue.service
systemctl status loomcraft-queue.service
```

Add scheduler cron (`crontab -e` for `deploy`):

```cron
* * * * * cd /var/www/loom-craft/current && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## 8) GitHub Actions Pipeline (Main -> VPS)

Create `.github/workflows/deploy-production.yml`:

```yaml
name: Deploy Production

on:
  push:
    branches: [main]
  workflow_dispatch:

concurrency:
  group: production-deploy
  cancel-in-progress: false

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          tools: composer:v2
          coverage: none

      - name: Setup Node
        uses: actions/setup-node@v4
        with:
          node-version: '22'

      - name: Install PHP dependencies
        run: composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

      - name: Install JS dependencies
        run: npm install

      - name: Build frontend assets
        run: npm run build

      - name: Prepare artifact
        run: |
          tar --exclude=.git \
              --exclude=.github \
              --exclude=node_modules \
              --exclude=tests \
              --exclude=.env \
              -czf release.tar.gz .

      - name: Upload artifact
        uses: actions/upload-artifact@v4
        with:
          name: release
          path: release.tar.gz

  deploy:
    runs-on: ubuntu-latest
    needs: build
    steps:
      - name: Download artifact
        uses: actions/download-artifact@v4
        with:
          name: release

      - name: Setup SSH key
        run: |
          mkdir -p ~/.ssh
          echo "${{ secrets.DEPLOY_SSH_KEY }}" > ~/.ssh/id_rsa
          chmod 600 ~/.ssh/id_rsa
          ssh-keyscan -H "${{ secrets.DEPLOY_HOST }}" >> ~/.ssh/known_hosts

      - name: Upload release tarball
        run: scp release.tar.gz ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }}:/tmp/release-${{ github.sha }}.tar.gz

      - name: Deploy on server
        run: |
          ssh ${{ secrets.DEPLOY_USER }}@${{ secrets.DEPLOY_HOST }} << 'EOF'
          set -euo pipefail
          RELEASE_DIR=/var/www/loom-craft/releases/${GITHUB_SHA}
          mkdir -p "${RELEASE_DIR}"
          tar -xzf /tmp/release-${GITHUB_SHA}.tar.gz -C "${RELEASE_DIR}"

          ln -sfn /var/www/loom-craft/shared/.env "${RELEASE_DIR}/.env"
          rm -rf "${RELEASE_DIR}/storage"
          ln -sfn /var/www/loom-craft/shared/storage "${RELEASE_DIR}/storage"
          mkdir -p /var/www/loom-craft/shared/storage/app/public
          rm -rf "${RELEASE_DIR}/public/storage"
          ln -sfn "${RELEASE_DIR}/storage/app/public" "${RELEASE_DIR}/public/storage"
          mkdir -p /var/www/loom-craft/shared/bootstrap/cache
          rm -rf "${RELEASE_DIR}/bootstrap/cache"
          ln -sfn /var/www/loom-craft/shared/bootstrap/cache "${RELEASE_DIR}/bootstrap/cache"

          cd "${RELEASE_DIR}"
          php artisan migrate --force
          php artisan config:cache

          ln -sfn "${RELEASE_DIR}" /var/www/loom-craft/current

          php /var/www/loom-craft/current/artisan queue:restart
          sudo systemctl reload php8.4-fpm
          sudo systemctl reload nginx

          rm -f /tmp/release-${GITHUB_SHA}.tar.gz
          EOF
        env:
          GITHUB_SHA: ${{ github.sha }}
```

Required GitHub Secrets:

- `DEPLOY_HOST` = `31.97.51.24`
- `DEPLOY_USER` = `deploy`
- `DEPLOY_SSH_KEY` = private key matching server authorized key

Optional:

- `DEPLOY_PORT` if not `22`

If `sudo` requires password for `deploy`, allow passwordless reload only:

- `/etc/sudoers.d/loomcraft-deploy`:
  - `deploy ALL=NOPASSWD:/bin/systemctl reload nginx,/bin/systemctl reload php8.4-fpm`

---

## 9) First Deployment Checklist

1. DNS records resolve to `31.97.51.24`.
2. Nginx site active and reachable on HTTP.
3. SSL certificate issued and valid on `https://loomcraft.work`.
4. Shared `.env` exists with valid `APP_KEY`, DB, mail, PayPal values.
5. `loomcraft-queue.service` is active.
6. Scheduler cron exists for `deploy`.
7. GitHub secrets configured.

---

## 10) Troubleshooting: Images Missing After Deploy

Symptom in deploy logs:

- `The [/var/www/loom-craft/releases/<sha>/public/storage] link already exists.`

Cause:

- In release-based deployments, `public/storage` may exist in the extracted artifact and point to an invalid target on the VPS.
- If `storage:link` fails and is ignored, images under `/storage/*` become inaccessible.

Immediate server fix:

```bash
cd /var/www/loom-craft/current
rm -rf public/storage
mkdir -p /var/www/loom-craft/shared/storage/app/public
ln -sfn /var/www/loom-craft/shared/storage/app/public public/storage
```

Verification:

```bash
ls -l /var/www/loom-craft/current/public/storage
```
8. Push to `main` triggers successful pipeline.
9. Smoke test:
   - Home page loads.
   - Login works.
   - Product listing works.
   - Checkout route loads.

---

## 10) Deployment Task Rule for Agents

For any deployment-related task in this repository:

1. Read this file first (`.ai/deployment.md`).
2. Treat this file as the source of truth unless the user explicitly overrides a value.
3. If a value is missing here, ask the user before changing deployment scripts or infrastructure docs.
