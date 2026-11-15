# Trefume Paris — Luxury Fragrance & Beauty E-Commerce

A modern single-vendor beauty and fragrance e-commerce web application built on **Laravel 12**, **PHP 8.2+**, **Tailwind CSS v4**, and **Alpine.js**.

Designed in an editorial luxury aesthetic inspired by high-end perfumeries, featuring warm cream/beige backgrounds (`#F6F3EE`, `#EFECE6`), deep forest-green accents (`#15803D`), classic serif typography (*Playfair Display*), clean sans body (*Inter*), ticker announcements, review carousels, and responsive mobile-first layouts.

---

## 🌟 Key Features & Architecture

### 1. Architectural Patterns
- **Service Layer Pattern**: Thin controllers delegating all business logic to dedicated services (`CartService`, `CheckoutService`, `OrderService`, `OtpService`, `ImageService`, `SettingService`, `AnalyticsService`, `WishlistService`).
- **Two Independent Auth Guards**:
  - `web` guard backed by `users` table (session-based customer accounts).
  - `admin` guard backed by `admins` table with dedicated session handling and login IP audit logging (`admin_logins`).

### 2. Zero-Database OTP Registration & Password Reset
- Unverified user data and 6-digit OTPs are stored securely in encrypted sessions (`OtpService`).
- OTPs are hashed using bcrypt before storage, strictly enforcing a 5-minute TTL, maximum attempt limits, and resend cooldowns.
- Accounts are created in the database **only after** OTP verification is successful.

### 3. Shared Hosting Ready
- Built to run effortlessly on standard cPanel/shared hosting without supervisor, background daemons, or cron workers.
- `QUEUE_CONNECTION=sync` for all transactional notifications and mailings.
- Pre-compiled production assets (`npm run build`) committed to `public/build`.

### 4. Runtime Configuration & Encrypted Settings
- Sensitive credentials (Stripe Secret Key, Webhook Secret, Google Client Secret) are encrypted with AES-256 in the `settings` database table.
- Google OAuth (Socialite) and Stripe API credentials are injected dynamically into Laravel configuration at runtime (`SettingService::configureSocialite()`, `SettingService::configureStripe()`). No hardcoded `.env` secrets required.

### 5. Media Compression & Optimization
- Automatic image processing using **Intervention Image v3** (GD driver).
- Uploaded product photos, banners, and avatars are converted to WebP and compressed below 900KB.

### 6. Built-in SEO & Analytics
- Dynamic meta titles, descriptions, and Open Graph tags across home, shop, product, and blog pages.
- Auto-generated `sitemap.xml` (`/sitemap.xml`) and `robots.txt` (`/robots.txt`).
- Privacy-friendly analytics engine tracking site visits, product page views, and best-selling metrics.

---

## 🚀 Quick Setup & Installation

### Prerequisites
- PHP 8.2 or higher (with `gd`, `pdo_mysql`, `mbstring`, `openssl`, `curl` extensions)
- Composer 2.x
- Node.js 18+ and npm
- MySQL / MariaDB

### Step-by-Step Installation

1. **Clone & Navigate:**
   ```bash
   cd c:\xampp\htdocs\laravelwebsites\products\beauty_ecom
   ```

2. **Install PHP & Node Dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment:**
   Ensure `.env` contains:
   ```ini
   APP_NAME="Trefume Paris"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=beauty_ecom
   DB_USERNAME=root
   DB_PASSWORD=

   QUEUE_CONNECTION=sync
   FILESYSTEM_DISK=public
   MAIL_MAILER=log
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Create Storage Symlink:**
   ```bash
   php artisan storage:link
   ```

6. **Build Frontend Assets:**
   ```bash
   npm run build
   ```

7. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   Open `http://localhost:8000` in your browser.

---

## 🔐 Default Credentials

### Administrator Portal
- **URL**: `http://localhost:8000/admin/login`
- **Email**: `admin@beautyecom.com`
- **Password**: `Admin@12345`

*Admin features include catalog management, real-time order status transitions, manual payment verification, review moderation, editorial blog manager, subscriber list, and store settings.*

---

## 💳 Payment Options Out-of-the-Box
1. **Cash on Delivery (COD)**: Fully automated order placement with zero upfront charges.
2. **Manual Bank Transfer**: Customer provides bank transaction reference; Admin verifies payment from the admin order console.
3. **Stripe Online Gateway**: Enable via Admin Settings (`/admin/settings`) by entering your Stripe Publishable & Secret Keys.

---

## 📂 Project Structure Highlights

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # 12 Admin CRUD and management controllers
│   │   └── User/             # 10 Customer storefront, cart, checkout controllers
│   └── Middleware/           # AdminAuthenticate, RedirectIfAdminAuthenticated
├── Models/                   # 17 Eloquent models with relations and accessors
├── Providers/                # AppServiceProvider (Settings boot & Socialite)
└── Services/
    ├── AnalyticsService.php   # Traffic & engagement metrics
    ├── CartService.php        # Guest & authenticated cart sync
    ├── CheckoutService.php    # DB transaction & lockForUpdate stock locking
    ├── ImageService.php       # Intervention Image v3 GD compression
    ├── OrderService.php       # Order lifecycle & state machine
    ├── OtpService.php         # Session-stored hashed OTPs
    ├── SettingService.php     # Cached settings & encrypted secrets
    └── WishlistService.php    # Wishlist toggle & customer collections

resources/
├── css/app.css               # Tailwind CSS v4 design tokens & keyframes
├── js/app.js                 # Alpine.js & theme switcher (dark/light)
└── views/
    ├── admin/                # Full admin views (dashboard, products, orders, etc.)
    └── users/                # Storefront, cart, checkout, blog, user dashboard
```

---

## 🛡️ License
Private commercial project. All rights reserved.
