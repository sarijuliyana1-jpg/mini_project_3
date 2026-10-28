# Mini Project 3 - StokKu

## Isi fitur
- Login/logout dengan role admin dan staff
- CRUD kategori (khusus admin)
- CRUD produk (khusus admin)
- Pencarian, filter kategori, sorting, pagination produk
- Transaksi barang masuk/keluar yang memperbarui stok
- Validasi stok keluar agar tidak negatif
- Dashboard ringkas
- Pengelolaan akun admin/staff (khusus admin)
- `schema.sql` untuk struktur database

## Cara memasang di Windows + XAMPP
1. Ekstrak folder `mini_project_3` ke `C:\xampp\htdocs\mini_project_3`.
2. Buka XAMPP Control Panel, lalu klik Start untuk Apache dan MySQL.
3. Buka `http://localhost/phpmyadmin`.
4. Pilih tab Import, pilih file `schema.sql`, lalu klik Import/Go.
   - File ini membuat database `mini_project_3` dan tabel-tabelnya.
5. Periksa `config.php`. Default XAMPP biasanya `root` tanpa password. Sesuaikan jika MySQL kamu memakai password.
6. Buka `http://localhost/mini_project_3/setup.php` sekali untuk membuat akun admin awal.
7. Login di `http://localhost/mini_project_3/login.php`.
   - Username: `admin`
   - Password: `admin123`
8. Setelah akun dibuat, hapus file `setup.php` dari folder proyek.
9. Buat kategori terlebih dahulu, lalu buat produk. Setelah itu uji transaksi masuk/keluar.

## Role
- Admin: dashboard, kategori, produk, transaksi, membuat akun.
- Staff: melihat dashboard, kategori, produk, dan membuat transaksi stok. Tidak bisa mengubah kategori/produk atau membuka halaman pengguna.

## Business rules
1. Username harus unik.
2. Nama kategori dan kode produk harus unik.
3. Harga dan stok tidak boleh negatif.
4. Jumlah transaksi harus bilangan bulat lebih dari nol.
5. Barang keluar tidak boleh melebihi stok yang tersedia.
6. Setiap transaksi menyimpan pengguna, jenis transaksi, waktu, catatan, produk, jumlah, dan harga pada saat transaksi.
7. Transaksi stok dan pembaruan stok dijalankan dalam database transaction agar keduanya berhasil bersama atau dibatalkan bersama.
8. Halaman admin dilindungi pemeriksaan role di server, bukan hanya menyembunyikan tombol.

## Uji yang perlu dilakukan
- Validasi error: coba simpan produk dengan harga negatif atau transaksi dengan jumlah 0. Sistem harus menolak.
- Akses terlarang: buat akun staff, login sebagai staff, lalu buka `http://localhost/mini_project_3/users.php`. Sistem harus menampilkan 403.
- Stok tidak cukup: coba barang keluar lebih banyak dari stok. Sistem harus menolak dan stok tidak berubah.
- Pagination: masukkan lebih dari 5 produk untuk melihat tombol halaman berikutnya.

## ERD
Tabel `users` 1—N `transactions`; `categories` 1—N `products`; `transactions` 1—N `transaction_details`; `products` 1—N `transaction_details`.

Catatan: Untuk kebutuhan tugas kuliah, aplikasi ini merupakan fondasi mini project. Sebelum dipakai untuk produksi, tambahkan CSRF protection, kebijakan password yang lebih kuat, audit log, dan penanganan error yang lebih lengkap.
