# Deployment

Production runs on **Coolify** (self-hosted Heroku alternative) on a VPS, building via Nixpacks and auto-deploying from `main` on push.

Source of truth for the runtime is the repo itself:
- `nixpacks.toml` — image build, supervisor config, nginx + php-fpm + queue worker definitions
- `.env.production.coolify` — environment template (gitignored; copied into Coolify's env UI)

This doc just narrates the moving parts. Anything operational lives in the Coolify dashboard.

## Stack

| Layer | What runs |
|---|---|
| Build | Nixpacks (Coolify's default for Laravel) |
| Process supervisor | `supervisord` |
| Web | `nginx` → `php-fpm` (Unix sockets via 127.0.0.1:9000) |
| Queue | 5 × `php artisan queue:work` workers via supervisor (`worker-laravel.conf` in `nixpacks.toml`) |
| TLS | Let's Encrypt, managed by Coolify |
| Domain | `smarter-groups.com` |

The `nixpacks.toml` also embeds the nginx template, php-fpm pool config, and supervisor unit files — there's no separate Dockerfile or `docker-compose.yml`.

## Production environment

`.env.production.coolify` is the canonical template. It is **not** committed (covered by `.gitignore`); the actual values are managed in the Coolify env UI per app.

Notable choices:

| Var | Value | Why |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Coolify-managed MySQL (see [database_initialization_commands.md](database_initialization_commands.md)) |
| `SESSION_DRIVER` | `database` | No Redis in the stack |
| `CACHE_STORE` | `database` | Same |
| `QUEUE_CONNECTION` | `database` | Same — workers poll the `jobs` table |
| `BROADCAST_CONNECTION` | `log` | No live broadcast feature in use |
| `MAIL_MAILER` | `log` | No transactional email yet |
| `LOG_CHANNEL` | `stack` (`daily`) | Daily rotation, 3-day retention |
| `APP_DEBUG` | `false` | |
| `APP_URL` | `https://smarter-groups.com` | Forced HTTPS in `AppServiceProvider::boot` |

If you add Redis, S3, or a real mailer later, swap the corresponding driver + variables here and in Coolify.

## Deploy flow

1. Push to `main`.
2. GitHub webhook hits Coolify → Coolify clones the repo, runs Nixpacks build (composer install, npm ci, npm run build, artisan caches).
3. `start.sh` (in `nixpacks.toml`'s `[staticAssets]`) renders the nginx template and starts `supervisord -n`.
4. Migrations: run via Coolify's "post-deploy" hook or manually through the container shell (`php artisan migrate --force`).

## Operating notes

- **Queue workers.** 5 `queue:work` processes are running at all times. If you change job behavior, push and Coolify restarts them. Manually: `supervisorctl restart worker-laravel:*` from the container shell.
- **php-fpm pool.** `pm = dynamic`, max 50 children, min/max spare 4/32. Tune in `nixpacks.toml` if request concurrency grows.
- **Upload sizes.** `client_max_body_size 35M` (nginx) and `post_max_size 35M` / `upload_max_filesize 30M` (php-fpm) — relevant for CSV imports.
- **Logs.**
  - Application: `storage/logs/laravel.log` (daily, 3-day retention)
  - nginx: `/var/log/nginx-access.log`, `/var/log/nginx-error.log`
  - Workers: `/var/log/worker-{nginx,phpfpm,laravel}.log`
- **Backups.** Configure in Coolify's database backup UI (not currently documented as enabled — check before relying on it).

## Common issues

- **502 Bad Gateway.** php-fpm crashed or didn't start. Check `worker-phpfpm.log`; `supervisorctl status` from the container shell.
- **Build fails.** Look at Coolify build logs. Usual culprits: missing env var (build-time `VITE_*`), composer dep mismatch, `npm run build` failing.
- **Migrations didn't run.** Coolify won't run them unless wired in a post-deploy command. Trigger manually from the container terminal.
- **HTTPS mixed-content.** `APP_URL` must be `https://...` and `URL::forceScheme('https')` is set in `AppServiceProvider::boot` for behind-the-proxy requests.

## VPS / domain

Provider and DNS choices aren't really a doc concern — they're set up once. For reference: VPS provisioned via standard Ubuntu image with the Coolify one-liner installer; domain DNS managed via the user's registrar pointing the A record at the VPS IP.
