<?php // membuka tag PHP dengan komentar agar tidak ada output sebelum session dimulai
require_once __DIR__ . '/cek_login.php'; // memastikan pengunjung sudah login sebelum cek role
if ($userLogin['role'] !== 'admin') { // mengecek apakah peran pengguna bukan admin
    redirect(BASE_URL . 'index.php'); // mengarahkan pengguna biasa kembali ke beranda
} // menutup blok pengecekan peran admin
