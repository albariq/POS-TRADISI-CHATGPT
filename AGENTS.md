# AGENTS.md

Panduan singkat untuk agent (Codex) saat membantu di repo ini.

## Ringkasan Proyek
- POS Tradisi adalah web app POS retail multi-outlet berbasis Laravel 12 + MySQL.
- UI: Blade + Tailwind (via CDN di repo ini).
- Fitur utama: outlet scoping, role-based access, inventory, sales, shift, receipt, report.

## Tujuan Umum Saat Membantu
- Jaga perilaku bisnis tetap konsisten (multi-outlet, role/permission, stok, kas).
- Prioritaskan perubahan yang aman, mudah diuji, dan tidak merusak alur kasir.
- Hindari perubahan besar tanpa persetujuan eksplisit.
- Ingat: aplikasi sudah ter-deploy, jadi perubahan harus ekstra hati-hati.

## Lingkungan Produksi (Wajib)
- Jangan pernah menjalankan `php artisan migrate:fresh`, `db:wipe`, atau perintah yang menghapus data.
- Hindari perubahan yang menyentuh data produksi tanpa instruksi eksplisit.
- Jika butuh reset/seed, batasi hanya untuk DB lokal/dev dan pastikan izin dulu.

## Konvensi Teknis
- Backend: Laravel 12, MySQL 8+, Spatie Permission.
- Frontend: Blade + Tailwind CDN (hindari build step tambahan jika tidak diminta).
- Bahasa default: Indonesia (lihat `APP_LOCALE` di `.env`).
- Konfigurasi pricing table di DB `pricing_settings` (per outlet + gram).

## Alur Kerja Agent
1) Pahami konteks file yang sedang dikerjakan + dampak bisnisnya.
2) Jika ada keraguan tentang aturan bisnis, tanyakan dulu.
3) Buat perubahan minimal yang menyelesaikan request.
4) Tambahkan komentar hanya bila logika tidak obvious.

## Yang Harus Dicek Sebelum Selesai
- Validasi input dan error handling di titik perubahan.
- Multi-outlet scoping tidak bocor (query harus terikat outlet).
- Permission/role sesuai (Owner/Admin/Manager/Cashier).
- Tidak mengubah UI/UX utama kasir tanpa persetujuan.

## Do / Don't (Kasir, Shift, Stok)
**Do**
- Pastikan perhitungan total, diskon, pajak, dan pembulatan konsisten di semua layar terkait.
- Jaga integritas shift: pembukaan/penutupan kas tidak boleh terlewat.
- Rekam pergerakan stok secara atomik saat transaksi (hindari stok minus tanpa izin).
- Pastikan receipt/print tetap kompatibel (format 80mm).

**Don't**
- Jangan ubah alur checkout utama tanpa konfirmasi eksplisit.
- Jangan hapus atau menonaktifkan audit trail (shift, kas, stok).
- Jangan ubah aturan rounding outlet secara default.
- Jangan membiarkan transaksi sukses tanpa update stok/shift yang sesuai.

## Testing / Verifikasi (opsional)
- `php artisan optimize:clear`
- `php artisan migrate --seed` (hanya untuk DB lokal/dev)
- `php artisan serve` (atau via Laragon)

## File Penting
- `routes/` untuk routing.
- `app/` untuk business logic.
- `resources/views/` untuk Blade.
- `config/` untuk konfigurasi.
- `database/` untuk migrasi/seed.

## Catatan Tambahan
- Jika user minta perubahan besar (arsitektur/UI besar), minta detail dulu.
- Jika butuh data contoh, gunakan akun demo di `README.md`.
