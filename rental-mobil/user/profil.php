<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../includes/cek_login.php'; // memastikan hanya pengguna login yang bisa melihat halaman
$stmt = $pdo->prepare('SELECT id, username, email, nama_lengkap, no_telepon, alamat, foto, role, created_at FROM users WHERE id = :id LIMIT 1'); // menyiapkan query data profil
$stmt->execute(array(':id' => $userLogin['id'])); // menjalankan query dengan id pengguna login
$profil = $stmt->fetch(); // mengambil data profil
if (!$profil) { // mengecek apakah data profil tidak ditemukan
    session_destroy(); // menghancurkan session yang tidak valid
    redirect(BASE_URL . 'auth/login.php'); // mengarahkan ke halaman login
} // menutup pengecekan data profil
$fotoProfil = !empty($profil['foto']) && is_file(BASE_PATH . $profil['foto']) ? $profil['foto'] : 'assets/img/profil/default.jpg'; // memakai foto default jika file tidak ada
$telepon = $profil['no_telepon'] !== null && $profil['no_telepon'] !== '' ? $profil['no_telepon'] : '-'; // menyiapkan nomor telepon atau tanda minus
$alamat = $profil['alamat'] !== null && $profil['alamat'] !== '' ? $profil['alamat'] : '-'; // menyiapkan alamat atau tanda minus
$tanggalDaftar = date('d M Y', strtotime($profil['created_at'])); // memformat tanggal bergabung
$judul = 'Profil Saya'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu navbar yang aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul halaman profil -->
    <div class="container"> <!-- wadah judul -->
      <h1>Profil Saya</h1> <!-- judul halaman -->
      <p>Kelola data pribadi akun Anda di sini.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian isi profil -->
    <div class="container profil-grid"> <!-- wadah dua kolom profil -->
      <div> <!-- kolom foto profil -->
        <img src="<?php echo e(BASE_URL . $fotoProfil); // alamat foto profil ?>" alt="Foto <?php echo e($profil['nama_lengkap']); // nama pengguna ?>" class="profil-foto" width="180" height="180" /> <!-- foto profil besar -->
      </div> <!-- menutup kolom foto -->
      <div class="profil-kartu"> <!-- kartu keterangan profil -->
        <h2 class="judul-bagian"><?php echo e($profil['nama_lengkap']); // nama lengkap ?></h2> <!-- nama lengkap pengguna -->
        <p class="sub-judul">Username: <?php echo e($profil['username']); // username ?> - Peran: <?php echo e($profil['role']); // peran ?></p> <!-- username dan peran -->
        <table class="tabel"> <!-- tabel data profil -->
          <tbody> <!-- isi tabel profil -->
            <tr><th>Email</th><td><?php echo e($profil['email']); // email ?></td></tr> <!-- baris email -->
            <tr><th>Nomor Telepon</th><td><?php echo e($telepon); // nomor telepon ?></td></tr> <!-- baris telepon -->
            <tr><th>Alamat</th><td><?php echo e($alamat); // alamat ?></td></tr> <!-- baris alamat -->
            <tr><th>Bergabung Sejak</th><td><?php echo e($tanggalDaftar); // tanggal bergabung ?></td></tr> <!-- baris tanggal -->
          </tbody> <!-- menutup isi tabel -->
        </table> <!-- menutup tabel profil -->
        <div class="daftar-aksi"> <!-- kumpulan tautan pengelolaan profil -->
          <a href="<?php echo e(BASE_URL . 'user/profil_edit.php'); // alamat edit profil ?>" class="tombol">Edit Profil</a> <!-- tautan edit data -->
          <a href="<?php echo e(BASE_URL . 'user/password.php'); // alamat ganti password ?>" class="tombol">Ganti Password</a> <!-- tautan ganti kata sandi -->
          <a href="<?php echo e(BASE_URL . 'user/foto.php'); // alamat upload foto ?>" class="tombol">Ganti Foto</a> <!-- tautan upload foto -->
        </div> <!-- menutup kumpulan tautan -->
      </div> <!-- menutup kartu keterangan -->
    </div> <!-- menutup wadah profil -->
  </div> <!-- menutup bagian profil -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
