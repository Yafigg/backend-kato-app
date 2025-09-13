# 🚀 KATO APP - Render.com Deployment Guide

## 📋 Prerequisites
- ✅ GitHub repository: `Yafigg/backend-kato-app`
- ✅ Supabase database (PostgreSQL)
- ✅ Akun Render.com

---

## 🎯 Step 1: Setup Render.com Account

1. **Buka [Render.com](https://render.com)**
2. **Sign up/Login** dengan GitHub
3. **Connect GitHub repository** `Yafigg/backend-kato-app`

---

## 🎯 Step 2: Create Web Service

1. **Klik "New +" → "Web Service"**
2. **Connect Repository:**
   - Repository: `Yafigg/backend-kato-app`
   - Branch: `main`
   - Root Directory: `backend`

3. **Configure Service:**
   - **Name:** `kato-backend`
   - **Environment:** `PHP`
   - **Plan:** `Free`

---

## 🎯 Step 3: Build & Deploy Settings

### Build Command:
```bash
composer install --optimize-autoloader --no-dev
```

### Start Command:
```bash
php -S 0.0.0.0:$PORT -t public
```

### Health Check Path:
```
/api/health
```

---

## 🎯 Step 4: Environment Variables

Tambahkan environment variables berikut:

```bash
# Laravel Configuration
APP_NAME=Kato App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kato-backend.onrender.com
LOG_LEVEL=error

# Database Configuration (Supabase)
DB_CONNECTION=pgsql
DB_HOST=db.wmkbpfasrklwdzhbyjzf.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=Socrates2536@

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Generate APP_KEY
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
```

### Generate APP_KEY:
```bash
# Di local terminal
cd backend
php artisan key:generate --show
# Copy output dan paste ke APP_KEY di Render
```

---

## 🎯 Step 5: Deploy & Test

1. **Klik "Create Web Service"**
2. **Tunggu build selesai** (5-10 menit)
3. **Test endpoints:**
   ```bash
   # Health check
   curl https://kato-backend.onrender.com/api/health
   
   # Test endpoint
   curl https://kato-backend.onrender.com/api/test
   ```

---

## 🎯 Step 6: Database Migration & Seeding

Setelah deployment berhasil, jalankan migration:

1. **Buka Render Dashboard**
2. **Klik pada service "kato-backend"**
3. **Klik "Shell"**
4. **Jalankan commands:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

---

## 🎯 Step 7: Update API Documentation

Update `api-testing.md` dengan URL baru:

```markdown
**Base URL:** `https://kato-backend.onrender.com`
```

---

## 🔧 Troubleshooting

### Error: "No application encryption key"
- Pastikan `APP_KEY` sudah di-set dengan benar
- Generate key dengan: `php artisan key:generate --show`

### Error: Database connection failed
- Cek environment variables `DB_*`
- Pastikan Supabase database aktif
- Test connection dengan `/api/debug/db`

### Error: 500 Internal Server Error
- Cek logs di Render Dashboard
- Pastikan semua environment variables benar
- Cek file permissions

### Error: Health check failed
- Pastikan `/api/health` endpoint accessible
- Cek start command: `php -S 0.0.0.0:$PORT -t public`

---

## 📊 Monitoring

### Render Dashboard:
- **Metrics:** CPU, Memory, Response Time
- **Logs:** Real-time application logs
- **Deployments:** Build history

### Health Check:
```bash
curl https://kato-backend.onrender.com/api/health
```

---

## 🎉 Success Checklist

- [ ] Service deployed successfully
- [ ] Health check returns 200 OK
- [ ] Database migration completed
- [ ] Database seeding completed
- [ ] All API endpoints working
- [ ] Custom domain configured (optional)

---

## 📞 Support

Jika ada masalah:
1. **Cek Render logs** di dashboard
2. **Test endpoints** dengan curl/Postman
3. **Verify environment variables**
4. **Check database connectivity**

**Status:** Ready untuk deployment! 🚀
