# Sistem Rental Mobil

Aplikasi web rental mobil berbasis PHP native, MySQL PDO, XHTML 1.0 Transitional, CSS, dan JavaScript. Proyek ini dibuat bertahap untuk tugas Pemrograman Web Dasar dan sudah mencakup alur pengguna, administrasi mobil, transaksi sewa, profil, hingga data pengguna.

## 1. Instalasi XAMPP

1. Install XAMPP lalu buka **XAMPP Control Panel**.
2. Aktifkan modul **Apache** dan **MySQL**.
3. Salin atau clone folder proyek ke:

   ```text
   C:\xampp\htdocs\rental-mobil
   ```

4. Import database melalui phpMyAdmin:
   - Buka `http://localhost/phpmyadmin/`
   - Pilih tab **Import**
   - Pilih berkas `database/rental_mobil.sql`
   - Klik **Go**

   Alternatif lewat command line:

   ```powershell
   C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\rental-mobil\database\rental_mobil.sql
   ```

5. Pastikan folder berikut ada dan dapat ditulis:

   ```text
   assets/uploads/profil
   assets/uploads/mobil
   ```

6. Buka aplikasi di:

   ```text
   http://localhost/rental-mobil/
   ```

7. Jika MySQL belum aktif dan muncul error `2002`, jalankan MySQL dari XAMPP Control Panel atau:

   ```powershell
   C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini
   ```

Catatan: peta Leaflet dan tile OpenStreetMap memerlukan koneksi internet.

## 2. Akun Demo

| Role | Username | Password | Email | Keterangan |
|---|---|---|---|---|
| Admin | `admin` | `admin123` | `admin@rental-mobil.test` | Mengelola mobil, rental, dan pengguna |
| User | `budi` | `user123` | `budi@rental-mobil.test` | Penyewa contoh pertama |
| User | `sari` | `user456` | `sari@rental-mobil.test` | Penyewa contoh kedua |

Data awal: **3 users**, **5 cars**, **0 rentals**.

## 3. Struktur Folder

```text
rental-mobil/
├── api/
│   └── cek_user.php                  # AJAX pengecekan username
├── assets/
│   ├── css/
│   │   └── style.css                 # seluruh gaya tampilan
│   ├── img/
│   │   ├── background/               # gambar latar hero/login/register/_PROFIL
│   │   ├── cars/                     # foto mobil contoh
│   │   ├── logo/                     # logo aplikasi
│   │   └── profil/                   # foto profil contoh
│   ├── js/
│   │   ├── admin.js                  # konfirmasi hapus mobil
│   │   ├── foto-preview.js           # pratinjau upload foto
│   │   ├── konfirmasi.js             # konfirmasi generik
│   │   ├── main.js                   # menu responsif
│   │   ├── peta-detail.js            # peta lokasi jemput
│   │   ├── peta-sewa.js              # geolocation + kalkulasi sewa
│   │   └── register.js               # validasi registrasi
│   └── uploads/
│       ├── mobil/                    # hasil upload foto mobil
│       └── profil/                   # hasil upload foto profil
├── auth/
│   ├── daftar.php                    # registrasi pengguna
│   ├── login.php                     # login
│   └── logout.php                    # logout
├── config/
│   └── koneksi.php                   # koneksi PDO, BASE_URL, session
├── database/
│   └── rental_mobil.sql              # skema + data awal
├── includes/
│   ├── cek_admin.php                 # pembatas akses admin
│   ├── cek_login.php                 # pembatas akses login
│   ├── footer.php                    # penutup XHTML
│   ├── fungsi.php                    # helper CSRF, flash, validasi, upload, dll.
│   └── header.php                    # DOCTYPE, navbar, flash
├── admin/
│   ├── index.php                     # dashboard admin
│   ├── mobil.php                     # daftar mobil
│   ├── mobil_tambah.php              # tambah mobil
│   ├── mobil_edit.php                # ubah mobil
│   ├── mobil_hapus.php               # hapus mobil
│   ├── rental.php                    # daftar rental
│   ├── rental_edit.php               # ubah status rental
│   ├── rental_hapus.php              # hapus rental
│   ├── user.php                      # daftar pengguna + filter
│   ├── user_detail.php               # detail pengguna
│   └── user_edit.php                 # ubah data/peran pengguna
├── user/
│   ├── dashboard.php                 # beranda setelah login
│   ├── sewa.php                      # form sewa + peta
│   ├── riwayat.php                   # riwayat sewa
│   ├── rental_detail.php             # detail sewa + peta
│   ├── rental_ubah.php               # ubah tanggal sewa
│   ├── rental_batal.php              # pembatalan sewa
│   ├── profil.php                    # profil pengguna
│   ├── profil_edit.php               # ubah profil
│   ├── password.php                  # ganti password
│   └── foto.php                      # ganti foto profil
├── detail.php                        # detail mobil untuk publik
├── index.php                         # beranda
├── katalog.php                       # katalog mobil
└── README.md                         # dokumentasi proyek
```

