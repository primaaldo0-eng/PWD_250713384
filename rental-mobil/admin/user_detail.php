<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID pengguna
$stmt = $pdo->prepare('SELECT id, username, nama_lengkap, email, foto, role, no_telepon, alamat, created_at, updated_at FROM users WHERE id = :id LIMIT 1'); // menyiapkan query detail pengguna
$stmt->execute(array(':id' => $id)); // menjalankan query
$orang = $stmt->fetch(); // mengambil data pengguna
if (!$orang) { // memastikan pengguna ditemukan
    set_flash('gagal', 'Data pengguna tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/user.php'); // kembali ke daftar pengguna
} // menutup pengecekan pengguna
$stmtStat = $pdo->prepare('SELECT COUNT(*) AS total, SUM(status IN (:s1, :s2)) AS aktif, SUM(status = :s3) AS selesai, COALESCE(SUM(total_harga), 0) AS nominal FROM rentals WHERE user_id = :user AND status <> :s4'); // menyiapkan statistik rental pengguna
$stmtStat->execute(array(':s1' => 'pending', ':s2' => 'dikonfirmasi', ':s3' => 'selesai', ':s4' => 'dibatalkan', ':user' => $id)); // menjalankan statistik
$stat = $stmtStat->fetch(); // mengambil statistik
$stmtRental = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.total_harga, r.status, c.nama AS nama_mobil FROM rentals r JOIN cars c ON c.id = r.car_id WHERE r.user_id = :user ORDER BY r.id DESC LIMIT 10'); // menyiapkan riwayat rental pengguna
$stmtRental->execute(array(':user' => $id)); // menjalankan riwayat
$riwayat = $stmtRental->fetchAll(); // mengambil riwayat
$judul = 'Detail Pengguna ' . $orang['nama_lengkap']; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul detail pengguna -->
    <div class="container"> <!-- wadah judul -->
      <h1>Detail Pengguna</h1> <!-- judul halaman -->
      <p>Informasi akun dan ringkasan aktivitas penyewaan.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian detail pengguna -->
    <div class="container"> <!-- wadah detail -->
      <div class="ringkasan-admin"> <!-- kartu statistik pengguna -->
        <div class="kartu-ringkasan"> <!-- kartu total transaksi -->
          <h2><?php echo (int)$stat['total']; ?></h2> <!-- jumlah transaksi -->
          <p>Total Transaksi Aktif &amp; Selesai</p> <!-- keterangan transaksi -->
        </div> <!-- menutup kartu transaksi -->
        <div class="kartu-ringkasan"> <!-- kartu transaksi aktif -->
          <h2><?php echo (int)$stat['aktif']; ?></h2> <!-- jumlah aktif -->
          <p>Sedang Aktif</p> <!-- keterangan aktif -->
        </div> <!-- menutup kartu aktif -->
        <div class="kartu-ringkasan"> <!-- kartu nominal -->
          <h2><?php echo e(rupiah($stat['nominal'])); ?></h2> <!-- total nominal -->
          <p>Total Nilai Sewa</p> <!-- keterangan nominal -->
        </div> <!-- menutup kartu nominal -->
      </div> <!-- menutup kartu statistik -->
      <div class="rental-grid"> <!-- tata letak profil dan riwayat -->
        <div> <!-- kolom profil -->
          <img src="<?php echo e(BASE_URL . $orang['foto']); ?>" alt="Foto <?php echo e($orang['nama_lengkap']); ?>" class="foto-form" width="180" height="130" /> <!-- foto profil -->
          <h2 class="judul-bagian"><?php echo e($orang['nama_lengkap']); ?></h2> <!-- nama pengguna -->
          <p><span class="label-status role-<?php echo e($orang['role']); ?>"><?php echo e(ucfirst($orang['role'])); ?></span></p> <!-- peran -->
          <table class="rental-info"> <!-- tabel data akun -->
            <tbody> <!-- isi data akun -->
              <tr><th>ID</th><td>#<?php echo (int)$orang['id']; ?></td></tr> <!-- ID -->
              <tr><th>Username</th><td><?php echo e($orang['username']); ?></td></tr> <!-- username -->
              <tr><th>Email</th><td><?php echo e($orang['email']); ?></td></tr> <!-- email -->
              <tr><th>Telepon</th><td><?php echo e($orang['no_telepon'] !== null && $orang['no_telepon'] !== '' ? $orang['no_telepon'] : '-'); ?></td></tr> <!-- telepon -->
              <tr><th>Alamat</th><td><?php echo e($orang['alamat'] !== null && $orang['alamat'] !== '' ? $orang['alamat'] : '-'); ?></td></tr> <!-- alamat -->
              <tr><th>Bergabung</th><td><?php echo e($orang['created_at']); ?></td></tr> <!-- tanggal daftar -->
              <tr><th>Diperbarui</th><td><?php echo e($orang['updated_at']); ?></td></tr> <!-- tanggal update -->
            </tbody> <!-- menutup isi data akun -->
          </table> <!-- menutup tabel data akun -->
          <div class="daftar-aksi"> <!-- aksi pengguna -->
            <a href="/rental-mobil/admin/user_edit.php?id=<?php echo (int)$orang['id']; ?>" class="tombol">Ubah Data</a> <!-- tombol ubah -->
            <a href="/rental-mobil/admin/user.php" class="tombol tombol-outline">Kembali</a> <!-- tombol kembali -->
          </div> <!-- menutup aksi -->
        </div> <!-- menutup kolom profil -->
        <div> <!-- kolom riwayat -->
          <h2 class="judul-bagian">Riwayat Sewa Terakhir</h2> <!-- judul riwayat -->
          <div class="tabel-wrapper"> <!-- pembungkus tabel riwayat -->
            <table class="tabel-admin"> <!-- tabel riwayat -->
              <thead> <!-- kepala tabel -->
                <tr> <!-- baris header -->
                  <th>ID</th> <!-- kolom ID -->
                  <th>Mobil</th> <!-- kolom mobil -->
                  <th>Tanggal</th> <!-- kolom tanggal -->
                  <th>Total</th> <!-- kolom total -->
                  <th>Status</th> <!-- kolom status -->
                </tr> <!-- menutup baris header -->
              </thead> <!-- menutup kepala tabel -->
              <tbody> <!-- isi riwayat -->
                <?php if (count($riwayat) === 0) { ?> <!-- pesan kosong -->
                  <tr><td colspan="5" class="kosong">Pengguna ini belum memiliki penyewaan.</td></tr> <!-- baris kosong -->
                <?php } else { ?> <!-- mulai data -->
                  <?php foreach ($riwayat as $item) { ?> <!-- mengulang riwayat -->
                    <tr> <!-- membuka baris -->
                      <td>#<?php echo (int)$item['id']; ?></td> <!-- ID rental -->
                      <td><?php echo e($item['nama_mobil']); ?></td> <!-- mobil -->
                      <td><?php echo e($item['tanggal_mulai']); ?> - <?php echo e($item['tanggal_selesai']); ?></td> <!-- tanggal -->
                      <td><?php echo e(rupiah($item['total_harga'])); ?></td> <!-- total -->
                      <td><span class="label-status status-<?php echo e($item['status']); ?>"><?php echo e(label_status_rental($item['status'])); ?></span></td> <!-- status -->
                    </tr> <!-- menutup baris -->
                  <?php } ?> <!-- menutup perulangan -->
                <?php } ?> <!-- menutup pengecekan data -->
              </tbody> <!-- menutup isi riwayat -->
            </table> <!-- menutup tabel riwayat -->
          </div> <!-- menutup pembungkus riwayat -->
        </div> <!-- menutup kolom riwayat -->
      </div> <!-- menutup tata letak profil dan riwayat -->
    </div> <!-- menutup wadah detail -->
  </div> <!-- menutup bagian detail -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
