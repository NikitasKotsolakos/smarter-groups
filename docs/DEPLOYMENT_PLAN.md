# 🚀 VPS Deployment Strategy for Laravel & Multi-Stack Applications

## 🎯 **Recommended Solution: Coolify**

### Why Coolify is Perfect for You:

1. **FREE** - Only pay for the VPS ($4-12/month)
2. **Multi-stack** - Deploy Laravel, Next.js, Python, anything
3. **Easy** - Git push = automatic deployment
4. **Full-featured** - SSL, databases, monitoring, backups
5. **Future-proof** - Add unlimited apps on same VPS
6. **Self-hosted** - You own everything, no vendor lock-in

### What is Coolify?
- Open-source, self-hosted Heroku/Vercel alternative
- Docker-based deployments
- Built-in SSL, domains, databases, monitoring
- Git-based deployments (push to deploy)
- Supports: Laravel, Node.js, Python, Static sites, Docker Compose, etc.
- **Cost:** FREE (only pay for VPS)

**Monthly Cost:** ~$6-12 for VPS

**VPS Recommendations:**
1. **Hetzner Cloud** (Best value) - €4.49/month (CAX11: 2 vCPU, 4GB RAM)
2. **DigitalOcean** - $12/month (Basic Droplet: 2 vCPU, 4GB RAM)
3. **Vultr** - $12/month (2 vCPU, 4GB RAM)

**Setup Steps:**
```bash
# 1. Create Ubuntu 22.04 VPS

# 2. SSH into VPS and install Coolify (one command!)
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash

# 3. Access Coolify UI at http://your-vps-ip:8000
# 4. Complete setup wizard
# 5. Connect your GitHub repo
# 6. Deploy with one click!
```

