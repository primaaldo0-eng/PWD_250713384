<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan memulai session
require_once __DIR__ . '/../includes/fungsi.php'; // mengambil fungsi pembantu
header('Content-Type: application/json; charset=utf-8'); // memberi tahu browser bahwa keluaran berupa JSON
$statusKode = 200; // menyiapkan kode status bawaan
$hasil = array('username' => array('tersedia' => false, 'pesan' => 'Masukkan username.'), 'email' => array('tersedia' => false, 'pesan' => 'Masukkan email.')); // menyiapkan bentuk jawaban JSON
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode !== 'GET') { // mengecek apakah metode bukan GET
    http_response_code(405); // mengirim kode status metode tidak diizinkan
    $hasil = array('username' => array('tersedia' => false, 'pesan' => 'Metode tidak diizinkan.'), 'email' => array('tersedia' => false, 'pesan' => 'Metode tidak diizinkan.')); // mengganti jawaban dengan pesan kesalahan
    echo json_encode($hasil, JSON_UNESCAPED_UNICODE); // mencetak jawaban JSON
    exit; // menghentikan kode berikutnya
} // menutup pengecekan metode
$username = isset($_GET['username']) && is_string($_GET['username']) ? trim($_GET['username']) : ''; // membaca username dari query string
$email = isset($_GET['email']) && is_string($_GET['email']) ? trim($_GET['email']) : ''; // membaca email dari query string
try { // memulai percobaan pemeriksaan database
    if ($username !== '') { // mengecek apabila username dikirim
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) { // memeriksa format username
            $hasil['username'] = array('tersedia' => false, 'pesan' => 'Format username tidak valid.'); // menolak format yang salah
        } else { // apabila format username benar
            $stmtUser = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username'); // menyiapkan query aman untuk username
            $stmtUser->execute(array(':username' => $username)); // menjalankan query username
            $jumlahUser = (int) $stmtUser->fetchColumn(); // menghitung jumlah username yang sudah dipakai
            $hasil['username'] = $jumlahUser === 0 ? array('tersedia' => true, 'pesan' => 'Username tersedia.') : array('tersedia' => false, 'pesan' => 'Username sudah dipakai.'); // menentukan hasil ketersediaan username
        } // menutup pengecekan format username
    } // menutup pengecekan username
    if ($email !== '') { // mengecek apabila email dikirim
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { // memeriksa format email
            $hasil['email'] = array('tersedia' => false, 'pesan' => 'Format email tidak valid.'); // menolak format yang salah
        } else { // apabila format email benar
            $stmtEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email'); // menyiapkan query aman untuk email
            $stmtEmail->execute(array(':email' => $email)); // menjalankan query email
            $jumlahEmail = (int) $stmtEmail->fetchColumn(); // menghitung jumlah email yang sudah dipakai
            $hasil['email'] = $jumlahEmail === 0 ? array('tersedia' => true, 'pesan' => 'Email tersedia.') : array('tersedia' => false, 'pesan' => 'Email sudah dipakai.'); // menentukan hasil ketersediaan email
        } // menutup pengecekan format email
    } // menutup pengecekan email
} catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
    error_log('Gagal cek user: ' . $e->getMessage()); // menyimpan detail error ke log server
    $statusKode = 500; // mengubah kode status menjadi kesalahan server
    $hasil = array('username' => array('tersedia' => false, 'pesan' => 'Pemeriksaan sementara gagal.'), 'email' => array('tersedia' => false, 'pesan' => 'Pemeriksaan sementara gagal.')); // memberi pesan umum yang aman
} // menutup percobaan pemeriksaan database
http_response_code($statusKode); // mengirim kode status akhir
echo json_encode($hasil, JSON_UNESCAPED_UNICODE); // mencetak jawaban JSON akhir
exit; // menghentikan kode setelah keluaran JSON
