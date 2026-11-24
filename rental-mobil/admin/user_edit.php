<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman untuk admin
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID pengguna
$stmt = $pdo->prepare('SELECT id, username, nama_lengkap, email, no_telepon, alamat, role FROM users WHERE id = :id LIMIT 1'); // menyiapkan query ambil pengguna
$stmt->execute(array(':id' => $id)); // menjalankan query
$orang = $stmt->fetch(); // mengambil data pengguna
if (!$orang) { // memastikan pengguna ditemukan
    set_flash('gagal', 'Data pengguna tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/user.php'); // kembali ke daftar
} // menutup pengecekan pengguna
$errors = array(); // menyiapkan daftar error
$data = array( // menyiapkan nilai formulir
    'id' => (int)$orang['id'], // ID pengguna
    'nama_lengkap' => $orang['nama_lengkap'], // nama lengkap
    'email' => $orang['email'], // email
    'no_telepon' => $orang['no_telepon'], // nomor telepon
    'alamat' => $orang['alamat'], // alamat
    'role' => $orang['role'] // peran
); // menutup nilai formulir
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') { // memproses perubahan data
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
        $errors[] = 'Token keamanan tidak valid.'; // mencatat error CSRF
    } // menutup pengecekan token
    if ((int)input_post('id') !== $id) { // memastikan ID tidak diubah
        $errors[] = 'ID pengguna tidak sesuai.'; // mencatat error ID
    } // menutup pengecekan ID
    $data['nama_lengkap'] = input_post('nama_lengkap'); // membaca nama lengkap
    $data['email'] = input_post('email'); // membaca email
    $data['no_telepon'] = input_post('no_telepon'); // membaca telepon
    $data['alamat'] = input_post('alamat'); // membaca alamat
    $data['role'] = input_post('role'); // membaca peran
    if ($data['nama_lengkap'] === '') { // memastikan nama terisi
        $errors[] = 'Nama lengkap wajib diisi.'; // mencatat error nama
    } // menutup pengecekan nama
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { // memvalidasi email
        $errors[] = 'Format email tidak valid.'; // mencatat error email
    } // menutup pengecekan email
    if ($data['role'] !== 'admin' && $data['role'] !== 'user') { // memvalidasi peran
        $errors[] = 'Peran tidak dikenal.'; // mencatat error peran
    } // menutup pengecekan peran
    if ($id === (int)$_SESSION['user_id'] && $data['role'] !== 'admin') { // mencegah admin menurunkan perannya sendiri
        $errors[] = 'Peran admin tidak dapat diubah sendiri.'; // mencatat error peran diri sendiri
    } // menutup pengecekan peran diri sendiri
    if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) { // mengecek duplikasi email bila format valid
        $cekEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :id'); // menyiapkan pengecekan email
        $cekEmail->execute(array(':email' => $data['email'], ':id' => $id)); // menjalankan pengecekan email
        if ($cekEmail->fetchColumn() > 0) { // mengecek hasil duplikasi
            $errors[] = 'Email sudah digunakan pengguna lain.'; // mencatat error duplikasi
        } // menutup pengecekan hasil
    } // menutup pengecekan duplikasi
    if (count($errors) === 0) { // menyimpan perubahan bila valid
        try { // menjalankan query dalam pengaman exception
            $ubah = $pdo->prepare('UPDATE users SET nama_lengkap = :nama, email = :email, no_telepon = :telepon, alamat = :alamat, role = :role WHERE id = :id'); // menyiapkan query update
            $ubah->execute(array( // mengirim nilai update
                ':nama' => $data['nama_lengkap'], // nama lengkap
                ':email' => $data['email'], // email
                ':telepon' => $data['no_telepon'], // telepon
                ':alamat' => $data['alamat'], // alamat
                ':role' => $data['role'], // peran
                ':id' => $id // ID pengguna
            )); // menutup array nilai
            set_flash('sukses', 'Data pengguna berhasil diperbarui.'); // menyimpan pesan sukses
            redirect(BASE_URL . 'admin/user_detail.php?id=' . $id); // pindah ke detail pengguna
        } catch (PDOException $exception) { // menangani kegagalan database
            $errors[] = 'Gagal memperbarui data pengguna.'; // mencatat error database
        } // menutup pengaman exception
    } // menutup pengecekan penyimpanan
} // menutup pemrosesan POST
$judul = 'Ubah Pengguna ' . $orang['nama_lengkap']; // menentukan judul halaman
$halamanAktif = 'admin'; // menandai menu admin
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul ubah pengguna -->
    <div class="container"> <!-- wadah judul -->
      <h1>Ubah Data Pengguna</h1> <!-- judul halaman -->
      <p>Username: <?php echo e($orang['username']); ?></p> <!-- username tidak dapat diubah -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir ubah pengguna -->
    <div class="container"> <!-- wadah formulir -->
      <?php if (count($errors) > 0) { ?> <!-- menampilkan error -->
        <div class="flash flash-gagal"> <!-- kotak error -->
          <ul> <!-- daftar error -->
            <?php foreach ($errors as $error) { ?> <!-- mengulang error -->
              <li><?php echo e($error); ?></li> <!-- menampilkan error -->
            <?php } ?> <!-- menutup perulangan -->
          </ul> <!-- menutup daftar error -->
        </div> <!-- menutup kotak error -->
      <?php } ?> <!-- menutup pengecekan error -->
      <form method="post" action="/rental-mobil/admin/user_edit.php?id=<?php echo $id; ?>" class="form-kartu"> <!-- formulir ubah data -->
        <?php echo csrf_field(); ?> <!-- token CSRF -->
        <input type="hidden" name="id" value="<?php echo $id; ?>" /> <!-- ID pengguna -->
        <div class="form-baris"> <!-- baris nama -->
          <label for="nama_lengkap" class="label-form">Nama Lengkap</label> <!-- label nama -->
          <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo e($data['nama_lengkap']); ?>" class="form-kolom" /> <!-- kolom nama -->
        </div> <!-- menutup baris nama -->
        <div class="form-baris"> <!-- baris email -->
          <label for="email" class="label-form">Email</label> <!-- label email -->
          <input type="text" id="email" name="email" value="<?php echo e($data['email']); ?>" class="form-kolom" /> <!-- kolom email -->
        </div> <!-- menutup baris email -->
        <div class="form-baris"> <!-- baris telepon -->
          <label for="no_telepon" class="label-form">No. Telepon</label> <!-- label telepon -->
          <input type="text" id="no_telepon" name="no_telepon" value="<?php echo e($data['no_telepon']); ?>" class="form-kolom" /> <!-- kolom telepon -->
        </div> <!-- menutup baris telepon -->
        <div class="form-baris"> <!-- baris alamat -->
          <label for="alamat" class="label-form">Alamat</label> <!-- label alamat -->
          <textarea id="alamat" name="alamat" rows="3" class="form-kolom"><?php echo e($data['alamat']); ?></textarea> <!-- kolom alamat -->
        </div> <!-- menutup baris alamat -->
        <div class="form-baris"> <!-- baris peran -->
          <label for="role" class="label-form">Peran</label> <!-- label peran -->
          <select id="role" name="role" class="form-kolom"> <!-- pilihan peran -->
            <option value="user" <?php if ($data['role'] === 'user') { ?>selected="selected"<?php } ?>>User</option> <!-- opsi user -->
            <option value="admin" <?php if ($data['role'] === 'admin') { ?>selected="selected"<?php } ?>>Admin</option> <!-- opsi admin -->
          </select> <!-- menutup pilihan peran -->
        </div> <!-- menutup baris peran -->
        <?php if ($id === (int)$_SESSION['user_id']) { ?> <!-- peringatan untuk akun sendiri -->
          <p class="info-koordinat">Anda tidak dapat mengubah peran akun sendiri.</p> <!-- pesan peringatan -->
        <?php } ?> <!-- menutup pengecekan akun sendiri -->
        <div class="form-bawah"> <!-- area tombol -->
          <input type="submit" value="Simpan Perubahan" class="tombol" /> <!-- tombol simpan -->
          <a href="/rental-mobil/admin/user_detail.php?id=<?php echo $id; ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