## 4. Pemetaan Ketentuan Tugas ke File

| Ketentuan / Fitur | File Utama | Catatan |
|---|---|---|
| Database dan koneksi PDO | `database/rental_mobil.sql`, `config/koneksi.php` | Prepared statement, `ERRMODE_EXCEPTION`, `EMULATE_PREPARES=false` |
| Tampilan XHTML 1.0 Transitional | `includes/header.php`, `includes/footer.php`, semua `*.php` halaman | Tidak memakai tag/atribut HTML5 |
| CSS eksternal dan responsif | `assets/css/style.css`, `assets/js/main.js` | Termasuk tata letak dua kolom dashboard |
| Komentar pada setiap baris kode | Seluruh `*.php`, `*.css`, `*.js`, `*.sql` | PHP `//`, XHTML `<!-- -->`, CSS `/* */`, JS `//`, SQL `--` |
| Session, flash message, CSRF, redirect aman | `config/koneksi.php`, `includes/fungsi.php` | Helper `csrf_field()`, `csrf_verify()`, `set_flash()`, `redirect()` |
| Proteksi login dan admin | `includes/cek_login.php`, `includes/cek_admin.php` | User biasa tidak bisa membuka halaman admin |
| Registrasi + cek username AJAX | `auth/daftar.php`, `api/cek_user.php`, `assets/js/register.js` | Password disimpan dengan `password_hash()` |
| Login dan logout | `auth/login.php`, `auth/logout.php` | Login memakai username atau email |
| Katalog dan detail mobil | `katalog.php`, `detail.php` | pencarian, filter merek, detail foto |
| CRUD data mobil admin | `admin/mobil.php`, `admin/mobil_tambah.php`, `admin/mobil_edit.php`, `admin/mobil_hapus.php` | Validasi plat unik + upload gambar |
| Upload dan penghapusan gambar | `includes/fungsi.php`, `user/foto.php`, `admin/mobil_*.php` | Ekstensi/MIME dibatasi, maksimal 2 MB |
| Form sewa + peta + geolocation | `user/sewa.php`, `assets/js/peta-sewa.js` | Leaflet 1.9.4 dan OpenStreetMap |
| Validasi tanggal dan bentrok sewa | `includes/fungsi.php`, `user/sewa.php`, `user/rental_ubah.php` | `tanggal_valid()`, `hitung_hari()`, `rental_aktif_bertabrakan()` |
| Riwayat dan detail transaksi user | `user/riwayat.php`, `user/rental_detail.php`, `assets/js/peta-detail.js` | Pengecekan ownership `user_id` |
| Ubah tanggal dan batal sewa | `user/rental_ubah.php`, `user/rental_batal.php` | Hanya status `pending`/`dikonfirmasi` |
| Kelola status rental admin | `admin/rental.php`, `admin/rental_edit.php`, `admin/rental_hapus.php` | Status tersinkron dengan status mobil |
| Profil, password, foto profil | `user/profil.php`, `user/profil_edit.php`, `user/password.php`, `user/foto.php` | Password lama diverifikasi dengan `password_verify()` |
| Dashboard admin | `admin/index.php` | Kartu ringkasan + tabel rental terbaru + pengguna terbaru |
| Data pengguna admin | `admin/user.php`, `admin/user_detail.php`, `admin/user_edit.php` | Pencarian, filter role, edit data/peran, proteksi peran admin sendiri |
| Format Rupiah dan label status | `includes/fungsi.php` | Helper `rupiah()`, `label_status_rental()` |
| Sinkronisasi status mobil | `includes/fungsi.php` | Helper `sinkronkan_status_mobil()` |

