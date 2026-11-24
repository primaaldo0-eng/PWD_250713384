<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : ''; // membaca kata kunci pencarian
$role = isset($_GET['role']) ? (string)$_GET['role'] : ''; // membaca filter peran
if ($role !== 'admin' && $role !== 'user') { // memvalidasi filter peran
    $role = ''; // mengosongkan filter yang tidak dikenal
} // menutup pengecekan peran
$where = ' WHERE 1 = 1'; // memulai klausa query
$params = array(); // menyiapkan parameter query
if ($q !== '') { // menambah pencarian teks
    $where .= ' AND (nama_lengkap LIKE :q1 OR username LIKE :q2 OR email LIKE :q3)'; // mencari di tiga kolom
    $pola = '%' . $q . '%'; // membuat pola pencarian
    $params[':q1'] = $pola; // parameter pertama
    $params[':q2'] = $pola; // parameter kedua
    $params[':q3'] = $pola; // parameter ketiga
} // menutup pengecekan pencarian
if ($role !== '') { // menambah filter peran
    $where .= ' AND role = :role'; // menambah klausa peran
    $params[':role'] = $role; // menambah parameter peran
} // menutup pengecekan peran
$stmt = $pdo->prepare('SELECT id, username, nama_lengkap, email, foto, no_telepon, alamat, role, created_at FROM users' . $where . ' ORDER BY id DESC'); // menyiapkan query daftar pengguna
$stmt->execute($params); // menjalankan query daftar pengguna
$daftarUser = $stmt->fetchAll(); // mengambil hasil
$judul = 'Data Pengguna'; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul data pengguna -->
    <div class="container"> <!-- wadah judul -->
      <h1>Data Pengguna</h1> <!-- judul halaman -->
      <p>Kelola akun pengguna dan peran yang dimiliki.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian tabel pengguna -->
    <div class="container"> <!-- wadah tabel pengguna -->
      <div class="admin-toolbar"> <!-- bilah pencarian -->
        <form method="get" action="/rental-mobil/admin/user.php" class="pencarian"> <!-- formulir pencarian -->
          <label for="q" class="label-form">Cari:</label> <!-- label kata kunci -->
          <input type="text" id="q" name="q" value="<?php echo e($q); ?>" class="form-kolom" /> <!-- kolom kata kunci -->
          <label for="role" class="label-form">Role:</label> <!-- label peran -->
          <select id="role" name="role" class="form-kolom"> <!-- pilihan peran -->
            <option value="">Semua</option> <!-- opsi semua peran -->
            <option value="admin" <?php if ($role === 'admin') { ?>selected="selected"<?php } ?>>Admin</option> <!-- opsi admin -->
            <option value="user" <?php if ($role === 'user') { ?>selected="selected"<?php } ?>>User</option> <!-- opsi user -->
          </select> <!-- menutup pilihan peran -->
          <input type="submit" value="Filter" class="tombol" /> <!-- tombol filter -->
        </form> <!-- menutup formulir pencarian -->
        <a href="/rental-mobil/admin/user.php" class="tombol tombol-outline">Reset</a> <!-- tautan reset -->
      </div> <!-- menutup bilah pencarian -->
      <div class="tabel-wrapper"> <!-- pembungkus tabel -->
        <table class="tabel-admin"> <!-- tabel data pengguna -->
          <thead> <!-- kepala tabel -->
            <tr> <!-- baris header -->
              <th>ID</th> <!-- kolom ID -->
              <th>Foto</th> <!-- kolom foto -->
              <th>Nama</th> <!-- kolom nama -->
              <th>Username</th> <!-- kolom username -->
              <th>Email</th> <!-- kolom email -->
              <th>Telepon</th> <!-- kolom telepon -->
              <th>Role</th> <!-- kolom peran -->
              <th>Bergabung</th> <!-- kolom tanggal daftar -->
              <th>Aksi</th> <!-- kolom aksi -->
            </tr> <!-- menutup baris header -->
          </thead> <!-- menutup kepala tabel -->
          <tbody> <!-- isi tabel -->
            <?php if (count($daftarUser) === 0) { ?> <!-- pesan kosong -->
              <tr><td colspan="9" class="kosong">Tidak ada pengguna yang cocok.</td></tr> <!-- baris kosong -->
            <?php } else { ?> <!-- mulai data -->
              <?php foreach ($daftarUser as $orang) { ?> <!-- mengulang pengguna -->
                <tr> <!-- membuka baris -->
                  <td>#<?php echo (int)$orang['id']; ?></td> <!-- ID -->
                  <td><img src="<?php echo e(BASE_URL . $orang['foto']); ?>" alt="Foto <?php echo e($orang['nama_lengkap']); ?>" class="foto-tabel" width="40" height="40" /></td> <!-- foto -->
                  <td><?php echo e($orang['nama_lengkap']); ?></td> <!-- nama -->
                  <td><?php echo e($orang['username']); ?></td> <!-- username -->
                  <td><?php echo e($orang['email']); ?></td> <!-- email -->
                  <td><?php echo e($orang['no_telepon'] !== null && $orang['no_telepon'] !== '' ? $orang['no_telepon'] : '-'); ?></td> <!-- telepon -->
                  <td><span class="label-status role-<?php echo e($orang['role']); ?>"><?php echo e(ucfirst($orang['role'])); ?></span></td> <!-- peran -->
                  <td><?php echo e($orang['created_at']); ?></td> <!-- tanggal daftar -->
                  <td> <!-- sel aksi -->
                    <div class="aksi-tabel"> <!-- wadah tombol -->
                      <a href="/rental-mobil/admin/user_detail.php?id=<?php echo (int)$orang['id']; ?>" class="tombol tombol-outline tombol-kecil">Detail</a> <!-- tautan detail -->
                      <a href="/rental-mobil/admin/user_edit.php?id=<?php echo (int)$orang['id']; ?>" class="tombol tombol-outline tombol-kecil">Ubah</a> <!-- tautan ubah -->
                    </div> <!-- menutup wadah tombol -->
                  </td> <!-- menutup sel aksi -->
                </tr> <!-- menutup baris -->
              <?php } ?> <!-- menutup perulangan -->
            <?php } ?> <!-- menutup pengecekan data -->
          </tbody> <!-- menutup isi tabel -->
        </table> <!-- menutup tabel pengguna -->
      </div> <!-- menutup pembungkus tabel -->
    </div> <!-- menutup wadah tabel pengguna -->
  </div> <!-- menutup bagian tabel pengguna -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
