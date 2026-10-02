# SIMASDARSA

Sistem informasi manajemen stok dan penjualan berbasis web untuk toko/warung UMKM. Dibangun dengan Laravel dan Tailwind CSS.

## Fitur

- **Login & hak akses** berdasarkan role (Pimpinan, Tim IT, Manager, Kasir) dan permission per menu.
- **Manajemen produk** lengkap dengan barcode, kategori, satuan, dan stok minimum.
- **Batch stok** dengan tanggal masuk dan tanggal kedaluwarsa, verifikasi stok masuk, update stok fisik, serta status lokasi barang.
- **Monitoring kedaluwarsa** dan notifikasi email otomatis setiap hari untuk stok yang akan expired dalam 30 hari.
- **Kasir (POS)**: pencarian produk dan proses transaksi penjualan.
- **Riwayat penjualan** dan detail transaksi.
- **Laporan** laba-rugi dan laporan eksekutif.
- **Panel Tim IT**: manajemen user, pengaturan permission, dan audit log (bisa diekspor).

## Teknologi

- PHP 8.3+ dan Laravel 13
- MySQL / MariaDB
- Vite dan Tailwind CSS 4

## Instalasi

1. Clone repositori lalu masuk ke foldernya.

   ```bash
   git clone https://github.com/Rzkyyy-cpu/simasdarsa.git
   cd simasdarsa
   ```

2. Install dependensi PHP dan JavaScript.

   ```bash
   composer install
   npm install
   ```

3. Salin file environment dan buat application key.

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Atur koneksi database di `.env`. Seeder data dummy memakai perintah khusus MySQL, jadi gunakan MySQL/MariaDB:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=simasdarsa
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Jalankan migrasi dan seeder. Seeder membuat 500 produk dummy, batch stok, riwayat penjualan 90 hari, dan akun demo.

   ```bash
   php artisan migrate --seed
   ```

6. Build aset frontend lalu jalankan server.

   ```bash
   npm run build
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000` di browser.

## Akun Demo

Semua akun memakai password `password`.

| Role     | Email                    |
|----------|--------------------------|
| Pimpinan | pimpinan@simasdarsa.com  |
| Tim IT   | tim_it@simasdarsa.com    |
| Manager  | manager@simasdarsa.com   |
| Kasir    | kasir@simasdarsa.com     |

Role Pimpinan dan Tim IT bisa membuka semua menu. Untuk Manager dan Kasir, atur dulu permission menunya lewat akun Tim IT di menu **User Management**.

Ganti password akun demo sebelum aplikasi dipakai sungguhan.

## Notifikasi Kedaluwarsa

Perintah berikut mengecek batch stok yang akan expired dalam 30 hari dan mengirim email ke user dengan role Pimpinan dan Manager:

```bash
php artisan stock:check-expiry
```

Perintah ini sudah dijadwalkan jalan setiap hari pukul 08:00. Supaya jadwal aktif, jalankan scheduler Laravel:

```bash
php artisan schedule:work
```

Secara default email hanya ditulis ke log (`MAIL_MAILER=log`). Isi pengaturan `MAIL_*` di `.env` untuk mengirim email sungguhan.
