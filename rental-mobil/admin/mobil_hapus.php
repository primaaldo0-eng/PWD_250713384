<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman hanya untuk admin
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { // memastikan hapus hanya lewat POST
    set_flash('gagal', 'Metode penghapusan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/mobil.php'); // kembali ke daftar mobil
} // menutup pengecekan metode
if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan formulir
    set_flash('gagal', 'Token keamanan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/mobil.php'); // kembali ke daftar mobil
} // menutup pengecekan token
$id = (int)input_post('id'); // membaca ID mobil yang akan dihapus
$stmt = $pdo->prepare('SELECT gambar FROM cars WHERE id = :id'); // menyiapkan query ambil gambar
$stmt->execute(array(':id' => $id)); // menjalankan query ambil gambar
$gambar = $stmt->fetchColumn(); // mengambil path gambar lama
if ($gambar === false) { // memastikan data mobil ditemukan
    set_flash('gagal', 'Data mobil tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/mobil.php'); // kembali ke daftar mobil
} // menutup pengecekan data
try { // menjalankan penghapusan dalam pengaman exception
    $hapus = $pdo->prepare('DELETE FROM cars WHERE id = :id'); // menyiapkan query hapus
    $hapus->execute(array(':id' => $id)); // menjalankan penghapusan data
    hapus_gambar_lama((string)$gambar); // menghapus file gambar dari server
    set_flash('sukses', 'Mobil berhasil dihapus.'); // menyimpan pesan sukses
} catch (PDOException $exception) { // menangani bila data masih dipakai transaksi
    set_flash('gagal', 'Mobil tidak dapat dihapus karena masih digunakan dalam transaksi.'); // menyimpan pesan error
} // menutup pengaman exception
redirect(BASE_URL . 'admin/mobil.php'); // kembali ke daftar mobil
