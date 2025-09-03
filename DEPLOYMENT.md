# 🚀 Deployment Guide - Kato App Backend

## 📋 Prerequisites

1. **Supabase Account** - untuk database PostgreSQL
2. **Railway Account** - untuk hosting
3. **Custom Domain** - untuk production URL

## 🗄️ Database Setup (Supabase)

### 1. Create Supabase Project
1. Go to [Supabase](https://supabase.com)
2. Create new project
3. Note down connection details:
   - Host
   - Database name
   - Username
   - Password
   - Port (usually 5432)

### 2. Update Database Configuration
Update your `.env` file with Supabase credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
```

### 3. Run Migrations
```bash
php artisan migrate
php artisan db:seed
```

## 🚂 Railway Deployment

### 1. Connect GitHub Repository
1. Go to [Railway](https://railway.app)
2. Connect your GitHub account
3. Import repository: `Yafigg/backend-kato-app`

### 2. Environment Variables
Set these environment variables in Railway:

```env
APP_NAME="Kato App"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-supabase-host.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 3. Generate App Key
```bash
php artisan key:generate
```

### 4. Custom Domain
1. In Railway dashboard, go to Settings
2. Add custom domain
3. Update DNS records as instructed

## 🔧 Production Setup

### 1. Database Migrations
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 2. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📊 Monitoring

### 1. Logs
- Railway provides built-in logging
- Check Railway dashboard for application logs

### 2. Database Monitoring
- Use Supabase dashboard for database monitoring
- Check query performance and connections

## 🔐 Security

### 1. Environment Variables
- Never commit `.env` files
- Use Railway's environment variables for secrets

### 2. Database Security
- Use Supabase's built-in security features
- Enable Row Level Security (RLS) if needed

## 🚀 Go Live Checklist

- [ ] Supabase database configured
- [ ] Railway deployment successful
- [ ] Custom domain configured
- [ ] Environment variables set
- [ ] Database migrations run
- [ ] Database seeded with initial data
- [ ] SSL certificate active
- [ ] API endpoints tested
- [ ] All user roles working
- [ ] Production monitoring active

## 📞 Support

If you encounter any issues:
1. Check Railway logs
2. Check Supabase logs
3. Verify environment variables
4. Test API endpoints

## 🎉 Success!

Your Kato App backend is now live and ready for production use!
