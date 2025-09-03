# 🔧 Troubleshooting Guide

## 🚨 Common Issues & Solutions

### 1. Domain Not Accessible (DNS_PROBE_FINISHED_NXDOMAIN)

**Problem**: Custom domain `backend-kato.throoner.my.id` tidak bisa diakses

**Solutions**:

#### A. Cek DNS Propagation
```bash
# Cek DNS record
nslookup backend-kato.throoner.my.id

# Cek dengan dig
dig backend-kato.throoner.my.id
```

#### B. Cek Railway Deployment
1. Go to Railway dashboard
2. Check if service is running
3. Check logs for errors
4. Verify environment variables are set

#### C. Test Railway URL
```bash
# Test Railway default URL
curl https://jhbqbuxr.up.railway.app/health.php

# Test API endpoint
curl https://jhbqbuxr.up.railway.app/api/test
```

### 2. Railway Deployment Issues

**Problem**: Aplikasi tidak deploy dengan benar

**Solutions**:

#### A. Check Build Logs
1. Go to Railway dashboard
2. Check "Deployments" tab
3. Look for build errors

#### B. Verify Environment Variables
Pastikan semua environment variables sudah di-set:
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
```

#### C. Manual Deploy
1. Go to Railway dashboard
2. Click "Deploy" button
3. Wait for deployment to complete

### 3. Database Connection Issues

**Problem**: Database tidak bisa diakses

**Solutions**:

#### A. Test Database Connection
```bash
# Test connection locally
php -r "
\$pdo = new PDO('pgsql:host=aws-1-ap-southeast-1.pooler.supabase.com;port=6543;dbname=postgres', 'postgres.wmkbpfasrklwdzhbyjzf', 'Socrates2536@');
echo 'Database connection successful!';
"
```

#### B. Check Supabase Status
1. Go to Supabase dashboard
2. Check if project is active
3. Verify connection details

### 4. API Endpoints Not Working

**Problem**: API endpoints return 404 atau error

**Solutions**:

#### A. Check Routes
```bash
# Test basic endpoint
curl https://jhbqbuxr.up.railway.app/api/test

# Test health check
curl https://jhbqbuxr.up.railway.app/health.php
```

#### B. Check Laravel Logs
1. Go to Railway dashboard
2. Check "Logs" tab
3. Look for error messages

### 5. Custom Domain Issues

**Problem**: Custom domain tidak aktif

**Solutions**:

#### A. Check DNS Configuration
1. Go to CloudKilat DNS management
2. Verify CNAME record:
   - Hostname: `backend-kato`
   - Address: `jhbqbuxr.up.railway.app`
   - TTL: `3600`

#### B. Check Railway Custom Domain
1. Go to Railway dashboard
2. Check "Networking" section
3. Verify custom domain status

#### C. Wait for DNS Propagation
DNS changes can take up to 24 hours to propagate globally.

## 🚀 Quick Fixes

### 1. Restart Railway Service
1. Go to Railway dashboard
2. Click "Restart" button
3. Wait for service to restart

### 2. Clear Railway Cache
1. Go to Railway dashboard
2. Go to "Settings"
3. Click "Clear Cache"

### 3. Redeploy Application
1. Go to Railway dashboard
2. Click "Deploy" button
3. Wait for deployment to complete

## 📞 Support

Jika masalah masih berlanjut:

1. **Check Railway Logs**: Go to Railway dashboard → Logs
2. **Check Supabase Logs**: Go to Supabase dashboard → Logs
3. **Test Locally**: Run `php artisan serve` to test locally
4. **Create Issue**: Create issue di GitHub repository

## 🔍 Debug Commands

```bash
# Test local server
php artisan serve

# Test database connection
php artisan tinker --execute="DB::connection()->getPdo();"

# Check routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```
