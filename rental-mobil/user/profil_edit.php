<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../includes/cek_login.php'; // memastikan hanya pengguna login yang bisa mengedit
$stmt = $pdo->prepare('SELECT id, username, email, nama_lengkap, no_telepon, alamat, foto, role FROM users WHERE id = :id LIMIT 1'); // menyiapkan query data yang akan diedit
$stmt->execute(array(':id' => $userLogin['id'])); // menjalankan query dengan id pengguna login
$data = $stmt->fetch(); // mengambil data pengguna
if (!$data) { // mengecek apakah data tidak ditemukan
    session_destroy(); // menghancurkan session yang tidak valid
    redirect(BASE_URL . 'auth/login.php'); // mengarahkan ke halaman login
} // menutup pengecekan data
$username = $data['username']; // mengisi nilai awal username
$email = $data['email']; // mengisi nilai awal email
$nama = $data['nama_lengkap']; // mengisi nilai awal nama
$telepon = $data['no_telepon'] !== null ? $data['no_telepon'] : ''; // mengisi nilai awal telepon
$alamat = $data['alamat'] !== null ? $data['alamat'] : ''; // mengisi nilai awal alamat
$errors = array(); // menyiapkan daftar error
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token CSRF
        $errors['umum'] = 'Token keamanan tidak valid, silakan coba lagi.'; // menyimpan error token
    } // menutup pengecekan token
    $username = input_post('username'); // membaca username baru
    $email = input_post('email'); // membaca email baru
    $nama = input_post('nama_lengkap'); // membaca nama baru
    $telepon = input_post('no_telepon'); // membaca telepon baru
    $alamat = input_post('alamat'); // membaca alamat baru
    if (strlen($nama) < 2 || strlen($nama) > 100) { // memeriksa panjang nama
        $errors['nama'] = 'Nama lengkap harus antara 2 sampai 100 karakter.'; // menyimpan error nama
    } // menutup pengecekan nama
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) { // memeriksa format username
        $errors['username'] = 'Username 3-20 karakter, hanya huruf, angka, dan garis bawah.'; // menyimpan error username
    } // menutup pengecekan username
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { // memeriksa format email
        $errors['email'] = 'Format email tidak valid.'; // menyimpan error email
    } // menutup pengecekan email
    if ($telepon !== '' && !preg_match('/^[0-9+ -]{6,20}$/', $telepon)) { // memeriksa format telepon jika diisi
        $errors['telepon'] = 'Nomor telepon hanya boleh angka, spasi, tanda plus, dan tanda hubung.'; // menyimpan error telepon
    } // menutup pengecekan telepon
    if (count($errors) === 0) { // mengecek apakah validasi awal lolos
        try { // memulai percobaan pemeriksaan database
            $stmtUser = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND id <> :idUser'); // menyiapkan query cek username milik orang lain
            $stmtUser->execute(array(':username' => $username, ':idUser' => $userLogin['id'])); // menjalankan query username
            if ((int) $stmtUser->fetchColumn() > 0) { // mengecek apakah username sudah dipakai orang lain
                $errors['username'] = 'Username sudah dipakai pengguna lain.'; // menyimpan error username
            } // menutup pengecekan username
            $stmtEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email AND id <> :idEmail'); // menyiapkan query cek email milik orang lain
            $stmtEmail->execute(array(':email' => $email, ':idEmail' => $userLogin['id'])); // menjalankan query email
            if ((int) $stmtEmail->fetchColumn() > 0) { // mengecek apakah email sudah dipakai orang lain
                $errors['email'] = 'Email sudah dipakai pengguna lain.'; // menyimpan error email
            } // menutup pengecekan email
            if (count($errors) === 0) { // mengecek apakah semua validasi lolos
                $stmtUpdate = $pdo->prepare('UPDATE users SET username = :username, email = :email, nama_lengkap = :nama, no_telepon = :telepon, alamat = :alamat WHERE id = :id'); // menyiapkan query update profil
                $stmtUpdate->execute(array(':username' => $username, ':email' => $email, ':nama' => $nama, ':telepon' => $telepon, ':alamat' => $alamat, ':id' => $userLogin['id'])); // menjalankan update dengan data aman
                $_SESSION['username'] = $username; // memperbarui username di session
                $_SESSION['nama_lengkap'] = $nama; // memperbarui nama di session
                set_flash('sukses', 'Profil berhasil diperbarui.'); // menyimpan pesan sukses
                redirect(BASE_URL . 'user/profil.php'); // mengarahkan ke halaman profil
            } // menutup blok update
        } catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
            error_log('Gagal edit profil: ' . $e->getMessage()); // menyimpan detail error ke log server
            $errors['umum'] = 'Profil sementara gagal diperbarui, silakan coba lagi.'; // memberi pesan umum yang aman
        } // menutup percobaan database
    } // menutup pengecekan validasi
} // menutup blok formulir
$judul = 'Edit Profil'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu navbar yang aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul edit profil -->
    <div class="container"> <!-- wadah judul -->
      <h1>Edit Profil</h1> <!-- judul halaman -->
      <p>Perbarui data pribadi akun Anda.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir edit -->
    <div class="container"> <!-- wadah formulir edit -->
      <?php if (count($errors) > 0) { // mengecek apakah ada error ?> <!-- awal daftar error -->
        <div class="flash flash-gagal"> <!-- kotak pesan error -->
          <ul> <!-- daftar pesan error -->
            <?php foreach ($errors as $pesan) { // mengulang setiap pesan error ?> <!-- awal perulangan error -->
              <li><?php echo e($pesan); // menampilkan pesan aman ?></li> <!-- satu baris error -->
            <?php } // menutup perulangan error ?> <!-- akhir perulangan error -->
          </ul> <!-- menutup daftar error -->
        </div> <!-- menutup kotak error -->
      <?php } // menutup pengecekan error ?> <!-- akhir daftar error -->
      <form method="post" action="<?php echo e(BASE_URL . 'user/profil_edit.php'); // alamat aksi formulir ?>" class="form-kartu"> <!-- formulir edit profil -->
        <?php echo csrf_field(); // menyisipkan token CSRF ?> <!-- token keamanan formulir -->
        <h1>Data Pribadi</h1> <!-- judul kartu formulir -->
        <div class="form-baris"> <!-- baris username -->
          <label for="username" class="label-form">Username</label> <!-- label username -->
          <input type="text" id="username" name="username" value="<?php echo e($username); // nilai username ?>" class="form-kolom" /> <!-- kolom username -->
        </div> <!-- menutup baris username -->
        <div class="form-baris"> <!-- baris email -->
          <label for="email" class="label-form">Email</label> <!-- label email -->
          <input type="text" id="email" name="email" value="<?php echo e($email); // nilai email ?>" class="form-kolom" /> <!-- kolom email -->
        </div> <!-- menutup baris email -->
        <div class="form-baris"> <!-- baris nama -->
          <label for="nama_lengkap" class="label-form">Nama Lengkap</label> <!-- label nama -->
          <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo e($nama); // nilai nama ?>" class="form-kolom" /> <!-- kolom nama -->
        </div> <!-- menutup baris nama -->
        <div class="form-baris"> <!-- baris telepon -->
          <label for="no_telepon" class="label-form">Nomor Telepon</label> <!-- label telepon -->
          <input type="text" id="no_telepon" name="no_telepon" value="<?php echo e($telepon); // nilai telepon ?>" class="form-kolom" /> <!-- kolom telepon -->
        </div> <!-- menutup baris telepon -->
        <div class="form-baris"> <!-- baris alamat -->
          <label for="alamat" class="label-form">Alamat</label> <!-- label alamat -->
          <textarea id="alamat" name="alamat" rows="3" class="form-kolom"><?php echo e($alamat); // nilai alamat ?></textarea> <!-- kolom alamat -->
        </div> <!-- menutup baris alamat -->
        <div class="form-bawah"> <!-- area tombol -->
          <input type="submit" value="Simpan Perubahan" class="tombol" /> <!-- tombol menyimpan -->
          <a href="<?php echo e(BASE_URL . 'user/profil.php'); // alamat profil ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir edit -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
