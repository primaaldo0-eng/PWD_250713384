<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../includes/cek_login.php'; // memastikan hanya pengguna login yang bisa ganti password
$stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1'); // menyiapkan query untuk mengambil hash kata sandi
$stmt->execute(array(':id' => $userLogin['id'])); // menjalankan query dengan id pengguna
$data = $stmt->fetch(); // mengambil baris data kata sandi
if (!$data) { // mengecek apakah data tidak ditemukan
    session_destroy(); // menghancurkan session yang tidak valid
    redirect(BASE_URL . 'auth/login.php'); // mengarahkan ke halaman login
} // menutup pengecekan data
$pesanError = ''; // menyiapkan pesan error
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token CSRF
        $pesanError = 'Token keamanan tidak valid, silakan coba lagi.'; // menyimpan error token
    } else { // jika token valid
        $lama = isset($_POST['password_lama']) && is_string($_POST['password_lama']) ? $_POST['password_lama'] : ''; // membaca kata sandi lama
        $baru = isset($_POST['password_baru']) && is_string($_POST['password_baru']) ? $_POST['password_baru'] : ''; // membaca kata sandi baru
        $konfirmasi = isset($_POST['konfirmasi_baru']) && is_string($_POST['konfirmasi_baru']) ? $_POST['konfirmasi_baru'] : ''; // membaca konfirmasi kata sandi
        if (!password_verify($lama, $data['password'])) { // memeriksa kata sandi lama
            $pesanError = 'Kata sandi lama tidak cocok.'; // menyimpan error kata sandi lama
        } elseif (strlen($baru) < 6) { // memeriksa panjang kata sandi baru
            $pesanError = 'Kata sandi baru minimal 6 karakter.'; // menyimpan error panjang
        } elseif ($baru !== $konfirmasi) { // memeriksa kesamaan konfirmasi
            $pesanError = 'Konfirmasi kata sandi baru tidak sama.'; // menyimpan error konfirmasi
        } elseif (password_verify($baru, $data['password'])) { // mencegah kata sandi baru sama dengan lama
            $pesanError = 'Kata sandi baru harus berbeda dari kata sandi lama.'; // menyimpan error kata sandi sama
        } else { // jika semua validasi lolos
            try { // memulai percobaan penyimpanan kata sandi
                $hashBaru = password_hash($baru, PASSWORD_DEFAULT); // membuat hash baru
                $stmtUpdate = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id'); // menyiapkan query update kata sandi
                $stmtUpdate->execute(array(':password' => $hashBaru, ':id' => $userLogin['id'])); // menjalankan update dengan hash baru
                session_regenerate_id(true); // mengganti id session setelah kata sandi diubah
                csrf_regenerate(); // mengganti token CSRF
                set_flash('sukses', 'Kata sandi berhasil diubah.'); // menyimpan pesan sukses
                redirect(BASE_URL . 'user/profil.php'); // mengarahkan ke halaman profil
            } catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
                error_log('Gagal ganti password: ' . $e->getMessage()); // menyimpan detail error ke log server
                $pesanError = 'Kata sandi sementara gagal diubah, silakan coba lagi.'; // memberi pesan umum yang aman
            } // menutup percobaan database
        } // menutup pengecekan validasi
    } // menutup pengecekan token
} // menutup blok formulir
$judul = 'Ganti Password'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu navbar yang aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul ganti password -->
    <div class="container"> <!-- wadah judul -->
      <h1>Ganti Password</h1> <!-- judul halaman -->
      <p>Gunakan kata sandi baru yang mudah Anda ingat tetapi sulit ditebak.</p> <!-- deskripsi halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir password -->
    <div class="container"> <!-- wadah formulir password -->
      <?php if ($pesanError !== '') { // mengecek apakah ada pesan error ?> <!-- awal pesan error -->
        <div class="flash flash-gagal"><?php echo e($pesanError); // menampilkan pesan error ?></div> <!-- kotak pesan error -->
      <?php } // menutup pengecekan pesan error ?> <!-- akhir pesan error -->
      <form method="post" action="<?php echo e(BASE_URL . 'user/password.php'); // alamat aksi formulir ?>" class="form-kartu"> <!-- formulir ganti password -->
        <?php echo csrf_field(); // menyisipkan token CSRF ?> <!-- token keamanan formulir -->
        <h1>Kata Sandi Baru</h1> <!-- judul kartu formulir -->
        <div class="form-baris"> <!-- baris kata sandi lama -->
          <label for="password_lama" class="label-form">Kata Sandi Lama</label> <!-- label kata sandi lama -->
          <input type="password" id="password_lama" name="password_lama" class="form-kolom" /> <!-- kolom kata sandi lama -->
        </div> <!-- menutup baris kata sandi lama -->
        <div class="form-baris"> <!-- baris kata sandi baru -->
          <label for="password_baru" class="label-form">Kata Sandi Baru</label> <!-- label kata sandi baru -->
          <input type="password" id="password_baru" name="password_baru" class="form-kolom" /> <!-- kolom kata sandi baru -->
        </div> <!-- menutup baris kata sandi baru -->
        <div class="form-baris"> <!-- baris konfirmasi -->
          <label for="konfirmasi_baru" class="label-form">Ulangi Kata Sandi Baru</label> <!-- label konfirmasi -->
          <input type="password" id="konfirmasi_baru" name="konfirmasi_baru" class="form-kolom" /> <!-- kolom konfirmasi -->
        </div> <!-- menutup baris konfirmasi -->
        <div class="form-bawah"> <!-- area tombol -->
          <input type="submit" value="Simpan Kata Sandi" class="tombol" /> <!-- tombol menyimpan -->
          <a href="<?php echo e(BASE_URL . 'user/profil.php'); // alamat profil ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir password -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
