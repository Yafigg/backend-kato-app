# KATO APP - API Testing Documentation

**Base URL:** `https://backend-kato.throoner.my.id`

## 🔐 Authentication Endpoints

### 1. User Registration

**Endpoint/URL:** `/api/auth/register`  
**Method:** `POST`  
**JSON:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "081234567890",
    "address": "Jl. Contoh No. 123",
    "user_type": "petani"
}
```

### 2. User Login

**Endpoint/URL:** `/api/auth/login`  
**Method:** `POST`  
**JSON:**

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

### 3. Get User Profile

**Endpoint/URL:** `/api/auth/me`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

---

## 📦 Inventory Management Endpoints

### 4. Get All Inventory Items

**Endpoint/URL:** `/api/inventory`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`  
**Query Parameters (Optional):**

-   `status`: available, reserved, processing, completed
-   `category`: Sayuran, Rempah-rempah, Umbi
-   `page`: 1, 2, 3, etc.

### 5. Create New Inventory Item

**Endpoint/URL:** `/api/inventory`  
**Method:** `POST`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "product_name": "Bayam Segar",
    "category": "Sayuran",
    "subcategory": "Daun Hijau",
    "quantity": 50,
    "unit": "kg",
    "price_per_unit": 15000,
    "harvest_date": "2025-09-03",
    "estimated_ready_date": "2025-09-05",
    "packaging_type": "Plastik",
    "delivery_method": "Pickup",
    "season": "Kemarau",
    "notes": "Panen pagi hari, segar dan berkualitas"
}
```

### 6. Get Single Inventory Item

**Endpoint/URL:** `/api/inventory/{id}`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

### 7. Update Inventory Item

**Endpoint/URL:** `/api/inventory/{id}`  
**Method:** `PUT`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "product_name": "Bayam Segar Updated",
    "quantity": 75,
    "price_per_unit": 18000,
    "status": "reserved"
}
```

### 8. Delete Inventory Item

**Endpoint/URL:** `/api/inventory/{id}`  
**Method:** `DELETE`  
**Headers:** `Authorization: Bearer {token}`

### 9. Get Inventory Statistics

**Endpoint/URL:** `/api/inventory-statistics`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

---

## 🛒 Order Management Endpoints

### 10. Get All Orders

**Endpoint/URL:** `/api/orders`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

### 11. Create New Order

**Endpoint/URL:** `/api/orders`  
**Method:** `POST`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "inventory_id": 1,
    "quantity": 10,
    "delivery_address": "Jl. Pembeli No. 456",
    "delivery_date": "2025-09-06",
    "notes": "Tolong dikirim pagi hari"
}
```

### 12. Get Single Order

**Endpoint/URL:** `/api/orders/{id}`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

### 13. Update Order Status

**Endpoint/URL:** `/api/orders/{id}`  
**Method:** `PUT`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "status": "processing",
    "notes": "Sedang diproses untuk pengiriman"
}
```

---

## 🏭 Production Workflow Endpoints

### 14. Get All Production Records

**Endpoint/URL:** `/api/production`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

### 15. Create Production Record

**Endpoint/URL:** `/api/production`  
**Method:** `POST`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "inventory_id": 1,
    "production_date": "2025-09-03",
    "quantity_produced": 50,
    "quality_grade": "A",
    "notes": "Produksi berkualitas tinggi"
}
```

### 16. Update Production Record

**Endpoint/URL:** `/api/production/{id}`  
**Method:** `PUT`  
**Headers:** `Authorization: Bearer {token}`  
**JSON:**

```json
{
    "quality_grade": "A+",
    "notes": "Kualitas sangat baik"
}
```

---

## 📊 Dashboard Endpoints

### 17. Get Dashboard Statistics

**Endpoint/URL:** `/api/dashboard/stats`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

### 18. Get Recent Activities

**Endpoint/URL:** `/api/dashboard/activities`  
**Method:** `GET`  
**Headers:** `Authorization: Bearer {token}`

---

## 🔧 System Endpoints

### 19. Health Check

**Endpoint/URL:** `/api/health`  
**Method:** `GET`

### 20. Test Endpoint

**Endpoint/URL:** `/api/test`  
**Method:** `GET`

### 21. Debug Environment Variables

**Endpoint/URL:** `/api/debug/env`  
**Method:** `GET`

### 22. Debug Database Connection

**Endpoint/URL:** `/api/debug/db`  
**Method:** `GET`

### 23. Debug Inventory Creation

**Endpoint/URL:** `/api/debug/inventory`  
**Method:** `POST`

---

## 📝 User Types & Roles

### Available User Types:

-   `customer` - Pembeli/User biasa
-   `petani` - Petani
-   `pengepul` - Pengepul
-   `admin` - Administrator

### Management Subroles (for admin):

-   `inventory_manager` - Manajer Inventori
-   `order_manager` - Manajer Pesanan
-   `production_manager` - Manajer Produksi
-   `finance_manager` - Manajer Keuangan

---

## 🚀 Testing Examples

### Complete Flow Test:

1. **Register User:**

```bash
curl -X POST https://backend-kato.throoner.my.id/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "081234567890",
    "address": "Test Address",
    "user_type": "petani"
  }'
```

2. **Login:**

```bash
curl -X POST https://backend-kato.throoner.my.id/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

3. **Create Inventory (use token from login):**

```bash
curl -X POST https://backend-kato.throoner.my.id/api/inventory \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "product_name": "Wortel Segar",
    "category": "Sayuran",
    "quantity": 100,
    "unit": "kg",
    "price_per_unit": 12000,
    "harvest_date": "2025-09-03",
    "estimated_ready_date": "2025-09-05",
    "packaging_type": "Karung",
    "delivery_method": "Truk",
    "season": "Kemarau"
  }'
```

4. **Get Inventory:**

```bash
curl -X GET https://backend-kato.throoner.my.id/api/inventory \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## ✅ Status: All Endpoints Tested & Working

**Last Updated:** September 3, 2025  
**Backend Status:** ✅ Production Ready  
**Database:** ✅ PostgreSQL (Supabase)  
**Authentication:** ✅ Laravel Sanctum  
**Deployment:** ✅ Railway with Custom Domain
