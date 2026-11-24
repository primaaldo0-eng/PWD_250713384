<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/../includes/fungsi.php'; // mengambil fungsi escape, CSRF, dan redirect
$sumberRedirect = isset($_POST['redirect']) ? $_POST['redirect'] : (isset($_GET['redirect']) ? $_GET['redirect'] : ''); // mengambil tujuan dari POST atau GET
$redirect = sanitasi_redirect($sumberRedirect); // membatasi tujuan agar tetap di aplikasi
if (!empty($_SESSION['user_id'])) { // mengecek apakah sudah login
    redirect($redirect); // mengarahkan langsung ke tujuan yang aman
} // menutup pengecekan status login
$identitas = ''; // menyiapkan nilai username atau email
$pesanError = ''; // menyiapkan pesan error login
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token CSRF
        $pesanError = 'Token keamanan tidak valid, silakan coba lagi.'; // menyimpan pesan error token
    } else { // jika token valid
        $identitas = input_post('identitas'); // membaca username atau email
        $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : ''; // membaca kata sandi
        if ($identitas === '' || $password === '') { // mengecek apakah ada kolom kosong
            $pesanError = 'Username atau email dan kata sandi wajib diisi.'; // menyimpan pesan error kolom kosong
        } else { // jika kolom terisi
            try { // memulai percobaan pencarian pengguna
                $stmt = $pdo->prepare('SELECT id, username, nama_lengkap, role, password, foto FROM users WHERE username = :identitas1 OR email = :identitas2 LIMIT 1'); // menyiapkan query aman dengan dua parameter berbeda
                $stmt->execute(array(':identitas1' => $identitas, ':identitas2' => $identitas)); // menjalankan query dengan identitas yang diketik
                $user = $stmt->fetch(); // mengambil data pengguna
                if ($user && password_verify($password, $user['password'])) { // memeriksa kata sandi dengan hash yang tersimpan
                    session_regenerate_id(true); // mengganti id session lama dengan yang baru
                    $_SESSION['user_id'] = (int) $user['id']; // menyimpan id pengguna ke session
                    $_SESSION['username'] = $user['username']; // menyimpan username ke session
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap']; // menyimpan nama pengguna ke session
                    $_SESSION['role'] = $user['role']; // menyimpan peran pengguna ke session
                    $_SESSION['foto'] = $user['foto']; // menyimpan foto profil ke session
                    csrf_regenerate(); // mengganti token CSRF setelah login
                    set_flash('sukses', 'Selamat datang, ' . $user['nama_lengkap'] . '.'); // menyimpan pesan selamat datang
                    redirect($redirect); // mengarahkan ke halaman tujuan setelah login
                } else { // jika kata sandi atau identitas salah
                    $pesanError = 'Username atau email dan kata sandi tidak cocok.'; // memberi pesan umum agar tidak membocorkan akun
                } // menutup pengecekan kata sandi
            } catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
                error_log('Gagal login: ' . $e->getMessage()); // menyimpan detail error ke log server
                $pesanError = 'Login sementara gagal, silakan coba lagi.'; // memberi pesan umum yang aman
            } // menutup percobaan database
        } // menutup pengecekan kolom kosong
    } // menutup pengecekan token CSRF
} // menutup blok pemrosesan formulir
$judul = 'Login'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu yang ditandai aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-auth"> <!-- area judul halaman login -->
    <div class="container"> <!-- wadah judul -->
      <h1>Login</h1> <!-- judul halaman -->
      <p>Masuk ke akun Anda untuk melanjutkan pemesanan.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir login -->
    <div class="container"> <!-- wadah formulir -->
      <?php if ($pesanError !== '') { // mengecek apakah ada pesan error ?> <!-- awal pesan error -->
        <div class="flash flash-gagal"><?php echo e($pesanError); // menampilkan pesan error ?></div> <!-- kotak pesan error -->
      <?php } // menutup pengecekan pesan error ?> <!-- akhir pesan error -->
      <form method="post" action="<?php echo e(BASE_URL . 'auth/login.php'); // alamat aksi formulir ?>" class="form-kartu"> <!-- formulir login -->
        <?php echo csrf_field(); // menyisipkan token CSRF ?> <!-- token keamanan formulir -->
        <input type="hidden" name="redirect" value="<?php echo e($redirect); // tujuan setelah login ?>" /> <!-- menyimpan halaman tujuan -->
        <h1>Selamat Datang Kembali</h1> <!-- judul kartu login -->
        <p class="form-pengantar">Masukkan username atau email dan kata sandi Anda.</p> <!-- pengantar formulir -->
        <div class="form-baris"> <!-- baris identitas -->
          <label for="identitas" class="label-form">Username atau Email</label> <!-- label identitas -->
          <input type="text" id="identitas" name="identitas" value="<?php echo e($identitas); // nilai identitas ?>" class="form-kolom" /> <!-- kolom identitas -->
        </div> <!-- menutup baris identitas -->
        <div class="form-baris"> <!-- baris kata sandi -->
          <label for="password" class="label-form">Kata Sandi</label> <!-- label kata sandi -->
          <input type="password" id="password" name="password" class="form-kolom" /> <!-- kolom kata sandi -->
        </div> <!-- menutup baris kata sandi -->
        <div class="form-bawah"> <!-- area tombol masuk -->
          <input type="submit" value="Masuk" class="tombol" /> <!-- tombol mengirim login -->
        </div> <!-- menutup area tombol -->
        <p class="form-tautan">Belum punya akun? <a href="<?php echo e(BASE_URL . 'auth/daftar.php?redirect=' . urlencode($redirect)); // alamat pendaftaran ?>">Daftar di sini</a>.</p> <!-- tautan ke pendaftaran -->
      </form> <!-- menutup formulir login -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
