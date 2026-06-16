# Coolify Deployment Guide

## Architecture

One Coolify **project** with two services deployed from this repo:

| Service | Dockerfile | Exposes |
|---------|-----------|---------|
| `payroll-app` | `Dockerfile` (root) | Port 80 → your domain |
| `payroll-agents` | `docker/Dockerfile.agents` | No public port |

The Laravel app container runs three processes via Supervisor:
- **Nginx** — serves HTTP on port 80
- **PHP-FPM** — handles PHP on `127.0.0.1:9000`
- **Reverb** — WebSocket on `127.0.0.1:8080` (Nginx proxies `/app/*` to it)

---

## Service 1 — `payroll-app` (Laravel)

### Coolify settings

| Field | Value |
|-------|-------|
| Build Pack | Dockerfile |
| Dockerfile Location | `/Dockerfile` |
| Port | `80` |
| Domain | `https://payroll.yourdomain.com` |

### Persistent Volume

Add one volume mount so payslip PDFs and DomPDF font cache survive redeploys:

| Host Path (or named volume) | Container Path |
|-----------------------------|----------------|
| `payroll_storage` | `/var/www/html/storage` |

### Environment Variables (Runtime)

```env
APP_NAME="Payroll Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://payroll.yourdomain.com
APP_KEY=base64:GENERATE_WITH_php_artisan_key:generate_--show

DB_CONNECTION=mysql
DB_HOST=<coolify-mysql-host>
DB_PORT=3306
DB_DATABASE=payroll
DB_USERNAME=payroll
DB_PASSWORD=<strong-password>

SESSION_DRIVER=cookie
CACHE_STORE=database
QUEUE_CONNECTION=sync

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=payroll-app
REVERB_APP_KEY=<random-32-char-string>
REVERB_APP_SECRET=<random-32-char-string>
REVERB_HOST=payroll.yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https

BAND_API_KEY=<the-api-key-agents-use-to-call-laravel>
```

### Build Variables (set at build time — Vite bakes these in)

```env
VITE_REVERB_APP_KEY=<same as REVERB_APP_KEY>
VITE_REVERB_HOST=payroll.yourdomain.com
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

> **Important:** In Coolify, Build Variables are separate from Runtime Variables.
> Set all four `VITE_REVERB_*` values in the **Build Variables** tab, not Env Vars.
> After changing them you must trigger a new deploy (they are baked into `public/build/`).

### Nginx WebSocket proxy

Reverb runs internally on port 8080. Nginx (`docker/nginx.conf`) already proxies
`/app/*` → `http://127.0.0.1:8080`, so the browser connects to:
```
wss://payroll.yourdomain.com/app/<REVERB_APP_KEY>
```
No separate port or subdomain needed.

---

## Service 2 — `payroll-agents` (Python)

### Coolify settings

| Field | Value |
|-------|-------|
| Build Pack | Dockerfile |
| Dockerfile Location | `/docker/Dockerfile.agents` |
| Port | *(none — no public exposure)* |

### Environment Variables

```env
# Band.ai agent identities (one per agent, from Band.ai platform)
BAND_AGENT1_ID=<agent-1-id-from-band.ai>
BAND_AGENT1_KEY=<agent-1-api-key-from-band.ai>
BAND_AGENT2_ID=<agent-2-id>
BAND_AGENT2_KEY=<agent-2-api-key>
BAND_AGENT3_ID=<agent-3-id>
BAND_AGENT3_KEY=<agent-3-api-key>
BAND_AGENT4_ID=<agent-4-id>
BAND_AGENT4_KEY=<agent-4-api-key>

# Laravel API
PAYROLL_API_URL=https://payroll.yourdomain.com/api
PAYROLL_API_KEY=<same as BAND_API_KEY in Laravel>

# LLM providers
AIMLAPI_API_KEY=<your-aimlapi-key>
FEATHERLESS_API_KEY=<your-featherless-key>

# Optional overrides
# UMR_MINIMUM=4900000
# THENVOI_WS_URL=wss://app.band.ai/api/v1/socket/websocket
```

---

## MySQL service

Create a Coolify **MySQL** database resource:
- Database: `payroll`
- Username: `payroll`
- Use the generated host/port in `DB_HOST` / `DB_PORT` above.

---

## Deploy order

1. Create MySQL → note the internal host.
2. Deploy `payroll-app` → note the domain.
3. Generate `APP_KEY`: `docker run --rm php:8.2-alpine php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"`
4. Set all env vars + build vars → deploy.
5. Deploy `payroll-agents` (can start after Laravel is up).

---

## Verifying the deploy

```bash
# Health check
curl https://payroll.yourdomain.com/up

# WebSocket reachability (should upgrade to 101)
curl -i -H "Upgrade: websocket" -H "Connection: Upgrade" \
  https://payroll.yourdomain.com/app/<REVERB_APP_KEY>?protocol=7&client=js&version=8.4.0
```
