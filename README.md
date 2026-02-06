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

## Tutorial Role Kasir (CASHIER)

Panduan singkat alur kerja kasir sehari-hari.

### 1. Login & Pilih Outlet

1. Buka halaman login `/login`.
2. Masuk dengan akun kasir yang telah disediakan.
3. Pilih outlet aktif (jika tersedia lebih dari satu).

### 2. Buka Shift

1. Buka menu **Shift** di navbar.
2. Klik **Buka Shift**.
3. Isi **Saldo Awal Kas** sesuai uang fisik di laci kas.
4. Simpan. Status shift berubah menjadi **Aktif**.

### 3. Buat Transaksi Penjualan

1. Cari produk (nama/scan barcode) lalu tambahkan ke cart.
2. Atur jumlah, varian, atau catatan item jika diperlukan.
3. Terapkan diskon/kupon jika ada.
4. Pilih pelanggan jika diperlukan:
   - Pilih pelanggan dari dropdown, atau
   - Klik **+ Tambah pelanggan** untuk input cepat (nama/telepon/email/alamat).
   - Jika dibiarkan kosong, transaksi dianggap pelanggan umum.
5. Pilih metode pembayaran: **Cash**, **Card**, **QRIS**, **E-Wallet**, atau **Transfer**.
6. Konfirmasi pembayaran. Sistem akan:
   - Mengurangi stok sesuai item terjual.
   - Mencatat transaksi dan pembayaran.
   - Menyediakan struk 80mm dan link receipt.

### 4. Hold / Lanjutkan Transaksi

1. Jika pelanggan belum siap bayar, gunakan **Tahan / Parkir** di POS.
2. Transaksi tersimpan sebagai **Hold** (draft).
3. Lanjutkan transaksi dari panel **Daftar Hold** di halaman POS:
    - Kasir melihat hold miliknya sendiri.
    - Owner/Admin/Manager dapat melihat semua hold outlet.

### 5. Cash Movement (Opsional)

Di halaman **Shift** (`/shifts`), gunakan fitur **Cash In/Out** untuk mencatat:

- Penambahan kas (mis. modal tambahan).
- Pengeluaran kas kecil (mis. beli plastik/air).

### 6. Tutup Shift

1. Setelah selesai, buka halaman **Shift** (`/shifts`) lalu klik **Tutup Shift**.
2. Masukkan **Saldo Akhir Kas** berdasarkan uang fisik.
3. Sistem akan menghitung selisih dan menyimpan laporan shift.

### 7. Cek Receipt

Struk bisa dicetak langsung atau dibuka via link publik:
`/receipt/{token}`.

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
