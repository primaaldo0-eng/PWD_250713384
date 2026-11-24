<?php // membuka PHP sebelum ada output apa pun
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan memulai session
require_once __DIR__ . '/fungsi.php'; // mengambil fungsi escape, CSRF, dan flash
$judulHalaman = isset($judul) ? $judul . ' - Rental Mobil' : 'Rental Mobil'; // menentukan judul tab browser
$halamanAktif = isset($halamanAktif) ? $halamanAktif : ''; // menentukan menu yang sedang aktif
$extraHead = isset($extraHead) ? $extraHead : ''; // menyiapkan tautan tambahan untuk bagian kepala halaman
$base = e(BASE_URL); // menyiapkan URL dasar yang aman untuk XHTML
$urlLogo = e(BASE_URL . 'assets/img/logo/logo-full.png'); // menyiapkan alamat logo utama
$navBeranda = ($halamanAktif === 'beranda') ? ' class="active"' : ''; // menandai menu beranda
$navKatalog = ($halamanAktif === 'katalog' || $halamanAktif === 'detail') ? ' class="active"' : ''; // menandai menu katalog dan detail
$isLogin = !empty($_SESSION['user_id']); // mengecek status login pengunjung
$navUser = null; // menyiapkan data pengguna untuk navbar
if ($isLogin) { // mengambil data pengguna jika sudah login
    $stmtNav = $pdo->prepare('SELECT id, username, nama_lengkap, foto, role FROM users WHERE id = :id LIMIT 1'); // menyiapkan query aman untuk pengguna
    $stmtNav->execute(array(':id' => $_SESSION['user_id'])); // menjalankan query dengan id dari session
    $navUser = $stmtNav->fetch(); // menyimpan baris data pengguna
    if (!$navUser) { // mengecek apakah data pengguna tidak ditemukan
        session_destroy(); // menghancurkan session yang tidak valid
        $isLogin = false; // mengembalikan status menjadi tamu
    } // menutup pengecekan data pengguna
} // menutup blok pengambilan data pengguna
$isAdmin = $isLogin && $navUser['role'] === 'admin'; // mengecek apakah perannya admin
$urlFoto = $isLogin ? e(BASE_URL . $navUser['foto']) : ''; // menyiapkan alamat foto profil kecil
$flash = ambil_flash(); // mengambil pesan sementara jika ada
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <!-- menutup PHP dan deklarasi tipe dokumen XHTML 1.0 Transitional -->
<html xmlns="http://www.w3.org/1999/xhtml" lang="id" xml:lang="id"> <!-- elemen akar dokumen XHTML -->
<head> <!-- bagian kepala dokumen -->
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> <!-- menentukan charset UTF-8 -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <!-- mengatur tampilan pada layar mobile -->
  <title><?php echo $judulHalaman; // menampilkan judul halaman ?></title> <!-- judul yang tampil di tab browser -->
  <link rel="icon" type="image/png" href="<?php echo e(BASE_URL . 'assets/img/logo/logo-vector.png'); // alamat favicon ?>" /> <!-- ikon kecil di tab -->
  <link rel="stylesheet" type="text/css" href="<?php echo e(BASE_URL . 'assets/css/style.css'); // alamat stylesheet ?>" /> <!-- memuat CSS eksternal -->
  <?php echo $extraHead; // memuat stylesheet tambahan seperti Leaflet ?> <!-- menutup bagian kepala tambahan -->
</head> <!-- menutup bagian kepala dokumen -->
<body> <!-- badan dokumen -->
  <div id="wrapper"> <!-- pembungkus seluruh halaman -->
    <div class="navbar"> <!-- bilah navigasi atas -->
      <div class="container navbar-inner"> <!-- wadah isi navbar -->
        <a href="<?php echo $base; // alamat beranda ?>index.php" class="brand"> <!-- tautan logo ke beranda -->
          <img src="<?php echo $urlLogo; // alamat logo ?>" alt="Logo Rental Mobil" width="160" height="44" /> <!-- logo perusahaan -->
        </a> <!-- menutup tautan logo -->
        <button type="button" id="btn-menu" class="btn-menu">Menu</button> <!-- tombol membuka menu di layar kecil -->
        <div id="menu" class="menu"> <!-- daftar menu navigasi -->
          <a href="<?php echo $base; // alamat beranda ?>index.php"<?php echo $navBeranda; // kelas aktif ?>>Beranda</a> <!-- menu beranda -->
          <a href="<?php echo $base; // alamat katalog ?>katalog.php"<?php echo $navKatalog; // kelas aktif ?>>Katalog</a> <!-- menu katalog -->
          <?php if ($isLogin) { // cabang untuk pengguna yang sudah login ?> <!-- awal menu pengguna login -->
            <?php if ($isAdmin) { // cabang khusus admin ?> <!-- awal menu admin -->
              <a href="<?php echo $base; // alamat halaman admin ?>admin/index.php">Admin</a> <!-- menu dashboard admin -->
              <a href="<?php echo $base; // alamat rental admin ?>admin/rental.php">Rental</a> <!-- menu kelola rental admin -->
              <a href="<?php echo $base; // alamat pengguna admin ?>admin/user.php">Pengguna</a> <!-- menu kelola pengguna admin -->
            <?php } // menutup cabang admin ?> <!-- akhir menu admin -->
            <a href="<?php echo $base; // alamat dashboard ?>user/dashboard.php">Dashboard</a> <!-- menu dashboard user -->
            <a href="<?php echo $base; // alamat riwayat ?>user/riwayat.php">Riwayat Sewa</a> <!-- menu riwayat rental user -->
            <a href="<?php echo $base; // alamat profil ?>user/profil.php">Profil</a> <!-- menu profil -->
            <form method="post" action="<?php echo $base; // alamat logout ?>auth/logout.php" class="form-logout"> <!-- formulir logout dengan token CSRF -->
              <?php echo csrf_field(); // token CSRF logout ?>
              <input type="submit" value="<?php echo e($navUser['nama_lengkap']); // nama pengguna ?>" class="menu-logout" /> <!-- tombol logout bertuliskan nama pengguna -->
            </form> <!-- menutup formulir logout -->
            <img src="<?php echo $urlFoto; // alamat foto profil ?>" alt="Foto profil" width="30" height="30" class="foto-user" /> <!-- foto profil kecil -->
          <?php } else { // cabang untuk tamu ?> <!-- awal menu tamu -->
            <a href="<?php echo $base; // alamat login ?>auth/login.php">Login</a> <!-- menu login -->
            <a href="<?php echo $base; // alamat pendaftaran ?>auth/daftar.php" class="tombol-kecil">Daftar</a> <!-- menu registrasi -->
          <?php } // menutup cabang status login ?> <!-- akhir pilihan menu -->
        </div> <!-- menutup daftar menu -->
      </div> <!-- menutup wadah navbar -->
    </div> <!-- menutup navbar -->
    <?php if ($flash) { // mengecek apakah ada pesan sementara ?> <!-- awal area pesan -->
      <div class="flash flash-<?php echo e($flash['jenis']); // jenis pesan ?>"><?php echo e($flash['pesan']); // isi pesan ?></div> <!-- pesan sukses atau gagal -->
    <?php } // menutup pengecekan pesan ?> <!-- akhir area pesan -->
    <div class="konten"> <!-- area konten utama halaman -->
