<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/../includes/fungsi.php'; // mengambil fungsi escape, CSRF, dan redirect
if (!empty($_SESSION['user_id'])) { // mengecek apakah pengunjung sudah login
    redirect(BASE_URL . 'index.php'); // mengarahkan pengguna login ke beranda
} // menutup pengecekan status login
$sumberRedirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_GET['redirect']) ? $_GET['redirect'] : ''); // mengambil tujuan dari POST atau GET
$redirect = sanitasi_redirect($sumberRedirect); // membatasi tujuan agar tetap di dalam aplikasi
$username = ''; // menyiapkan nilai username
$email = ''; // menyiapkan nilai email
$nama = ''; // menyiapkan nilai nama lengkap
$noTelepon = ''; // menyiapkan nilai nomor telepon
$alamat = ''; // menyiapkan nilai alamat
$errors = array(); // menyiapkan daftar error validasi
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token CSRF
        $errors['umum'] = 'Token keamanan tidak valid, silakan coba lagi.'; // menyimpan pesan error umum
    } // menutup pengecekan token
    $username = input_post('username'); // membaca username
    $email = input_post('email'); // membaca email
    $nama = input_post('nama_lengkap'); // membaca nama lengkap
    $noTelepon = input_post('no_telepon'); // membaca nomor telepon
    $alamat = input_post('alamat'); // membaca alamat
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : ''; // membaca kata sandi tanpa memangkas spasi
    $konfirmasi = isset($_POST['konfirmasi']) && is_string($_POST['konfirmasi']) ? $_POST['konfirmasi'] : ''; // membaca konfirmasi kata sandi
    if (strlen($nama) < 2 || strlen($nama) > 100) { // memeriksa panjang nama lengkap
        $errors['nama'] = 'Nama lengkap harus antara 2 sampai 100 karakter.'; // menyimpan error nama
    } // menutup pengecekan nama
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) { // memeriksa format username
        $errors['username'] = 'Username 3-20 karakter, hanya huruf, angka, dan garis bawah.'; // menyimpan error username
    } // menutup pengecekan username
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { // memeriksa format email
        $errors['email'] = 'Format email tidak valid.'; // menyimpan error email
    } // menutup pengecekan email
    if (strlen($password) < 6) { // memeriksa panjang kata sandi
        $errors['password'] = 'Kata sandi minimal 6 karakter.'; // menyimpan error kata sandi
    } // menutup pengecekan kata sandi
    if ($password !== $konfirmasi) { // memeriksa kesamaan konfirmasi
        $errors['konfirmasi'] = 'Konfirmasi kata sandi tidak sama.'; // menyimpan error konfirmasi
    } // menutup pengecekan konfirmasi
    if ($noTelepon !== '' && !preg_match('/^[0-9+ -]{6,20}$/', $noTelepon)) { // memeriksa nomor telepon jika diisi
        $errors['no_telepon'] = 'Nomor telepon hanya boleh angka, spasi, tanda plus, dan tanda hubung.'; // menyimpan error telepon
    } // menutup pengecekan telepon
    if (count($errors) === 0) { // mengecek apakah validasi awal lolos
        try { // memulai percobaan pemeriksaan database
            $stmtUser = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username'); // menyiapkan query pengecekan username
            $stmtUser->execute(array(':username' => $username)); // menjalankan query username
            if ((int) $stmtUser->fetchColumn() > 0) { // mengecek apakah username sudah terpakai
                $errors['username'] = 'Username sudah dipakai pengguna lain.'; // menyimpan error username
            } // menutup pengecekan username terpakai
            $stmtEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email'); // menyiapkan query pengecekan email
            $stmtEmail->execute(array(':email' => $email)); // menjalankan query email
            if ((int) $stmtEmail->fetchColumn() > 0) { // mengecek apakah email sudah terpakai
                $errors['email'] = 'Email sudah dipakai pengguna lain.'; // menyimpan error email
            } // menutup pengecekan email terpakai
            if (count($errors) === 0) { // mengecek apakah semua validasi lolos
                $hash = password_hash($password, PASSWORD_DEFAULT); // membuat hash kata sandi dengan algoritma bawaan PHP
                $stmtSimpan = $pdo->prepare('INSERT INTO users (username, email, password, nama_lengkap, no_telepon, alamat, role, foto) VALUES (:username, :email, :password, :nama, :telepon, :alamat, :role, :foto)'); // menyiapkan query simpan pengguna baru
                $stmtSimpan->execute(array(':username' => $username, ':email' => $email, ':password' => $hash, ':nama' => $nama, ':telepon' => $noTelepon, ':alamat' => $alamat, ':role' => 'user', ':foto' => 'assets/img/profil/default.jpg')); // menjalankan query dengan data yang aman
                csrf_regenerate(); // mengganti token setelah pendaftaran berhasil
                set_flash('sukses', 'Akun berhasil dibuat. Silakan login.'); // menyimpan pesan sukses
                redirect(BASE_URL . 'auth/login.php?redirect=' . urlencode($redirect)); // mengarahkan ke halaman login dengan tujuan awal
            } // menutup blok penyimpanan
        } catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
            error_log('Gagal daftar: ' . $e->getMessage()); // menyimpan detail error ke log server
            $errors['umum'] = 'Pendaftaran sementara gagal, silakan coba lagi.'; // memberi pesan umum yang aman
        } // menutup percobaan database
    } // menutup pengecekan validasi awal
} // menutup blok pemrosesan formulir
$judul = 'Daftar Akun'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu yang ditandai aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-auth hero-auth-daftar"> <!-- area judul halaman pendaftaran -->
    <div class="container"> <!-- wadah judul -->
      <h1>Daftar Akun</h1> <!-- judul halaman -->
      <p>Buat akun untuk mulai menyewa mobil favorit Anda.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir pendaftaran -->
    <div class="container"> <!-- wadah formulir -->
      <?php if (count($errors) > 0) { // mengecek apakah ada error ditampilkan ?> <!-- awal daftar error -->
        <div class="flash flash-gagal"> <!-- kotak pesan error -->
          <ul> <!-- daftar pesan error -->
            <?php foreach ($errors as $pesan) { // mengulang setiap pesan error ?> <!-- awal perulangan error -->
              <li><?php echo e($pesan); // menampilkan pesan yang aman ?></li> <!-- satu baris pesan error -->
            <?php } // menutup perulangan error ?> <!-- akhir perulangan error -->
          </ul> <!-- menutup daftar pesan error -->
        </div> <!-- menutup kotak pesan error -->
      <?php } // menutup pengecekan error ?> <!-- akhir daftar error -->
      <form method="post" action="<?php echo e(BASE_URL . 'auth/daftar.php'); // alamat aksi formulir ?>" id="form-daftar" class="form-kartu"> <!-- formulir registrasi -->
        <?php echo csrf_field(); // menyisipkan token CSRF ?> <!-- token keamanan formulir -->
        <input type="hidden" name="redirect" value="<?php echo e($redirect); // tujuan setelah login ?>" /> <!-- menyimpan halaman tujuan -->
        <h1>Buat Akun Baru</h1> <!-- judul kartu formulir -->
        <p class="form-pengantar">Isi data berikut dengan benar.</p> <!-- pengantar formulir -->
        <div class="form-baris"> <!-- baris nama lengkap -->
          <label for="nama_lengkap" class="label-form">Nama Lengkap</label> <!-- label nama lengkap -->
          <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo e($nama); // nilai nama ?>" class="form-kolom" /> <!-- kolom nama lengkap -->
          <div class="pesan-error" id="err-nama"><?php echo e(isset($errors['nama']) ? $errors['nama'] : ''); // error nama ?></div> <!-- pesan error nama -->
        </div> <!-- menutup baris nama -->
        <div class="form-baris"> <!-- baris username -->
          <label for="username" class="label-form">Username</label> <!-- label username -->
          <input type="text" id="username" name="username" value="<?php echo e($username); // nilai username ?>" class="form-kolom" /> <!-- kolom username -->
          <div class="status-cek" id="status-username"></div> <!-- status ketersediaan username -->
          <div class="pesan-error" id="err-username"><?php echo e(isset($errors['username']) ? $errors['username'] : ''); // error username ?></div> <!-- pesan error username -->
        </div> <!-- menutup baris username -->
        <div class="form-baris"> <!-- baris email -->
          <label for="email" class="label-form">Email</label> <!-- label email -->
          <input type="text" id="email" name="email" value="<?php echo e($email); // nilai email ?>" class="form-kolom" /> <!-- kolom email -->
          <div class="status-cek" id="status-email"></div> <!-- status ketersediaan email -->
          <div class="pesan-error" id="err-email"><?php echo e(isset($errors['email']) ? $errors['email'] : ''); // error email ?></div> <!-- pesan error email -->
        </div> <!-- menutup baris email -->
        <div class="form-baris"> <!-- baris kata sandi -->
          <label for="password" class="label-form">Kata Sandi</label> <!-- label kata sandi -->
          <input type="password" id="password" name="password" class="form-kolom" /> <!-- kolom kata sandi -->
          <div class="pesan-error" id="err-password"><?php echo e(isset($errors['password']) ? $errors['password'] : ''); // error kata sandi ?></div> <!-- pesan error kata sandi -->
        </div> <!-- menutup baris kata sandi -->
        <div class="form-baris"> <!-- baris konfirmasi kata sandi -->
          <label for="konfirmasi" class="label-form">Ulangi Kata Sandi</label> <!-- label konfirmasi -->
          <input type="password" id="konfirmasi" name="konfirmasi" class="form-kolom" /> <!-- kolom konfirmasi -->
          <div class="pesan-error" id="err-konfirmasi"><?php echo e(isset($errors['konfirmasi']) ? $errors['konfirmasi'] : ''); // error konfirmasi ?></div> <!-- pesan error konfirmasi -->
        </div> <!-- menutup baris konfirmasi -->
        <div class="form-baris"> <!-- baris nomor telepon -->
          <label for="no_telepon" class="label-form">Nomor Telepon (opsional)</label> <!-- label telepon -->
          <input type="text" id="no_telepon" name="no_telepon" value="<?php echo e($noTelepon); // nilai telepon ?>" class="form-kolom" /> <!-- kolom telepon -->
          <div class="pesan-error"><?php echo e(isset($errors['no_telepon']) ? $errors['no_telepon'] : ''); // error telepon ?></div> <!-- pesan error telepon -->
        </div> <!-- menutup baris telepon -->
        <div class="form-baris"> <!-- baris alamat -->
          <label for="alamat" class="label-form">Alamat (opsional)</label> <!-- label alamat -->
          <input type="text" id="alamat" name="alamat" value="<?php echo e($alamat); // nilai alamat ?>" class="form-kolom" /> <!-- kolom alamat -->
        </div> <!-- menutup baris alamat -->
        <div class="form-bawah"> <!-- area tombol kirim -->
          <input type="submit" value="Daftar" class="tombol" /> <!-- tombol mengirim pendaftaran -->
        </div> <!-- menutup area tombol -->
        <p class="form-tautan">Sudah punya akun? <a href="<?php echo e(BASE_URL . 'auth/login.php?redirect=' . urlencode($redirect)); // alamat login ?>">Login di sini</a>.</p> <!-- tautan ke halaman login -->
      </form> <!-- menutup formulir registrasi -->
      <script type="text/javascript" src="<?php echo e(BASE_URL . 'assets/js/register.js'); // alamat skrip pendaftaran ?>"></script> <!-- memuat validasi klien -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
