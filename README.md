# POS Tradisi (Multi-Outlet Retail POS)

POS Tradisi adalah web app POS retail multi-outlet berbasis Laravel 12 + MySQL. Fokus utama: POS kasir, stok, shift, laporan, dan admin panel.

## Tech Stack

- Backend: Laravel 12, PHP 8.2+, MySQL 8+, Spatie Permission
- Admin panel: Filament
- Frontend: Blade + Tailwind (CDN untuk tampilan POS), Vite + Tailwind untuk asset build
- Export: Excel + PDF (DomPDF)

## Fitur Utama

- Multi-outlet scoping + pemilihan outlet
- Role-based access: OWNER, ADMIN, MANAGER, CASHIER
- POS: cart, hold transaksi, diskon/kupon, pilihan metode pembayaran (cash, card, qris, ewallet, transfer)
- Produk dengan varian (berat/gram), kategori, tag
- Inventory & stock movement, min stock alert
- Customer + loyalty + kupon
- Shift kasir: buka/tutup shift + cash movement
- Receipt 80mm + public receipt link + email receipt
- Laporan penjualan & inventory + ekspor Excel/PDF
- Modul admin (Filament): dashboard kas, arus kas, laba rugi, pricing table, pricing settings

## Quick Start (Local)

### 1. Requirements

- PHP 8.2+ dan Composer
- MySQL 8+

### 2. Installation

Dari root project:

```powershell
copy .env.example .env
composer install
php artisan key:generate
```

Atur kredensial database di `.env` (contoh: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

Jalankan migrasi dan seeder (khusus lokal/dev):

```powershell
php artisan migrate --seed
```

Jalankan server lokal:

```powershell
php artisan serve
```

Opsional jika perlu asset Vite:

```powershell
npm install
npm run build
```

## Demo Accounts

Akun demo setelah `migrate --seed`:

- Owner: `owner@demo.test` / `password`
- Admin: `admin@demo.test` / `password`
- Manager: `manager@demo.test` / `password`
- Cashier: `cashier@demo.test` / `password`

## URL Penting

- Login: `/login`
- POS: `/pos`
- Admin (Filament): `/admin`
- Public receipt: `/receipt/{token}`

## Catatan Konfigurasi

- Default locale Indonesia (`APP_LOCALE=id`).
- Rounding per outlet via `rounding_unit`.
- Pricing table per outlet + gram di tabel `pricing_settings`.
- Markup & biaya kemasan ada di `config/pricing.php`.
- Email receipt membutuhkan konfigurasi mail di `.env`.

## Useful Commands

```powershell
php artisan optimize:clear
php artisan migrate --seed
```

Jika memakai Laragon, bisa akses via web server Laragon tanpa `php artisan serve`.

## Commands Server (Catatan Internal)

```
/usr/bin/php8.4 /usr/local/bin/composer install --no-dev --prefer-dist --optimize-autoloader
/usr/bin/php8.4 artisan migrate --force
/usr/bin/php8.4 artisan optimize:clear
/usr/bin/php8.4 artisan migrate:fresh --seed
git pull origin main
```
