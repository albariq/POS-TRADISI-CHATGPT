# ERP Finance Roadmap (POS Tradisi)

Dokumen ini menjelaskan rencana pengembangan ERP dengan fokus Finance + General Ledger (GL) manual terlebih dulu.

## Tujuan Utama
- Menambahkan modul finance yang terpisah dari POS.
- Menyediakan GL manual yang rapi sebelum otomatisasi jurnal.
- Tetap aman untuk multi-outlet dan mudah dikembangkan.

## Asumsi Saat Ini
- Outlet: 2 sekarang, dan akan bertambah.
- GL: full GL tetapi input jurnal manual dulu.
- Integrasi awal: receipt tetap seperti sekarang (tanpa perubahan besar).
- User tidak coding: semua implementasi dilakukan oleh agent.

## Tahap 1 — Finance Core + GL Manual
1) Master Data Akuntansi
   - Chart of Accounts (COA)
   - Fiscal year & period
   - Opening balance
2) Journal Entry (Manual)
   - Input jurnal debit/credit
   - Validasi total debit = total credit
   - Wajib pilih outlet (untuk multi-outlet)
3) Posting & Ledger
   - Posting jurnal
   - General Ledger per akun
   - Trial Balance
   - Laporan Laba Rugi dan Neraca dasar
4) Laporan
   - Filter outlet & periode
   - Export sederhana (opsional)

## Tahap 2 — Integrasi POS Ringan
- Transaksi POS → draft jurnal (belum otomatis posting).
- User konfirmasi sebelum posting.
- Receipt tetap existing (minim perubahan).

## Tahap 3 — Otomasi Bertahap
- Mapping akun per transaksi (jual, retur, diskon, pajak).
- Auto-journal opsional.
- Lock period & audit trail.

## Keputusan Yang Diperlukan (Agar Implementasi Mulai)
1) Struktur COA: standar atau custom?
2) Outlet wajib pada jurnal? (ya/tidak)
3) Jurnal boleh di-edit setelah posting? (ya/tidak)
4) Periode akuntansi: bulanan? butuh lock period?
5) Mata uang: hanya IDR?

## Deliverables Tahap 1 (Target)
- Migrations tabel finance (COA, journals, journal_lines, periods).
- Model + Policy dasar.
- UI input jurnal (Filament).
- Laporan GL / Trial Balance / Laba Rugi / Neraca.

## Catatan
- Setelah keputusan di atas, agent akan mulai implementasi tahap 1.
- Semua perubahan akan dibuat minimal dan aman untuk alur kasir.
