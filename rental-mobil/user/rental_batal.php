<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { // memastikan pembatalan lewat POST
    set_flash('gagal', 'Metode pembatalan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
} // menutup pengecekan metode
if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
    set_flash('gagal', 'Token keamanan tidak valid.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
} // menutup pengecekan token
$id = (int)input_post('id'); // membaca ID rental dari formulir
$stmt = $pdo->prepare('SELECT id, car_id, status FROM rentals WHERE id = :id AND user_id = :user LIMIT 1'); // menyiapkan query rental milik pengguna
$stmt->execute(array(':id' => $id, ':user' => $_SESSION['user_id'])); // menjalankan query dengan pembatas pengguna
$rental = $stmt->fetch(); // mengambil data rental
if (!$rental) { // memastikan rental ditemukan
    set_flash('gagal', 'Data rental tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
} // menutup pengecekan rental
if ($rental['status'] !== 'pending' && $rental['status'] !== 'dikonfirmasi') { // membatasi pembatalan pada rental aktif
    set_flash('gagal', 'Status rental ini tidak dapat dibatalkan.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/rental_detail.php?id=' . (int)$rental['id']); // kembali ke detail
} // menutup pengecekan status
try { // menjalankan perubahan dalam pengaman exception
    $ubah = $pdo->prepare('UPDATE rentals SET status = :status WHERE id = :id'); // menyiapkan query pembatalan
    $ubah->execute(array(':status' => 'dibatalkan', ':id' => (int)$rental['id'])); // menjalankan pembatalan
    sinkronkan_status_mobil($pdo, (int)$rental['car_id']); // menyelaraskan status mobil
    set_flash('sukses', 'Penyewaan berhasil dibatalkan.'); // menyimpan pesan sukses
} catch (PDOException $exception) { // menangani kegagalan database
    set_flash('gagal', 'Gagal membatalkan penyewaan.'); // menyimpan pesan error
} // menutup pengaman exception
redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