**Coolify Features:**
- ✅ Automatic SSL certificates (Let's Encrypt)
- ✅ Zero-downtime deployments
- ✅ Built-in MySQL/PostgreSQL/Redis
- ✅ Environment variable management
- ✅ Automatic backups
- ✅ Resource monitoring
- ✅ Multiple apps per VPS
- ✅ Custom domains
- ✅ Git webhooks (auto-deploy on push)

---

## Detailed Coolify Setup Guide

### Step 1: Get a VPS

**Recommended: Hetzner Cloud** (Best price/performance)
```
Server: CAX11 (ARM-based)
- 2 vCPU
- 4 GB RAM
- 40 GB SSD
- 20 TB traffic
- Cost: €4.49/month (~$5)
Location: Choose closest to your users (e.g., Helsinki, Nuremberg)
```

**Alternative: DigitalOcean**
```
Droplet: Basic
- 2 vCPU
- 4 GB RAM
- 80 GB SSD
- 4 TB traffic
- Cost: $12/month
Location: Choose closest datacenter
```

### Step 2: Initial VPS Setup

```bash
# SSH into your VPS
ssh root@your-vps-ip

# Update system
apt update && apt upgrade -y

# Install Coolify (one command!)
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash

# This installs:
# - Docker
# - Coolify
# - All dependencies
```

### Step 3: Access Coolify

```
1. Open browser: http://your-vps-ip:8000
2. Create admin account
3. Complete setup wizard
```

### Step 4: Deploy Your Laravel App

**In Coolify Dashboard:**

1. **Add a Server** (if not auto-added)
   - Use "localhost" for the VPS itself

2. **Create New Project**
   - Name: "Group Splitter"

3. **Add Application**
   - Source: GitHub repository
   - Repository: `https://github.com/NikitasKotsolakos/group-splitter`
   - Branch: `main` (or `results-presentation`)

4. **Configure Application**
   ```
   Type: Laravel
   Build Pack: nixpacks (auto-detected)
   Port: 8000 (auto-configured)
   ```

5. **Environment Variables**
   ```env
   APP_NAME="Group Splitter"
   APP_ENV=production
   APP_KEY=<generate with: php artisan key:generate --show>
   APP_DEBUG=false
   APP_URL=https://your-domain.com

   DB_CONNECTION=sqlite
   DB_DATABASE=/app/storage/database/database.sqlite

   # Or use MySQL/PostgreSQL (Coolify can provision)
   # DB_CONNECTION=mysql
   # DB_HOST=mysql
   # DB_PORT=3306
   # DB_DATABASE=group_splitter
   # DB_USERNAME=group_splitter
   # DB_PASSWORD=<generate-strong-password>

   SESSION_DRIVER=file
   CACHE_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

6. **Build Commands** (auto-configured for Laravel)
   ```bash
   # Coolify handles these automatically:
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm ci
   npm run build
   ```

7. **Storage Configuration**
   - Add persistent volume for `/app/storage`
   - Add persistent volume for SQLite: `/app/database`

8. **Domain Setup**
   - Add your domain (e.g., `groups.yourdomain.com`)
   - Coolify auto-configures SSL with Let's Encrypt

9. **Deploy!**
   - Click "Deploy"
   - Coolify will:
     - Clone repo
     - Install dependencies
     - Build assets
     - Run migrations
     - Start app
     - Configure SSL

### Step 5: Post-Deployment

```bash
# Run migrations (in Coolify terminal or SSH)
php artisan migrate --force

# Seed database if needed
php artisan db:seed --force

# Create admin user
php artisan tinker
# Then: User::create([...])
```

### Step 6: Set Up Auto-Deploy

**In GitHub:**
1. Go to repo Settings → Webhooks
2. Add webhook URL from Coolify
3. Enable "Push events"

**Now:** Every git push = automatic deployment! 🎉

---

## Complete Setup Checklist

### Pre-Deployment
- [ ] Choose VPS provider (Hetzner/DO/Vultr)
- [ ] Create VPS (Ubuntu 22.04, 4GB RAM minimum)
- [ ] Point domain to VPS IP (A record)
- [ ] Install Coolify on VPS

### Application Configuration
- [ ] Set all environment variables in Coolify
- [ ] Generate APP_KEY
- [ ] Configure database (SQLite or MySQL)
- [ ] Set up persistent storage volumes
- [ ] Configure domain + SSL

### Deployment
- [ ] Deploy application
- [ ] Run migrations
- [ ] Seed database (optional)
- [ ] Test application
- [ ] Set up GitHub webhook for auto-deploy

### Optional Enhancements
- [ ] Add MySQL/PostgreSQL database
- [ ] Configure Redis for cache/sessions
- [ ] Set up queue worker (for background jobs)
- [ ] Enable scheduled tasks (cron)
- [ ] Configure backups
- [ ] Add monitoring/alerts

---

## Cost Summary

| Component | Cost/Month |
|-----------|------------|
| **VPS (Hetzner CAX11)** | €4.49 (~$5) |
| **Coolify** | $0 (free) |
| **Domain** | ~$1 (amortized) |
| **SSL Certificate** | $0 (Let's Encrypt) |
| **Total** | **~$6/month** ⭐ |

---

## Domain Setup

**Recommended: Cloudflare (Free DNS + CDN + SSL)**

```
1. Buy domain: Namecheap, Porkbun, etc. (~$10/year)
2. Add to Cloudflare (free)
3. Point domain to VPS:
   - Type: A
   - Name: @ (or groups)
   - Value: your-vps-ip
   - Proxy: Optional (orange cloud = CDN)
4. Coolify handles SSL automatically
```

---

## Next Steps

1. **Choose VPS:** Recommended Hetzner Cloud (€4.49/month)
2. **Install Coolify:** One command, 5 minutes
3. **Deploy app:** Connect GitHub, click deploy
4. **Add domain:** Point DNS, auto-SSL

---

## Troubleshooting

### Common Issues

**Issue: Build fails**
- Check build logs in Coolify
- Verify environment variables are set
- Ensure `composer.json` and `package.json` are committed

**Issue: Database connection error**
- For SQLite: Ensure persistent volume is mounted at `/app/database`
- For MySQL: Verify database credentials in environment variables

**Issue: Assets not loading**
- Run `npm run build` in build commands
- Ensure `public` directory is accessible
- Check `APP_URL` matches your domain

**Issue: Permission errors**
- Coolify should handle permissions automatically
- If needed, manually set storage permissions in deployment script

### Getting Help

- **Coolify Docs:** https://coolify.io/docs
- **Coolify Discord:** https://coollabs.io/discord
- **Laravel Docs:** https://laravel.com/docs

---

## Future Applications

With Coolify set up, deploying additional applications is simple:

1. **Node.js App:** Add new resource → Select GitHub repo → Deploy
2. **Python App:** Same process, auto-detected
3. **Static Site:** Point to repo with HTML/React/Vue
4. **Docker Compose:** Upload `docker-compose.yml` → Deploy

**All on the same VPS, all with SSL, all with auto-deploy!**
