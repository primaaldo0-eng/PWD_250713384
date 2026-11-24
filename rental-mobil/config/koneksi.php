<?php // membuka tag PHP dengan komentar agar tidak ada output sebelum session dimulai
// Mengatur zona waktu aplikasi ke Jakarta
date_default_timezone_set('Asia/Jakarta'); // menjamkan server sesuai waktu Indonesia
if (session_status() === PHP_SESSION_NONE) { // mengecek apakah session belum aktif
    session_start(); // memulai session sebelum ada output apa pun
} // menutup blok pengecekan session
define('DB_HOST', 'localhost'); // menyimpan alamat host database
define('DB_NAME', 'rental_mobil'); // menyimpan nama database
define('DB_USER', 'root'); // menyimpan user database XAMPP
define('DB_PASS', ''); // menyimpan password database XAMPP yang kosong
define('BASE_URL', '/rental-mobil/'); // menyimpan alamat URL dasar aplikasi
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR); // menyimpan alamat folder dasar aplikasi
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4'; // merangkai string koneksi PDO
try { // memulai percobaan koneksi database
    $pdo = new PDO($dsn, DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false)); // membuat koneksi PDO sekaligus mengatur prepared statement asli
} catch (PDOException $e) { // menangkap error koneksi tanpa menampilkannya ke pengunjung
    error_log('Koneksi database gagal: ' . $e->getMessage()); // menyimpan detail error ke log server
    die('Maaf, sistem sedang tidak dapat terhubung ke database.'); // menampilkan pesan umum yang aman
} // menutup blok percobaan koneksi
