<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
$statusDiizinkan = array('pending', 'dikonfirmasi', 'selesai', 'dibatalkan'); // daftar status rental
$status = isset($_GET['status']) ? (string)$_GET['status'] : ''; // membaca filter status
if (!in_array($status, $statusDiizinkan, true)) { // memvalidasi filter
    $status = ''; // mengosongkan filter tidak valid
} // menutup pengecekan filter
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : ''; // membaca kata kunci pencarian
$where = ' WHERE 1 = 1'; // memulai klausa query
$params = array(); // menyiapkan parameter query
if ($status !== '') { // menambah filter status
    $where .= ' AND r.status = :status'; // menambah klausa status
    $params[':status'] = $status; // menambah parameter status
} // menutup pengecekan status
if ($q !== '') { // menambah pencarian teks
    $where .= ' AND (u.nama_lengkap LIKE :q1 OR c.nama LIKE :q2 OR c.plat_nomor LIKE :q3)'; // mencari di nama user, mobil, dan plat
    $pola = '%' . $q . '%'; // membuat pola pencarian
    $params[':q1'] = $pola; // parameter pertama
    $params[':q2'] = $pola; // parameter kedua
    $params[':q3'] = $pola; // parameter ketiga
} // menutup pengecekan pencarian
$stmt = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.total_hari, r.total_harga, r.status, r.lokasi_jemput, u.nama_lengkap, c.nama AS nama_mobil FROM rentals r JOIN users u ON u.id = r.user_id JOIN cars c ON c.id = r.car_id' . $where . ' ORDER BY r.id DESC'); // menyiapkan query daftar rental
$stmt->execute($params); // menjalankan query daftar rental
$daftarRental = $stmt->fetchAll(); // mengambil hasil
$judul = 'Kelola Rental'; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul kelola rental -->
    <div class="container"> <!-- wadah judul -->
      <h1>Kelola Data Rental</h1> <!-- judul halaman -->
      <p>Konfirmasi pengajuan, pantau status, dan hapus transaksi.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian tabel rental -->
    <div class="container"> <!-- wadah tabel rental -->
      <div class="admin-toolbar"> <!-- bilah pencarian dan filter -->
        <form method="get" action="/rental-mobil/admin/rental.php" class="pencarian"> <!-- formulir pencarian -->
          <label for="q" class="label-form">Cari:</label> <!-- label kata kunci -->
          <input type="text" id="q" name="q" value="<?php echo e($q); ?>" class="form-kolom" /> <!-- kolom kata kunci -->
          <label for="status" class="label-form">Status:</label> <!-- label status -->
          <select id="status" name="status" class="form-kolom"> <!-- pilihan status -->
            <option value="">Semua</option> <!-- opsi semua -->
            <?php foreach ($statusDiizinkan as $opsi) { ?> <!-- mengulang status -->
              <option value="<?php echo e($opsi); ?>" <?php if ($status === $opsi) { ?>selected="selected"<?php } ?>><?php echo e(label_status_rental($opsi)); ?></option> <!-- satu opsi -->
            <?php } ?> <!-- menutup perulangan status -->
          </select> <!-- menutup pilihan status -->
          <input type="submit" value="Filter" class="tombol" /> <!-- tombol filter -->
        </form> <!-- menutup formulir pencarian -->
        <a href="/rental-mobil/admin/rental.php" class="tombol tombol-outline">Reset</a> <!-- tautan reset filter -->
      </div> <!-- menutup bilah pencarian -->
      <div class="tabel-wrapper"> <!-- pembungkus tabel -->
        <table class="tabel-admin"> <!-- tabel daftar rental -->
          <thead> <!-- kepala tabel -->
            <tr> <!-- baris header -->
              <th>ID</th> <!-- kolom ID -->
              <th>Penyewa</th> <!-- kolom penyewa -->
              <th>Mobil</th> <!-- kolom mobil -->
              <th>Jadwal</th> <!-- kolom jadwal -->
              <th>Total</th> <!-- kolom total -->
              <th>Lokasi</th> <!-- kolom lokasi -->
              <th>Status</th> <!-- kolom status -->
              <th>Aksi</th> <!-- kolom aksi -->
            </tr> <!-- menutup baris header -->
          </thead> <!-- menutup kepala tabel -->
          <tbody> <!-- isi tabel -->
            <?php if (count($daftarRental) === 0) { ?> <!-- pesan kosong -->
              <tr><td colspan="8" class="kosong">Belum ada data rental.</td></tr> <!-- baris kosong -->
            <?php } else { ?> <!-- mulai data -->
              <?php foreach ($daftarRental as $rental) { ?> <!-- mengulang rental -->
                <tr> <!-- membuka baris -->
                  <td>#<?php echo (int)$rental['id']; ?></td> <!-- ID -->
                  <td><?php echo e($rental['nama_lengkap']); ?></td> <!-- penyewa -->
                  <td><?php echo e($rental['nama_mobil']); ?></td> <!-- mobil -->
                  <td><?php echo e($rental['tanggal_mulai']); ?><br /><?php echo e($rental['tanggal_selesai']); ?></td> <!-- jadwal -->
                  <td><?php echo e(rupiah($rental['total_harga'])); ?><br /><small><?php echo (int)$rental['total_hari']; ?> hari</small></td> <!-- total -->
                  <td><?php echo e($rental['lokasi_jemput'] !== null && $rental['lokasi_jemput'] !== '' ? $rental['lokasi_jemput'] : '-'); ?></td> <!-- lokasi -->
                  <td><span class="label-status status-<?php echo e($rental['status']); ?>"><?php echo e(label_status_rental($rental['status'])); ?></span></td> <!-- status -->
                  <td> <!-- sel aksi -->
                    <div class="aksi-tabel"> <!-- wadah tombol -->
                      <a href="/rental-mobil/admin/rental_edit.php?id=<?php echo (int)$rental['id']; ?>" class="tombol tombol-outline tombol-kecil">Kelola</a> <!-- tautan edit status -->
                      <form method="post" action="/rental-mobil/admin/rental_hapus.php" class="form-konfirmasi"> <!-- formulir hapus -->
                        <?php echo csrf_field(); ?> <!-- token CSRF -->
                        <input type="hidden" name="id" value="<?php echo (int)$rental['id']; ?>" /> <!-- ID rental -->
                        <input type="hidden" name="pesan" value="Yakin ingin menghapus rental #<?php echo (int)$rental['id']; ?>?" class="pesan-konfirmasi" /> <!-- pesan konfirmasi -->
                        <input type="submit" value="Hapus" class="tombol tombol-bahaya tombol-kecil" /> <!-- tombol hapus -->
                      </form> <!-- menutup formulir hapus -->
                    </div> <!-- menutup wadah tombol -->
                  </td> <!-- menutup sel aksi -->
                </tr> <!-- menutup baris -->
              <?php } ?> <!-- menutup perulangan -->
            <?php } ?> <!-- menutup pengecekan data -->
          </tbody> <!-- menutup isi tabel -->
        </table> <!-- menutup tabel -->
      </div> <!-- menutup pembungkus tabel -->
      <script type="text/javascript" src="/rental-mobil/assets/js/konfirmasi.js"></script> <!-- memuat konfirmasi hapus -->
    </div> <!-- menutup wadah tabel -->
  </div> <!-- menutup bagian tabel rental -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