## 5. Checklist Pengujian Manual

- [ ] Apache dan MySQL aktif di XAMPP Control Panel.
- [ ] Database `rental_mobil` berisi 3 users, 5 cars, dan 0 rentals setelah import.
- [ ] Beranda, katalog, dan detail mobil terbuka dengan gambar yang benar.
- [ ] Registrasi user baru berhasil; username duplikat ditolak dengan AJAX.
- [ ] Login `admin/admin123`, `budi/user123`, dan `sari/user456` berhasil.
- [ ] Logout mengakhiri sesi dan mengarahkan ke halaman login/beranda.
- [ ] Form dengan token CSRF ditolak bila token diubah atau dihapus.
- [ ] Admin menambah, mengubah, dan menghapus mobil; plat duplikat ditolak.
- [ ] Upload foto mobil valid berhasil; file lebih dari 2 MB dan ekstensi salah ditolak.
- [ ] User menyewa mobil, lokasi jemput terisi, dan total harga terhitung otomatis.
- [ ] Tanggal selesai sebelum/sama dengan tanggal mulai ditolak.
- [ ] Mobil sudah disewa pada rentang tanggal tertentu tidak bisa disewa ulang.
- [ ] Status mobil otomatis menjadi `disewa` saat ada rental aktif dan kembali `tersedia` setelah selesai/dibatalkan.
- [ ] User melihat riwayat dan detail sewa; data rental orang lain tidak bisa diakses.
- [ ] User mengubah tanggal sewa aktif dan membatalkan sewa; status tersinkron.
- [ ] Admin mengubah status rental menjadi `dikonfirmasi`, `selesai`, atau `dibatalkan`.
- [ ] User mengubah nama, email, telepon, alamat, password, dan foto profil.
- [ ] Email duplikat saat edit profil/pengguna ditolak.
- [ ] Dashboard admin menampilkan kartu ringkasan, tabel rental terbaru, dan tabel pengguna terbaru.
- [ ] Halaman data pengguna mencari berdasarkan nama/username/email dan memfilter role.
- [ ] Admin mengubah data pengguna; admin tidak dapat menurunkan perannya sendiri.
- [ ] User biasa yang membuka URL admin diarahkan ke beranda.
- [ ] Semua halaman lolos XHTML 1.0 Transitional (validator dijalankan per halaman).
- [ ] Semua tautan internal dan gambar menghasilkan status `200`/`302`.
- [ ] Seluruh file PHP, CSS, JS, dan SQL memiliki komentar pada setiap baris kode.
- [ ] `php -l` untuk semua PHP dan `node --check` untuk semua JS tanpa error.

## 6. Catatan Teknis

- Named parameter PDO sengaja dibedakan (`:q1`, `:q2`, `:q3`) karena `PDO::ATTR_EMULATE_PREPARES` dinonaktifkan.
- Konfirmasi hapus memakai `form.form-konfirmasi` dan `input.pesan-konfirmasi`, bukan atribut `data-*` agar tetap valid XHTML.
- Folder `assets/uploads/` sengaja kosong pada repositori awal; direktori dibuat saat aplikasi dijalankan.
- Import ulang `database/rental_mobil.sql` akan menghapus dan mengganti seluruh data existing.
