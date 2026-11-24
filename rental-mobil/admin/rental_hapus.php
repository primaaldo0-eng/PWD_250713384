<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { // memastikan penghapusan lewat POST
    set_flash('gagal', 'Metode penghapusan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar
} // menutup pengecekan metode
if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
    set_flash('gagal', 'Token keamanan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar
} // menutup pengecekan token
$id = (int)input_post('id'); // membaca ID rental
$stmt = $pdo->prepare('SELECT car_id FROM rentals WHERE id = :id LIMIT 1'); // menyiapkan query ambil mobil terkait
$stmt->execute(array(':id' => $id)); // menjalankan query
$carId = $stmt->fetchColumn(); // mengambil ID mobil
if ($carId === false) { // memastikan rental ditemukan
    set_flash('gagal', 'Data rental tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar
} // menutup pengecekan rental
try { // menjalankan penghapusan dalam pengaman exception
    $hapus = $pdo->prepare('DELETE FROM rentals WHERE id = :id'); // menyiapkan query hapus
    $hapus->execute(array(':id' => $id)); // menjalankan penghapusan
    sinkronkan_status_mobil($pdo, (int)$carId); // menyelaraskan status mobil
    set_flash('sukses', 'Data rental berhasil dihapus.'); // menyimpan pesan sukses
} catch (PDOException $exception) { // menangani kegagalan database
    set_flash('gagal', 'Gagal menghapus data rental.'); // menyimpan pesan error
} // menutup pengaman exception
redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar
