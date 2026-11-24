CREATE DATABASE IF NOT EXISTS rental_mobil CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; -- membuat database rental_mobil jika belum ada
USE rental_mobil; -- memilih database rental_mobil sebagai database aktif
SET FOREIGN_KEY_CHECKS = 0; -- menonaktifkan pengecekan foreign key agar tabel bisa dihapus urut bebas
DROP TABLE IF EXISTS rentals; -- menghapus tabel transaksi lama jika ada
DROP TABLE IF EXISTS cars; -- menghapus tabel mobil lama jika ada
DROP TABLE IF EXISTS users; -- menghapus tabel pengguna lama jika ada
SET FOREIGN_KEY_CHECKS = 1; -- mengaktifkan kembali pengecekan foreign key
CREATE TABLE users ( -- membuat tabel DATA pengguna
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- menyimpan id unik pengguna secara otomatis
  username VARCHAR(50) NOT NULL UNIQUE, -- menyimpan nama pengguna yang tidak boleh sama
  email VARCHAR(100) NOT NULL UNIQUE, -- menyimpan email pengguna yang tidak boleh sama
  password VARCHAR(255) NOT NULL, -- menyimpan kata sandi yang sudah di-hash dengan password_hash
  nama_lengkap VARCHAR(100) NOT NULL, -- menyimpan nama lengkap pengguna
  foto VARCHAR(255) NOT NULL DEFAULT 'assets/img/profil/default.jpg', -- menyimpan path foto profil dengan foto default
  role ENUM('user','admin') NOT NULL DEFAULT 'user', -- menyimpan peran pengguna sebagai user atau admin
  no_telepon VARCHAR(20) DEFAULT NULL, -- menyimpan nomor telepon pengguna secara opsional
  alamat TEXT DEFAULT NULL, -- menyimpan alamat pengguna secara opsional
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- mencatat waktu pembuatan akun
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- mencatat waktu terakhir akun diubah
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- memakai mesin InnoDB dan karakter UTF-8
CREATE TABLE cars ( -- membuat tabel DATA mobil
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- menyimpan id unik mobil secara otomatis
  nama VARCHAR(100) NOT NULL, -- menyimpan nama mobil yang ditampilkan di katalog
  merek VARCHAR(50) NOT NULL, -- menyimpan merek mobil seperti BMW atau Ferrari
  model VARCHAR(50) NOT NULL, -- menyimpan model atau tipe mobil
  tahun YEAR NOT NULL, -- menyimpan tahun pembuatan mobil
  warna VARCHAR(30) NOT NULL, -- menyimpan warna mobil
  plat_nomor VARCHAR(20) NOT NULL UNIQUE, -- menyimpan plat nomor mobil yang tidak boleh sama
  harga_per_hari DECIMAL(10,2) NOT NULL, -- menyimpan harga sewa per hari
  gambar VARCHAR(255) NOT NULL, -- menyimpan path foto mobil
  deskripsi TEXT DEFAULT NULL, -- menyimpan deskripsi singkat mobil
  status ENUM('tersedia','disewa','perbaikan') NOT NULL DEFAULT 'tersedia', -- menyimpan status ketersediaan mobil
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- mencatat waktu data mobil dibuat
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- mencatat waktu terakhir data mobil diubah
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- memakai mesin InnoDB dan karakter UTF-8
CREATE TABLE rentals ( -- membuat tabel DATA transaksi rental
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, -- menyimpan id unik transaksi secara otomatis
  user_id INT UNSIGNED NOT NULL, -- menyimpan id pengguna yang menyewa
  car_id INT UNSIGNED NOT NULL, -- menyimpan id mobil yang disewa
  tanggal_mulai DATE NOT NULL, -- menyimpan tanggal mulai sewa
  tanggal_selesai DATE NOT NULL, -- menyimpan tanggal selesai sewa
  lokasi_jemput VARCHAR(255) DEFAULT NULL, -- menyimpan alamat lokasi penjemputan
  latitude DECIMAL(10,7) DEFAULT NULL, -- menyimpan koordinat latitude lokasi penjemputan
  longitude DECIMAL(10,7) DEFAULT NULL, -- menyimpan koordinat longitude lokasi penjemputan
  total_hari INT NOT NULL, -- menyimpan jumlah hari sewa yang sudah dihitung otomatis
  total_harga DECIMAL(10,2) NOT NULL, -- menyimpan total harga sewa yang sudah dihitung otomatis
  status ENUM('pending','dikonfirmasi','selesai','dibatalkan') NOT NULL DEFAULT 'pending', -- menyimpan status transaksi rental
  catatan TEXT DEFAULT NULL, -- menyimpan catatan tambahan dari pengguna atau admin
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- mencatat waktu transaksi dibuat
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- mencatat waktu terakhir transaksi diubah
  CONSTRAINT fk_rentals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE, -- menghubungkan transaksi ke tabel users
  CONSTRAINT fk_rentals_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE RESTRICT ON UPDATE CASCADE, -- menghubungkan transaksi ke tabel cars
  INDEX idx_rentals_tanggal (car_id, tanggal_mulai, tanggal_selesai), -- mempercepat pencarian bentrok tanggal sewa
  INDEX idx_rentals_status (status) -- mempercepat pencarian transaksi berdasarkan status
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- memakai mesin InnoDB dan karakter UTF-8
INSERT INTO users (id, username, email, password, nama_lengkap, foto, role, no_telepon, alamat) VALUES (1, 'admin', 'admin@rental-mobil.test', '$2y$10$M3kTtXu63W08V2xUhXAAvuBb/gukQYGiWLcOX6eznafhynCPswCoG', 'Administrator', 'assets/img/profil/default.jpg', 'admin', '081200000001', 'Jl. Admin No. 1'); -- memasukkan satu akun admin contoh dengan password admin123
INSERT INTO users (id, username, email, password, nama_lengkap, foto, role, no_telepon, alamat) VALUES (2, 'budi', 'budi@rental-mobil.test', '$2y$10$V28GFrRveDnkvmVB5WgNJu1JFK.esWDmEESlyZwO.tI2joXkyrwdS', 'Budi Santoso', 'assets/img/profil/user1.jpg', 'user', '081200000002', 'Jl. Melati No. 12'); -- memasukkan akun user contoh pertama dengan password user123
INSERT INTO users (id, username, email, password, nama_lengkap, foto, role, no_telepon, alamat) VALUES (3, 'sari', 'sari@rental-mobil.test', '$2y$10$ZTit0AozgGiCtsFK8c3Hjep.V2pC8EshE9d5DlA5kLnTPAPSjhrqy', 'Sari Putri', 'assets/img/profil/user2.jpg', 'user', '081200000003', 'Jl. Mawar No. 8'); -- memasukkan akun user contoh kedua dengan password user456
INSERT INTO cars (id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES (1, 'BMW M4 Competition', 'BMW', 'M4 Competition', 2023, 'Biru', 'B 1234 BMW', 1500000.00, 'assets/img/cars/bmw_m4_competition.jpg', 'Mobil sport BMW dengan performa tinggi dan kabin nyaman.', 'tersedia'); -- memasukkan mobil contoh pertama
INSERT INTO cars (id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES (2, 'Bugatti Chiron', 'Bugatti', 'Chiron', 2022, 'Biru', 'B 2222 BUG', 8500000.00, 'assets/img/cars/bugatti_chiron.jpg', 'Mobil hypercar mewah dengan kecepatan dan desain eksklusif.', 'tersedia'); -- memasukkan mobil contoh kedua
INSERT INTO cars (id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES (3, 'Ferrari Roma', 'Ferrari', 'Roma', 2023, 'Merah', 'B 3333 FER', 6200000.00, 'assets/img/cars/ferrari_roma.jpg', 'Grand tourer Ferrari yang elegan dengan ritme berkendara halus.', 'tersedia'); -- memasukkan mobil contoh ketiga
INSERT INTO cars (id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES (4, 'Lamborghini Urus', 'Lamborghini', 'Urus', 2024, 'Kuning', 'B 4444 LAM', 5800000.00, 'assets/img/cars/lamborghini_urus.jpg', 'SUV sport Lamborghini dengan ruang luas dan tenaga besar.', 'tersedia'); -- memasukkan mobil contoh keempat
INSERT INTO cars (id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, deskripsi, status) VALUES (5, 'Mercedes-Benz S-Class', 'Mercedes-Benz', 'S-Class', 2023, 'Hitam', 'B 5555 MER', 3200000.00, 'assets/img/cars/mercedes_s_class.jpg', 'Sedan mewah Mercedes-Benz dengan kenyamanan kelas atas.', 'tersedia'); -- memasukkan mobil contoh kelima
