<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/../includes/fungsi.php'; // mengambil fungsi CSRF dan redirect
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST' && csrf_verify(input_post('csrf_token'))) { // memeriksa metode POST dan token CSRF
    session_regenerate_id(true); // mengganti id session sebelum session dihapus
    $_SESSION = array(); // mengosongkan semua data session
    if (ini_get('session.use_cookies')) { // mengecek apakah session memakai cookie
        $parameter = session_get_cookie_params(); // mengambil pengaturan cookie session
        setcookie(session_name(), '', time() - 42000, $parameter['path'], $parameter['domain'], $parameter['secure'], $parameter['httponly']); // menghapus cookie session di browser
    } // menutup penghapusan cookie
    session_destroy(); // menghancurkan session di server
    session_start(); // memulai session baru untuk menyimpan pesan
    csrf_regenerate(); // membuat token baru untuk session tamu
    set_flash('sukses', 'Anda telah keluar dari akun.'); // menyimpan pesan logout berhasil
    redirect(BASE_URL . 'index.php'); // mengarahkan ke beranda
} // menutup blok logout valid
set_flash('gagal', 'Gagal keluar dari akun, silakan coba lagi.'); // menyimpan pesan gagal
redirect(BASE_URL . 'index.php'); // mengarahkan ke beranda jika logout gagal
