<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
$stmt = $pdo->prepare('SELECT COUNT(*) AS total, SUM(status IN (:s1, :s2)) AS aktif, SUM(status = :s3) AS selesai FROM rentals WHERE user_id = :user'); // menyiapkan query ringkasan rental pengguna
$stmt->execute(array(':s1' => 'pending', ':s2' => 'dikonfirmasi', ':s3' => 'selesai', ':user' => $_SESSION['user_id'])); // menjalankan query ringkasan
$ringkasan = $stmt->fetch(); // mengambil hasil ringkasan
$total = (int)$ringkasan['total']; // membaca total seluruh rental
$aktif = (int)$ringkasan['aktif']; // membaca jumlah rental aktif
$selesai = (int)$ringkasan['selesai']; // membaca jumlah rental selesai
$judul = 'Dashboard'; // menentukan judul halaman
$halamanAktif = 'dashboard'; // menandai menu dashboard
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul dashboard -->
    <div class="container"> <!-- wadah judul -->
      <h1>Halo, <?php echo e($userLogin['nama_lengkap']); ?></h1> <!-- menyapa pengguna yang login -->
      <p>Kelola penyewaan dan lihat status terkini dari sini.</p> <!-- deskripsi dashboard -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian isi dashboard -->
    <div class="container"> <!-- wadah isi dashboard -->
      <div class="ringkasan-admin"> <!-- kumpulan kartu ringkasan pengguna -->
        <div class="kartu-ringkasan"> <!-- kartu total rental -->
          <h2><?php echo $total; ?></h2> <!-- angka total rental -->
          <p>Total Penyewaan</p> <!-- keterangan total rental -->
          <a href="/rental-mobil/user/riwayat.php">Lihat riwayat</a> <!-- tautan ke riwayat -->
        </div> <!-- menutup kartu total -->
        <div class="kartu-ringkasan"> <!-- kartu rental aktif -->
          <h2><?php echo $aktif; ?></h2> <!-- angka rental aktif -->
          <p>Sedang Aktif</p> <!-- keterangan rental aktif -->
          <a href="/rental-mobil/user/riwayat.php">Lihat status</a> <!-- tautan ke status aktif -->
        </div> <!-- menutup kartu aktif -->
        <div class="kartu-ringkasan"> <!-- kartu rental selesai -->
          <h2><?php echo $selesai; ?></h2> <!-- angka rental selesai -->
          <p>Penyewaan Selesai</p> <!-- keterangan rental selesai -->
          <a href="/rental-mobil/katalog.php">Sewa mobil lagi</a> <!-- tautan ke katalog -->
        </div> <!-- menutup kartu selesai -->
      </div> <!-- menutup kumpulan kartu ringkasan -->
      <div class="daftar-aksi"> <!-- kumpulan aksi cepat -->
        <a href="/rental-mobil/katalog.php" class="tombol">Jelajahi Katalog</a> <!-- tombol ke katalog -->
        <a href="/rental-mobil/user/profil.php" class="tombol tombol-outline">Kelola Profil</a> <!-- tombol ke profil -->
      </div> <!-- menutup kumpulan aksi cepat -->
    </div> <!-- menutup wadah isi dashboard -->
  </div> <!-- menutup bagian isi dashboard -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
