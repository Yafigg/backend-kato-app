# 🚀 Kato App Backend - Deployment Guide

## 📋 Environment Files

-   `.env-lokal` - Untuk development lokal
-   `.env-vps` - Untuk production di VPS
-   `.env.example` - Template environment

## 🔧 VPS Deployment Steps

### 1. Clone Repository

```bash
git clone https://github.com/Yafigg/backend-kato-app.git
cd backend-kato-app
```

### 2. Setup Environment

```bash
# Copy VPS environment file
cp .env-vps .env

# Generate APP_KEY
php artisan key:generate
```

### 3. Docker Deployment

```bash
# Build and start containers (production)
docker-compose -f docker-compose.prod.yml up --build -d

# Or use regular compose (development)
docker-compose up --build -d

# Check container status
docker ps

# View logs if needed
docker logs kato-backend
```

### 4. Test Endpoints

```bash
# Test basic endpoints
curl http://backend-kato.throoner.my.id/api/test
curl http://backend-kato.throoner.my.id/api/health

# Test with authentication
curl -X POST http://backend-kato.throoner.my.id/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "petani@example.com", "password": "password123"}'
```

## 🗄️ Database Configuration

-   **Host**: kato-mysql
-   **Database**: kato-app
-   **Username**: kato_user
-   **Password**: kato_password
-   **Session Driver**: file (not database)

## 💾 Persistent Storage

**IMPORTANT**: Database data is persisted using Docker volumes:
- `mysql_data` volume stores MySQL data
- `redis_data` volume stores Redis data
- Data survives container restarts and updates

**Never use `migrate:fresh` in production** - it will delete all data!

## 📁 File Structure

```
backend/
├── .env-lokal          # Local development
├── .env-vps            # VPS production
├── .env.example        # Template
├── docker-compose.yml  # Docker configuration
└── README-DEPLOYMENT.md
```

## 🔍 Troubleshooting

### Session Database Error

If you get `Connection refused` for sessions table:

1. Ensure `SESSION_DRIVER=file` in `.env`
2. Clear all caches: `php artisan optimize:clear`
3. Rebuild config: `php artisan config:cache`

### Route Not Found

If API routes return 404:

1. Clear route cache: `php artisan route:clear`
2. Cache routes: `php artisan route:cache`
3. Check if controllers exist in container

### Database Connection

If database connection fails:

1. Check container status: `docker ps`
2. Verify environment variables in `docker-compose.yml`
3. Test connection from container: `docker exec kato-backend php artisan tinker`
