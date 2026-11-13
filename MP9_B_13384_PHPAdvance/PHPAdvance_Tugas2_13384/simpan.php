<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $judul   = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $tahun   = trim($_POST['tahun'] ?? '');

    if ($judul === '' || $penulis === '' || $tahun === '') {
        echo '<p style="color:red;">!! Harap lengkapi semua data. !!</p>';

    } elseif (!preg_match('/^\d{4}$/', $tahun)) {
        echo '<p style="color:red;">!! Tahun harus berupa 4 digit angka. !!</p>';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            'INSERT INTO buku (judul, penulis, tahun) VALUES (?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'sss', $judul, $penulis, $tahun);
        mysqli_stmt_execute($stmt);

        echo '<p>Data berhasil disimpan ('
            . mysqli_stmt_affected_rows($stmt) . ' baris).</p>';

        echo '<p><a href="index.xhtml">Kembali</a></p>';
    }
} else {
    echo '<p>Silakan isi formulir terlebih dahulu.</p>';
    echo '<a href="index.xhtml">Kembali</a>';
}
?>
