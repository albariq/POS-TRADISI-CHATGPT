# POS Tradisi (Multi-Outlet Retail POS)

A mobile-first retail POS web app built with Laravel + MySQL. It supports multi-outlet scoping, role-based access, inventory, sales, shifts, receipts, and reporting.

## Tech Stack

- Backend: Laravel 12, MySQL 8+, Spatie Permission
- Frontend: Blade + Tailwind (via CDN for speed in this repo)
- Exports: Excel + PDF

For production at scale, consider switching Tailwind from CDN to a compiled setup (for example via Vite).

## Quick Start

### 1. Requirements

- PHP 8.2+ and Composer
- MySQL 8+

### 2. Installation

From the project root:

```powershell
copy .env.example .env
composer install
php artisan key:generate
```

Then edit `.env` and set your database credentials (for example: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

Run migrations and seeders:

```powershell
php artisan migrate --seed
```

Start the local server:

```powershell
php artisan serve
```

## Demo Accounts

Use the following accounts after running `migrate --seed`:

- Owner: `owner@demo.test` / `password`
- Admin: `admin@demo.test` / `password`
- Manager: `manager@demo.test` / `password`
- Cashier: `cashier@demo.test` / `password`

## Key Features

- Multi-outlet scoping with outlet selection
- Role-based access: OWNER, ADMIN, MANAGER, CASHIER
- Products and variants, inventory movements, POS checkout
- Shift open/close and cash movements
- Receipt print (80mm), public receipt link, email and WhatsApp share
- Reports with Excel/PDF export

## Configuration Notes

- Default locale is Indonesian (`id`). Change it via `APP_LOCALE` in `.env`.
- Rounding is configured per outlet via `rounding_unit`.

## Useful Commands

Common maintenance commands:

```powershell
php artisan optimize:clear
php artisan migrate --force
```

If you are using Laragon, you can also run the app via Laragon's web server and open the project URL directly instead of using `php artisan serve`.
