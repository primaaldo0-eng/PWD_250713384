<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat pengecekan sesi dan hak akses admin
$stmt = $pdo->prepare('SELECT (SELECT COUNT(*) FROM users) AS users, (SELECT COUNT(*) FROM cars) AS cars, (SELECT COUNT(*) FROM rentals) AS rentals, (SELECT COUNT(*) FROM rentals WHERE status = :pending) AS pending'); // menyiapkan query ringkasan dengan parameter
$stmt->execute(array(':pending' => 'pending')); // menjalankan query ringkasan
$ringkasan = $stmt->fetch(); // mengambil hasil ringkasan
$stmtTerbaru = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.status, u.nama_lengkap, c.nama AS nama_mobil FROM rentals r JOIN users u ON u.id = r.user_id JOIN cars c ON c.id = r.car_id ORDER BY r.id DESC LIMIT 5'); // menyiapkan daftar rental terbaru
$stmtTerbaru->execute(); // menjalankan query rental terbaru
$terbaru = $stmtTerbaru->fetchAll(); // mengambil rental terbaru
$stmtPengguna = $pdo->prepare('SELECT id, username, nama_lengkap, email, foto, role, created_at FROM users ORDER BY id DESC LIMIT 5'); // menyiapkan daftar pengguna terbaru
$stmtPengguna->execute(); // menjalankan query pengguna terbaru
$penggunaTerbaru = $stmtPengguna->fetchAll(); // mengambil pengguna terbaru
$judul = 'Dashboard Admin'; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul dashboard admin -->
    <div class="container"> <!-- wadah judul -->
      <h1>Dashboard Admin</h1> <!-- judul halaman -->
      <p>Ringkasan sistem rental mobil dan akses cepat ke kelola data.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian isi dashboard admin -->
    <div class="container"> <!-- wadah isi dashboard -->
      <div class="ringkasan-admin"> <!-- kumpulan kartu ringkasan -->
        <div class="kartu-ringkasan"> <!-- kartu total pengguna -->
          <h2><?php echo (int)$ringkasan['users']; ?></h2> <!-- jumlah pengguna -->
          <p>Pengguna Terdaftar</p> <!-- keterangan pengguna -->
          <a href="/rental-mobil/admin/user.php">Kelola pengguna</a> <!-- tautan kelola pengguna -->
        </div> <!-- menutup kartu pengguna -->
        <div class="kartu-ringkasan"> <!-- kartu total mobil -->
          <h2><?php echo (int)$ringkasan['cars']; ?></h2> <!-- jumlah mobil -->
          <p>Unit Mobil</p> <!-- keterangan mobil -->
          <a href="/rental-mobil/admin/mobil.php">Kelola mobil</a> <!-- tautan kelola mobil -->
        </div> <!-- menutup kartu mobil -->
        <div class="kartu-ringkasan"> <!-- kartu total rental -->
          <h2><?php echo (int)$ringkasan['rentals']; ?></h2> <!-- jumlah rental -->
          <p>Total Transaksi</p> <!-- keterangan rental -->
          <a href="/rental-mobil/admin/rental.php">Kelola rental</a> <!-- tautan kelola rental -->
        </div> <!-- menutup kartu rental -->
        <div class="kartu-ringkasan"> <!-- kartu rental menunggu -->
          <h2><?php echo (int)$ringkasan['pending']; ?></h2> <!-- jumlah pending -->
          <p>Menunggu Konfirmasi</p> <!-- keterangan pending -->
          <a href="/rental-mobil/admin/rental.php?status=pending">Lihat pengajuan</a> <!-- tautan filter pending -->
        </div> <!-- menutup kartu pending -->
      </div> <!-- menutup kumpulan kartu ringkasan -->
      <div class="daftar-aksi"> <!-- akses cepat admin -->
        <a href="/rental-mobil/admin/mobil.php" class="tombol">Kelola Data Mobil</a> <!-- tombol kelola mobil -->
        <a href="/rental-mobil/admin/rental.php" class="tombol tombol-outline">Kelola Data Rental</a> <!-- tombol kelola rental -->
        <a href="/rental-mobil/admin/user.php" class="tombol tombol-outline">Data Pengguna</a> <!-- tombol data pengguna -->
      </div> <!-- menutup akses cepat -->
      <div class="admin-dua-kolom"> <!-- membuka dua tabel ringkasan -->
        <div> <!-- kolom rental terbaru -->
          <h2 class="judul-bagian">Rental Terbaru</h2> <!-- judul tabel rental -->
          <div class="tabel-wrapper"> <!-- pembungkus tabel rental -->
            <table class="tabel-admin"> <!-- tabel rental terbaru -->
              <thead> <!-- kepala tabel -->
                <tr> <!-- baris header -->
                  <th>ID</th> <!-- kolom ID -->
                  <th>Penyewa</th> <!-- kolom penyewa -->
                  <th>Mobil</th> <!-- kolom mobil -->
                  <th>Tanggal</th> <!-- kolom tanggal -->
                  <th>Status</th> <!-- kolom status -->
                  <th>Aksi</th> <!-- kolom aksi -->
                </tr> <!-- menutup baris header -->
              </thead> <!-- menutup kepala tabel -->
              <tbody> <!-- isi tabel -->
                <?php if (count($terbaru) === 0) { ?> <!-- pesan kosong -->
                  <tr><td colspan="6" class="kosong">Belum ada transaksi rental.</td></tr> <!-- baris kosong -->
                <?php } else { ?> <!-- mulai data -->
                  <?php foreach ($terbaru as $item) { ?> <!-- mengulang rental terbaru -->
                    <tr> <!-- membuka baris -->
                      <td>#<?php echo (int)$item['id']; ?></td> <!-- ID rental -->
                      <td><?php echo e($item['nama_lengkap']); ?></td> <!-- nama penyewa -->
                      <td><?php echo e($item['nama_mobil']); ?></td> <!-- nama mobil -->
                      <td><?php echo e($item['tanggal_mulai']); ?> - <?php echo e($item['tanggal_selesai']); ?></td> <!-- jadwal -->
                      <td><span class="label-status status-<?php echo e($item['status']); ?>"><?php echo e(label_status_rental($item['status'])); ?></span></td> <!-- status -->
                      <td><a href="/rental-mobil/admin/rental_edit.php?id=<?php echo (int)$item['id']; ?>" class="tombol tombol-outline tombol-kecil">Kelola</a></td> <!-- tautan kelola -->
                    </tr> <!-- menutup baris -->
                  <?php } ?> <!-- menutup perulangan -->
                <?php } ?> <!-- menutup pengecekan data -->
              </tbody> <!-- menutup isi tabel -->
            </table> <!-- menutup tabel rental -->
          </div> <!-- menutup pembungkus tabel rental -->
        </div> <!-- menutup kolom rental -->
        <div> <!-- kolom pengguna terbaru -->
          <h2 class="judul-bagian">Pengguna Terbaru</h2> <!-- judul tabel pengguna -->
          <div class="tabel-wrapper"> <!-- pembungkus tabel pengguna -->
            <table class="tabel-admin"> <!-- tabel pengguna terbaru -->
              <thead> <!-- kepala tabel -->
                <tr> <!-- baris header -->
                  <th>ID</th> <!-- kolom ID -->
                  <th>Foto</th> <!-- kolom foto -->
                  <th>Nama</th> <!-- kolom nama -->
                  <th>Username</th> <!-- kolom username -->
                  <th>Role</th> <!-- kolom role -->
                  <th>Aksi</th> <!-- kolom aksi -->
                </tr> <!-- menutup baris header -->
              </thead> <!-- menutup kepala tabel -->
              <tbody> <!-- isi tabel -->
                <?php foreach ($penggunaTerbaru as $orang) { ?> <!-- mengulang pengguna terbaru -->
                  <tr> <!-- membuka baris -->
                    <td>#<?php echo (int)$orang['id']; ?></td> <!-- ID pengguna -->
                    <td><img src="<?php echo e(BASE_URL . $orang['foto']); ?>" alt="Foto <?php echo e($orang['nama_lengkap']); ?>" class="foto-tabel" width="40" height="40" /></td> <!-- foto pengguna -->
                    <td><?php echo e($orang['nama_lengkap']); ?><br /><small><?php echo e($orang['email']); ?></small></td> <!-- nama dan email -->
                    <td><?php echo e($orang['username']); ?><br /><small><?php echo e($orang['created_at']); ?></small></td> <!-- username dan tanggal daftar -->
                    <td><span class="label-status role-<?php echo e($orang['role']); ?>"><?php echo e(ucfirst($orang['role'])); ?></span></td> <!-- role -->
                    <td><a href="/rental-mobil/admin/user_detail.php?id=<?php echo (int)$orang['id']; ?>" class="tombol tombol-outline tombol-kecil">Detail</a></td> <!-- tautan detail -->
                  </tr> <!-- menutup baris -->
                <?php } ?> <!-- menutup perulangan -->
              </tbody> <!-- menutup isi tabel -->
            </table> <!-- menutup tabel pengguna -->
          </div> <!-- menutup pembungkus tabel pengguna -->
        </div> <!-- menutup kolom pengguna -->
      </div> <!-- menutup dua tabel ringkasan -->
    </div> <!-- menutup wadah isi dashboard -->
  </div> <!-- menutup bagian isi dashboard -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
