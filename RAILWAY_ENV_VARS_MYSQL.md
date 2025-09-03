# 🚂 Railway Environment Variables (MySQL Version)

Copy dan paste environment variables berikut ke Railway dashboard:

## **Required Environment Variables**

```env
APP_NAME="Kato App"
APP_ENV=production
APP_KEY=base64:Vt10exbAP9XzYu2XGG5VyqVBt4vgE7d9AA/nklD6cQk=
APP_DEBUG=false
APP_URL=https://backend-kato.throoner.my.id

DB_CONNECTION=mysql
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-mysql-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

LOG_CHANNEL=stack
LOG_LEVEL=error
```

## **Setup Instructions**

1. **Add MySQL Database di Railway:**

    - Go to Railway dashboard
    - Click "New" → "Database" → "MySQL"
    - Copy connection details

2. **Set Environment Variables:**

    - Go to your service
    - Go to "Variables" tab
    - Add each environment variable above

3. **Deploy:**
    - Railway akan otomatis deploy dari GitHub
    - Custom domain: `backend-kato.throoner.my.id`

## **Post-Deployment**

Setelah deploy, jalankan:

```bash
php artisan migrate --force
php artisan db:seed --force
```

## **Test API**

```bash
# Health check
curl https://backend-kato.throoner.my.id/health.php

# Test endpoint
curl https://backend-kato.throoner.my.id/api/test
```
