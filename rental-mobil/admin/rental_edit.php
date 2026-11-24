<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
$statusDiizinkan = array('pending', 'dikonfirmasi', 'selesai', 'dibatalkan'); // daftar status rental
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID rental
$stmt = $pdo->prepare('SELECT r.id, r.status, r.catatan, r.tanggal_mulai, r.tanggal_selesai, r.total_hari, r.total_harga, r.lokasi_jemput, u.nama_lengkap AS nama_penyewa, c.id AS car_id, c.nama AS nama_mobil, c.plat_nomor FROM rentals r JOIN users u ON u.id = r.user_id JOIN cars c ON c.id = r.car_id WHERE r.id = :id LIMIT 1'); // menyiapkan query detail rental
$stmt->execute(array(':id' => $id)); // menjalankan query
$rental = $stmt->fetch(); // mengambil data rental
if (!$rental) { // memastikan rental ditemukan
    set_flash('gagal', 'Data rental tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar
} // menutup pengecekan rental
$errors = array(); // menyiapkan daftar error
$data = array( // menyiapkan nilai formulir
    'id' => (int)$rental['id'], // ID rental
    'status' => $rental['status'], // status saat ini
    'catatan' => $rental['catatan'] // catatan saat ini
); // menutup nilai formulir
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') { // memproses perubahan status
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
        $errors[] = 'Token keamanan tidak valid.'; // mencatat error CSRF
    } // menutup pengecekan token
    if ((int)input_post('id') !== (int)$rental['id']) { // memastikan ID tidak diubah
        $errors[] = 'ID rental tidak sesuai.'; // mencatat error ID
    } // menutup pengecekan ID
    $data['status'] = input_post('status'); // membaca status baru
    $data['catatan'] = input_post('catatan'); // membaca catatan baru
    if (!in_array($data['status'], $statusDiizinkan, true)) { // memvalidasi status
        $errors[] = 'Status tidak dikenal.'; // mencatat error status
    } // menutup pengecekan status
    if (count($errors) === 0) { // menyimpan perubahan bila valid
        try { // menjalankan query dalam pengaman exception
            $ubah = $pdo->prepare('UPDATE rentals SET status = :status, catatan = :catatan WHERE id = :id'); // menyiapkan query update
            $ubah->execute(array(':status' => $data['status'], ':catatan' => $data['catatan'], ':id' => (int)$rental['id'])); // menjalankan update status
            sinkronkan_status_mobil($pdo, (int)$rental['car_id']); // menyelaraskan status mobil
            set_flash('sukses', 'Status rental berhasil diperbarui.'); // menyimpan pesan sukses
            redirect(BASE_URL . 'admin/rental.php'); // kembali ke daftar rental
        } catch (PDOException $exception) { // menangani kegagalan database
            $errors[] = 'Gagal memperbarui status rental.'; // mencatat error database
        } // menutup pengaman exception
    } // menutup pengecekan penyimpanan
} // menutup pemrosesan POST
$judul = 'Kelola Rental #' . $rental['id']; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul kelola rental -->
    <div class="container"> <!-- wadah judul -->
      <h1>Kelola Rental #<?php echo (int)$rental['id']; ?></h1> <!-- judul halaman -->
      <p>Perbarui status transaksi dan catatan admin.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir status -->
    <div class="container rental-grid"> <!-- wadah dua kolom -->
      <div> <!-- kolom formulir -->
        <?php if (count($errors) > 0) { ?> <!-- menampilkan error -->
          <div class="flash flash-gagal"> <!-- kotak error -->
            <ul> <!-- daftar error -->
              <?php foreach ($errors as $error) { ?> <!-- mengulang error -->
                <li><?php echo e($error); ?></li> <!-- menampilkan error -->
              <?php } ?> <!-- menutup perulangan -->
            </ul> <!-- menutup daftar error -->
          </div> <!-- menutup kotak error -->
        <?php } ?> <!-- menutup pengecekan error -->
        <form method="post" action="/rental-mobil/admin/rental_edit.php?id=<?php echo (int)$rental['id']; ?>" class="form-kartu"> <!-- formulir ubah status -->
          <?php echo csrf_field(); ?> <!-- token CSRF -->
          <input type="hidden" name="id" value="<?php echo (int)$rental['id']; ?>" /> <!-- ID rental -->
          <table class="rental-info"> <!-- ringkasan rental -->
            <tbody> <!-- isi ringkasan -->
              <tr><th>Penyewa</th><td><?php echo e($rental['nama_penyewa']); ?></td></tr> <!-- penyewa -->
              <tr><th>Mobil</th><td><?php echo e($rental['nama_mobil']); ?> (<?php echo e($rental['plat_nomor']); ?>)</td></tr> <!-- mobil -->
              <tr><th>Jadwal</th><td><?php echo e($rental['tanggal_mulai']); ?> - <?php echo e($rental['tanggal_selesai']); ?></td></tr> <!-- jadwal -->
              <tr><th>Total</th><td><?php echo e(rupiah($rental['total_harga'])); ?> / <?php echo (int)$rental['total_hari']; ?> hari</td></tr> <!-- total -->
              <tr><th>Lokasi Jemput</th><td><?php echo e($rental['lokasi_jemput'] !== null && $rental['lokasi_jemput'] !== '' ? $rental['lokasi_jemput'] : '-'); ?></td></tr> <!-- lokasi -->
            </tbody> <!-- menutup isi ringkasan -->
          </table> <!-- menutup ringkasan -->
          <div class="form-baris"> <!-- baris status -->
            <label for="status" class="label-form">Status</label> <!-- label status -->
            <select id="status" name="status" class="form-kolom"> <!-- pilihan status -->
              <?php foreach ($statusDiizinkan as $opsi) { ?> <!-- mengulang status -->
                <option value="<?php echo e($opsi); ?>" <?php if ($data['status'] === $opsi) { ?>selected="selected"<?php } ?>><?php echo e(label_status_rental($opsi)); ?></option> <!-- satu opsi -->
              <?php } ?> <!-- menutup perulangan -->
            </select> <!-- menutup pilihan -->
          </div> <!-- menutup baris status -->
          <div class="form-baris"> <!-- baris catatan -->
            <label for="catatan" class="label-form">Catatan Admin</label> <!-- label catatan -->
            <textarea id="catatan" name="catatan" rows="4" class="form-kolom"><?php echo e($data['catatan']); ?></textarea> <!-- kolom catatan -->
          </div> <!-- menutup baris catatan -->
          <div class="form-bawah"> <!-- area tombol -->
            <input type="submit" value="Simpan Status" class="tombol" /> <!-- tombol simpan -->
            <a href="/rental-mobil/admin/rental.php" class="tombol tombol-outline">Kembali</a> <!-- tombol kembali -->
          </div> <!-- menutup area tombol -->
        </form> <!-- menutup formulir -->
      </div> <!-- menutup kolom formulir -->
      <div> <!-- kolom keterangan -->
        <h2 class="judul-bagian">Status Terkini</h2> <!-- judul status -->
        <p><span class="label-status status-<?php echo e($rental['status']); ?>"><?php echo e(label_status_rental($rental['status'])); ?></span></p> <!-- status saat ini -->
        <ul class="timeline"> <!-- daftar alur status -->
          <li class="timeline-item">Pengajuan dibuat oleh penyewa.</li> <!-- langkah pengajuan -->
          <li class="timeline-item">Admin mengubah status menjadi konfirmasi, selesai, atau dibatalkan.</li> <!-- langkah konfirmasi -->
          <li class="timeline-item">Status mobil mengikuti transaksi aktif.</li> <!-- langkah sinkronisasi -->
        </ul> <!-- menutup daftar alur -->
      </div> <!-- menutup kolom keterangan -->
    </div> <!-- menutup wadah dua kolom -->
  </div> <!-- menutup bagian formulir -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
