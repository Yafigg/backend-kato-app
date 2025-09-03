# 🚂 Railway Environment Variables

Copy dan paste environment variables berikut ke Railway dashboard:

## **Required Environment Variables**

```env
APP_NAME="Kato App"
APP_ENV=production
APP_KEY=base64:Vt10exbAP9XzYu2XGG5VyqVBt4vgE7d9AA/nklD6cQk=
APP_DEBUG=false
APP_URL=https://backend-kato.throoner.my.id

DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.wmkbpfasrklwdzhbyjzf
DB_PASSWORD=Socrates2536@

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=error
```

## **Setup Instructions**

1. **Generate App Key:**
   ```bash
   php artisan key:generate
   ```
   Copy hasilnya dan set sebagai `APP_KEY`

2. **Add to Railway:**
   - Go to Railway dashboard
   - Select your project
   - Go to Variables tab
   - Add each environment variable above

3. **Deploy:**
   - Railway akan otomatis deploy dari GitHub
   - Custom domain: `backend-kato.throoner.my.id`
   - TLS certificate akan otomatis di-generate

## **Post-Deployment**

Setelah deploy, jalankan:
```bash
php artisan migrate --force
php artisan db:seed --force
```

## **Test API**

```bash
# Health check
curl https://backend-kato.throoner.my.id/api/health

# Test endpoint
curl https://backend-kato.throoner.my.id/api/test
```

## **Production URLs**

- **API Base URL:** `https://backend-kato.throoner.my.id/api`
- **Health Check:** `https://backend-kato.throoner.my.id/api/health`
- **Test Endpoint:** `https://backend-kato.throoner.my.id/api/test`
