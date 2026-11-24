<?php // membuka tag PHP dengan komentar agar tidak ada output sebelum session dimulai
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan memastikan session aktif
require_once __DIR__ . '/fungsi.php'; // mengambil fungsi pembantu seperti redirect dan e
if (empty($_SESSION['user_id'])) { // mengecek apakah pengunjung belum login
    redirect(BASE_URL . 'auth/login.php'); // mengarahkan tamu ke halaman login
} // menutup blok pengecekan login
$stmt = $pdo->prepare('SELECT id, username, email, nama_lengkap, foto, role FROM users WHERE id = :id LIMIT 1'); // menyiapkan query aman untuk mencari pengguna login
$stmt->execute(array(':id' => $_SESSION['user_id'])); // menjalankan query dengan id dari session
$userLogin = $stmt->fetch(); // mengambil data pengguna yang sedang login
if (!$userLogin) { // mengecek apakah data pengguna tidak ditemukan
    session_destroy(); // menghancurkan session yang sudah tidak valid
    redirect(BASE_URL . 'auth/login.php'); // mengarahkan kembali ke halaman login
} // menutup blok pengecekan data pengguna
