# SiPustaka HMVC

SiPustaka adalah aplikasi perpustakaan Laravel untuk mengelola koleksi buku, peminjaman, dan akun anggota. Fitur aplikasi dipisah ke modul HMVC menggunakan `nwidart/laravel-modules`.

## Menjalankan aplikasi

1. Pasang dependensi PHP dan JavaScript:

   ```bash
   composer install
   npm install
   ```

2. Salin `.env.example` menjadi `.env`, lalu atur koneksi database. Untuk instalasi lokal SQLite, buat file `database/database.sqlite`.
3. Buat application key dan jalankan migrasi serta akun contoh:

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

4. Kompilasi aset dan jalankan server:

   ```bash
   npm run build
   php artisan serve
   ```

Untuk pengembangan UI, jalankan `npm run dev` di terminal terpisah.

## Akun contoh

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@sipustaka.com` | `admin12345` |
| Anggota | `user@sipustaka.com` | `user12345` |

Registrasi publik selalu menghasilkan akun anggota. Peran admin hanya dapat diberikan melalui pengelolaan pengguna.

## Modul dan akses

- **Buku** (`Modules/Buku`): katalog, pencarian judul/penulis/penerbit, serta CRUD yang hanya dapat dilakukan admin.
- **Peminjaman** (`Modules/Peminjaman`): CRUD melalui AJAX; anggota melihat dan mengelola peminjamannya sendiri, sedangkan admin dapat mengelola seluruh data. Bukti menerima JPG, PNG, atau PDF maksimal 2 MB dan disimpan pada disk `public`.
- **Pengelolaan Pengguna** (`Modules/UserManagement`): CRUD akun dan pengaturan peran untuk admin.

Semua halaman data memerlukan login. Alias middleware `role` terdaftar di `bootstrap/app.php`. FormRequest menangani validasi dan respons validasi AJAX menggunakan status HTTP 422.

## Pengujian

```bash
php artisan test
```
