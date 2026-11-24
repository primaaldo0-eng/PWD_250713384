<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
$statusDiizinkan = array('pending', 'dikonfirmasi', 'selesai', 'dibatalkan'); // daftar status yang bisa difilter
$status = isset($_GET['status']) ? (string)$_GET['status'] : ''; // membaca filter status dari URL
if (!in_array($status, $statusDiizinkan, true)) { // memastikan filter valid
    $status = ''; // mengosongkan filter yang tidak dikenal
} // menutup pengecekan filter
$where = ' WHERE r.user_id = :user'; // memulai klausa query dengan pembatas pengguna
$params = array(':user' => $_SESSION['user_id']); // menyiapkan parameter query
if ($status !== '') { // menambahkan filter status bila dipilih
    $where .= ' AND r.status = :status'; // menambah klausa status
    $params[':status'] = $status; // menambah parameter status
} // menutup pengecekan filter
$stmt = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.total_hari, r.total_harga, r.status, r.lokasi_jemput, c.nama AS nama_mobil, c.gambar AS gambar_mobil FROM rentals r JOIN cars c ON c.id = r.car_id' . $where . ' ORDER BY r.id DESC'); // menyiapkan query riwayat
$stmt->execute($params); // menjalankan query riwayat
$daftarRental = $stmt->fetchAll(); // mengambil semua rental pengguna
$judul = 'Riwayat Sewa'; // menentukan judul halaman
$halamanAktif = 'riwayat'; // menandai menu riwayat
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul riwayat -->
    <div class="container"> <!-- wadah judul -->
      <h1>Riwayat Sewa</h1> <!-- judul halaman -->
      <p>Pantau semua pengajuan sewa dan status terbarunya.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian tabel riwayat -->
    <div class="container"> <!-- wadah tabel riwayat -->
      <div class="admin-toolbar"> <!-- bilah filter riwayat -->
        <form method="get" action="/rental-mobil/user/riwayat.php" class="pencarian"> <!-- formulir filter status -->
          <label for="status" class="label-form">Status:</label> <!-- label filter -->
          <select id="status" name="status" class="form-kolom"> <!-- pilihan status -->
            <option value="">Semua</option> <!-- opsi tanpa filter -->
            <?php foreach ($statusDiizinkan as $opsi) { ?> <!-- mengulang status -->
              <option value="<?php echo e($opsi); ?>" <?php if ($status === $opsi) { ?>selected="selected"<?php } ?>><?php echo e(label_status_rental($opsi)); ?></option> <!-- satu opsi status -->
            <?php } ?> <!-- menutup perulangan status -->
          </select> <!-- menutup pilihan status -->
          <input type="submit" value="Filter" class="tombol" /> <!-- tombol menerapkan filter -->
        </form> <!-- menutup formulir filter -->
        <a href="/rental-mobil/katalog.php" class="tombol">Sewa Mobil Lain</a> <!-- tautan ke katalog -->
      </div> <!-- menutup bilah filter -->
      <div class="tabel-wrapper"> <!-- pembungkus tabel -->
        <table class="tabel-admin"> <!-- tabel riwayat rental -->
          <thead> <!-- kepala tabel -->
            <tr> <!-- baris header -->
              <th>ID</th> <!-- kolom ID -->
              <th>Mobil</th> <!-- kolom mobil -->
              <th>Tanggal</th> <!-- kolom jadwal -->
              <th>Total</th> <!-- kolom total -->
              <th>Status</th> <!-- kolom status -->
              <th>Aksi</th> <!-- kolom aksi -->
            </tr> <!-- menutup baris header -->
          </thead> <!-- menutup kepala tabel -->
          <tbody> <!-- isi tabel -->
            <?php if (count($daftarRental) === 0) { ?> <!-- pesan bila kosong -->
              <tr><td colspan="6" class="kosong">Belum ada data penyewaan.</td></tr> <!-- baris kosong -->
            <?php } else { ?> <!-- mulai menampilkan data -->
              <?php foreach ($daftarRental as $rental) { ?> <!-- mengulang tiap rental -->
                <tr> <!-- membuka baris rental -->
                  <td>#<?php echo (int)$rental['id']; ?></td> <!-- menampilkan ID -->
                  <td><img src="<?php echo e(BASE_URL . $rental['gambar_mobil']); ?>" alt="Foto <?php echo e($rental['nama_mobil']); ?>" class="foto-tabel" width="70" height="50" /><br /><?php echo e($rental['nama_mobil']); ?></td> <!-- menampilkan foto dan nama mobil -->
                  <td><?php echo e($rental['tanggal_mulai']); ?><br /><?php echo e($rental['tanggal_selesai']); ?></td> <!-- menampilkan tanggal sewa -->
                  <td><?php echo e(rupiah($rental['total_harga'])); ?><br /><small><?php echo (int)$rental['total_hari']; ?> hari</small></td> <!-- menampilkan total -->
                  <td><span class="label-status status-<?php echo e($rental['status']); ?>"><?php echo e(label_status_rental($rental['status'])); ?></span></td> <!-- menampilkan status -->
                  <td> <!-- membuka sel aksi -->
                    <div class="aksi-tabel"> <!-- wadah tombol aksi -->
                      <a href="/rental-mobil/user/rental_detail.php?id=<?php echo (int)$rental['id']; ?>" class="tombol tombol-outline tombol-kecil">Detail</a> <!-- tautan detail -->
                      <?php if ($rental['status'] === 'pending' || $rental['status'] === 'dikonfirmasi') { ?> <!-- aksi untuk rental aktif -->
                        <a href="/rental-mobil/user/rental_ubah.php?id=<?php echo (int)$rental['id']; ?>" class="tombol tombol-outline tombol-kecil">Ubah Tanggal</a> <!-- tautan ubah jadwal -->
                        <form method="post" action="/rental-mobil/user/rental_batal.php" class="form-konfirmasi"> <!-- formulir pembatalan -->
                          <?php echo csrf_field(); ?> <!-- token CSRF -->
                          <input type="hidden" name="id" value="<?php echo (int)$rental['id']; ?>" /> <!-- ID rental -->
                          <input type="hidden" name="pesan" value="Yakin ingin membatalkan sewa #<?php echo (int)$rental['id']; ?>?" class="pesan-konfirmasi" /> <!-- pesan konfirmasi -->
                          <input type="submit" value="Batal" class="tombol tombol-bahaya tombol-kecil" /> <!-- tombol batal -->
                        </form> <!-- menutup formulir pembatalan -->
                      <?php } ?> <!-- menutup pengecekan rental aktif -->
                    </div> <!-- menutup wadah aksi -->
                  </td> <!-- menutup sel aksi -->
                </tr> <!-- menutup baris rental -->
              <?php } ?> <!-- menutup perulangan rental -->
            <?php } ?> <!-- menutup pengecekan data -->
          </tbody> <!-- menutup isi tabel -->
        </table> <!-- menutup tabel riwayat -->
      </div> <!-- menutup pembungkus tabel -->
      <script type="text/javascript" src="/rental-mobil/assets/js/konfirmasi.js"></script> <!-- memuat konfirmasi pembatalan -->
    </div> <!-- menutup wadah tabel riwayat -->
  </div> <!-- menutup bagian tabel riwayat -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
