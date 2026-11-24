<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID rental dari URL
$stmt = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.lokasi_jemput, r.latitude, r.longitude, r.total_hari, r.total_harga, r.status, r.catatan, r.created_at, r.updated_at, c.id AS car_id, c.nama AS nama_mobil, c.gambar AS gambar_mobil, c.plat_nomor, u.nama_lengkap FROM rentals r JOIN cars c ON c.id = r.car_id JOIN users u ON u.id = r.user_id WHERE r.id = :id AND r.user_id = :user LIMIT 1'); // menyiapkan query detail rental milik pengguna
$stmt->execute(array(':id' => $id, ':user' => $_SESSION['user_id'])); // menjalankan query dengan pembatas pengguna
$rental = $stmt->fetch(); // mengambil data rental
if (!$rental) { // memastikan rental ditemukan dan milik pengguna
    set_flash('gagal', 'Data rental tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
} // menutup pengecekan rental
$extraHead = '<link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />'; // memuat stylesheet Leaflet
$judul = 'Detail Rental #' . $rental['id']; // menentukan judul halaman
$halamanAktif = 'riwayat'; // menandai menu riwayat
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul detail rental -->
    <div class="container"> <!-- wadah judul -->
      <h1>Detail Rental #<?php echo (int)$rental['id']; ?></h1> <!-- judul halaman -->
      <p>Informasi lengkap penyewaan dan lokasi jemput.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian isi detail rental -->
    <div class="container rental-grid"> <!-- wadah dua kolom detail -->
      <div> <!-- kolom informasi utama -->
        <img src="<?php echo e(BASE_URL . $rental['gambar_mobil']); ?>" alt="Foto <?php echo e($rental['nama_mobil']); ?>" class="detail-foto" width="700" height="450" /> <!-- foto mobil -->
        <h2 class="judul-bagian"><?php echo e($rental['nama_mobil']); ?></h2> <!-- nama mobil -->
        <p><span class="label-status status-<?php echo e($rental['status']); ?>"><?php echo e(label_status_rental($rental['status'])); ?></span></p> <!-- status rental -->
        <table class="rental-info"> <!-- tabel informasi rental -->
          <tbody> <!-- isi tabel informasi -->
            <tr><th>Plat Nomor</th><td><?php echo e($rental['plat_nomor']); ?></td></tr> <!-- baris plat -->
            <tr><th>Penyewa</th><td><?php echo e($rental['nama_lengkap']); ?></td></tr> <!-- baris penyewa -->
            <tr><th>Tanggal Mulai</th><td><?php echo e($rental['tanggal_mulai']); ?></td></tr> <!-- baris tanggal mulai -->
            <tr><th>Tanggal Selesai</th><td><?php echo e($rental['tanggal_selesai']); ?></td></tr> <!-- baris tanggal selesai -->
            <tr><th>Lama Sewa</th><td><?php echo (int)$rental['total_hari']; ?> hari</td></tr> <!-- baris lama sewa -->
            <tr><th>Total Harga</th><td><?php echo e(rupiah($rental['total_harga'])); ?></td></tr> <!-- baris total -->
            <tr><th>Lokasi Jemput</th><td><?php echo e($rental['lokasi_jemput'] !== null && $rental['lokasi_jemput'] !== '' ? $rental['lokasi_jemput'] : '-'); ?></td></tr> <!-- baris lokasi -->
            <tr><th>Catatan</th><td><?php echo e($rental['catatan'] !== null && $rental['catatan'] !== '' ? $rental['catatan'] : '-'); ?></td></tr> <!-- baris catatan -->
          </tbody> <!-- menutup isi tabel informasi -->
        </table> <!-- menutup tabel informasi -->
        <div class="daftar-aksi"> <!-- kumpulan aksi rental -->
          <?php if ($rental['status'] === 'pending' || $rental['status'] === 'dikonfirmasi') { ?> <!-- aksi hanya untuk rental aktif -->
            <a href="/rental-mobil/user/rental_ubah.php?id=<?php echo (int)$rental['id']; ?>" class="tombol">Ubah Tanggal</a> <!-- tombol ubah tanggal -->
            <form method="post" action="/rental-mobil/user/rental_batal.php" class="form-konfirmasi"> <!-- formulir batal -->
              <?php echo csrf_field(); ?> <!-- token CSRF -->
              <input type="hidden" name="id" value="<?php echo (int)$rental['id']; ?>" /> <!-- ID rental -->
              <input type="hidden" name="pesan" value="Yakin ingin membatalkan sewa #<?php echo (int)$rental['id']; ?>?" class="pesan-konfirmasi" /> <!-- pesan konfirmasi -->
              <input type="submit" value="Batalkan Sewa" class="tombol tombol-bahaya" /> <!-- tombol batal -->
            </form> <!-- menutup formulir batal -->
          <?php } ?> <!-- menutup pengecekan rental aktif -->
          <a href="/rental-mobil/user/riwayat.php" class="tombol tombol-outline">Kembali</a> <!-- tombol kembali -->
        </div> <!-- menutup kumpulan aksi -->
      </div> <!-- menutup kolom informasi utama -->
      <div> <!-- kolom peta dan riwayat -->
        <h2 class="judul-bagian">Lokasi Jemput</h2> <!-- judul peta -->
        <input type="hidden" id="lat-detail" value="<?php echo e($rental['latitude']); ?>" /> <!-- menyimpan latitude untuk JS -->
        <input type="hidden" id="lng-detail" value="<?php echo e($rental['longitude']); ?>" /> <!-- menyimpan longitude untuk JS -->
        <div id="peta-detail" class="peta peta-kecil"></div> <!-- wadah peta Leaflet -->
        <p id="info-peta" class="info-koordinat">Menyiapkan peta lokasi jemput.</p> <!-- keterangan peta -->
        <h2 class="judul-bagian">Riwayat Status</h2> <!-- judul riwayat status -->
        <ul class="timeline"> <!-- daftar riwayat status -->
          <li class="timeline-item">Pengajuan dibuat<span class="timeline-tanggal"><?php echo e($rental['created_at']); ?></span></li> <!-- entri dibuat -->
          <li class="timeline-item">Status terkini: <?php echo e(label_status_rental($rental['status'])); ?><span class="timeline-tanggal"><?php echo e($rental['updated_at']); ?></span></li> <!-- entri status -->
        </ul> <!-- menutup daftar riwayat -->
      </div> <!-- menutup kolom peta dan riwayat -->
    </div> <!-- menutup wadah dua kolom -->
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> <!-- memuat pustaka Leaflet -->
    <script type="text/javascript" src="/rental-mobil/assets/js/peta-detail.js"></script> <!-- memuat logika peta detail -->
    <script type="text/javascript" src="/rental-mobil/assets/js/konfirmasi.js"></script> <!-- memuat konfirmasi pembatalan -->
  </div> <!-- menutup bagian isi detail rental -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
