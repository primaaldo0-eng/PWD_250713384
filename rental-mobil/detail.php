<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/includes/fungsi.php'; // mengambil fungsi escape, flash, dan redirect
$idMobil = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0; // membaca dan memvalidasi id mobil
if ($idMobil <= 0) { // mengecek apakah id tidak valid
    set_flash('gagal', 'Mobil yang Anda cari tidak ditemukan.'); // menyimpan pesan gagal
    redirect(BASE_URL . 'katalog.php'); // mengarahkan kembali ke katalog
} // menutup pengecekan id
$stmt = $pdo->prepare('SELECT * FROM cars WHERE id = :id LIMIT 1'); // menyiapkan query detail mobil
$stmt->execute(array(':id' => $idMobil)); // menjalankan query dengan id mobil
$mobil = $stmt->fetch(); // mengambil data mobil
if (!$mobil) { // mengecek apakah mobil tidak ada
    set_flash('gagal', 'Mobil yang Anda cari tidak ditemukan.'); // menyimpan pesan gagal
    redirect(BASE_URL . 'katalog.php'); // mengarahkan kembali ke katalog
} // menutup pengecekan data mobil
$judul = $mobil['nama']; // menjadikan nama mobil sebagai judul halaman
$halamanAktif = 'detail'; // menandai menu katalog sebagai aktif
$isLogin = !empty($_SESSION['user_id']); // mengecek status login untuk tautan sewa
$target = 'detail.php?id=' . $mobil['id']; // menyiapkan halaman tujuan setelah login
$urlSewa = $isLogin ? BASE_URL . 'user/sewa.php?car_id=' . $mobil['id'] : BASE_URL . 'auth/login.php?redirect=' . urlencode($target); // mengarahkan tamu ke login dengan tujuan kembali ke mobil ini
$urlGambar = e(BASE_URL . $mobil['gambar']); // menyiapkan alamat foto mobil
$namaMobil = e($mobil['nama']); // menyiapkan nama mobil yang aman
$harga = e(number_format($mobil['harga_per_hari'], 0, ',', '.')); // menyiapkan harga yang aman
$deskripsi = e($mobil['deskripsi'] !== null && $mobil['deskripsi'] !== '' ? $mobil['deskripsi'] : 'Deskripsi belum tersedia.'); // menyiapkan deskripsi yang aman
require_once __DIR__ . '/includes/header.php'; // memuat kepala halaman dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-detail"> <!-- area judul detail -->
    <div class="container"> <!-- wadah judul detail -->
      <h1>Detail Mobil</h1> <!-- judul halaman detail -->
      <p>Lihat spesifikasi lengkap sebelum memutuskan untuk menyewa.</p> <!-- deskripsi halaman detail -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul detail -->
  <div class="bagian"> <!-- bagian isi detail -->
    <div class="container detail-grid"> <!-- wadah dua kolom detail -->
      <div> <!-- kolom foto mobil -->
        <img src="<?php echo $urlGambar; // alamat foto mobil ?>" alt="Foto <?php echo $namaMobil; // nama mobil ?>" class="detail-foto" width="700" height="450" /> <!-- foto besar mobil -->
      </div> <!-- menutup kolom foto -->
      <div> <!-- kolom keterangan mobil -->
        <h2 class="judul-bagian"><?php echo $namaMobil; // nama mobil ?></h2> <!-- nama mobil sebagai judul -->
        <p class="harga">Rp <?php echo $harga; // harga per hari ?> / hari</p> <!-- harga sewa per hari -->
        <p><span class="label-status <?php echo e($mobil['status']); // status mobil ?>"><?php echo e($mobil['status']); // status mobil ?></span></p> <!-- label status mobil -->
        <table class="tabel"> <!-- tabel spesifikasi -->
          <tbody> <!-- isi tabel spesifikasi -->
            <tr><th>Merek</th><td><?php echo e($mobil['merek']); // merek mobil ?></td></tr> <!-- baris merek -->
            <tr><th>Model</th><td><?php echo e($mobil['model']); // model mobil ?></td></tr> <!-- baris model -->
            <tr><th>Tahun</th><td><?php echo e($mobil['tahun']); // tahun mobil ?></td></tr> <!-- baris tahun -->
            <tr><th>Warna</th><td><?php echo e($mobil['warna']); // warna mobil ?></td></tr> <!-- baris warna -->
            <tr><th>Plat Nomor</th><td><?php echo e($mobil['plat_nomor']); // plat nomor ?></td></tr> <!-- baris plat nomor -->
          </tbody> <!-- menutup isi tabel -->
        </table> <!-- menutup tabel spesifikasi -->
        <p><?php echo $deskripsi; // deskripsi mobil ?></p> <!-- deskripsi singkat mobil -->
        <a href="<?php echo e($urlSewa); // alamat tombol sewa ?>" class="tombol">Sewa Sekarang</a> <!-- tombol sewa atau login -->
        <a href="<?php echo e(BASE_URL . 'katalog.php'); // alamat katalog ?>" class="tombol tombol-outline">Kembali ke Katalog</a> <!-- tombol kembali -->
      </div> <!-- menutup kolom keterangan -->
    </div> <!-- menutup wadah dua kolom -->
  </div> <!-- menutup bagian isi detail -->
<?php require_once __DIR__ . '/includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
