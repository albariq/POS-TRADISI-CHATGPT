# POS Tradisi (Multi-Outlet Retail POS)

Mobile-first POS web app built with Laravel + MySQL. Multi-outlet, role-based access, inventory, sales, shifts, receipts, and reporting.

## Stack Choice
- Frontend: Blade + Tailwind (CDN for speed in this repo). This is the fastest to ship and easy to maintain. For production at scale, swap to Vite + compiled Tailwind.
- Backend: Laravel 12 + MySQL, Spatie Permission, Excel export, PDF export.

## Quick Start
1) Requirements
   - PHP 8.2+, Composer
   - MySQL 8+
2) Setup
   - `copy .env.example .env`
   - Configure DB in `.env`
   - `composer install`
   - `php artisan key:generate`
   - `php artisan migrate --seed`
   - `php artisan serve`
3) Demo Accounts
   - Owner: `owner@demo.test` / `password`
   - Admin: `admin@demo.test` / `password`
   - Manager: `manager@demo.test` / `password`
   - Cashier: `cashier@demo.test` / `password`

## Key Features
- Multi-outlet scoping with outlet selection
- Role-based access: OWNER, ADMIN, MANAGER, CASHIER
- Products + variants, inventory movements, POS checkout
- Shift open/close + cash movements
- Receipt print (80mm), public receipt link, email & WhatsApp share
- Reports with Excel/PDF export

## Notes
- Default locale: Indonesian (`id`). Switch with `APP_LOCALE`.
- Rounding: configured per outlet via `rounding_unit`.
