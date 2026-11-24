<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../includes/cek_login.php'; // memastikan hanya pengguna login yang bisa mengunggah foto
$fotoSaatIni = !empty($userLogin['foto']) && is_file(BASE_PATH . $userLogin['foto']) ? $userLogin['foto'] : 'assets/img/profil/default.jpg'; // memakai foto default jika file tidak ada
$pesanError = ''; // menyiapkan pesan error upload
$metode = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''; // membaca metode request dengan aman
if ($metode === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token CSRF
        $pesanError = 'Token keamanan tidak valid, silakan coba lagi.'; // menyimpan error token
    } elseif (!isset($_FILES['foto'])) { // mengecek apakah berkas dikirim
        $pesanError = 'Silakan pilih foto terlebih dahulu.'; // menyimpan error berkas kosong
    } else { // jika berkas tersedia
        $berkas = $_FILES['foto']; // menyalin data berkas
        $ukuranMaks = 2 * 1024 * 1024; // menentukan batas maksimal 2 MB
        if ($berkas['error'] !== UPLOAD_ERR_OK) { // mengecek status upload dari PHP
            $pesanError = 'Upload gagal, silakan coba lagi.'; // menyimpan error umum upload
        } elseif ($berkas['size'] === 0 || $berkas['size'] > $ukuranMaks) { // mengecek ukuran berkas
            $pesanError = 'Ukuran foto harus lebih dari 0 dan maksimal 2 MB.'; // menyimpan error ukuran
        } else { // jika ukuran sesuai
            $ekstensi = strtolower(pathinfo($berkas['name'], PATHINFO_EXTENSION)); // mengambil ekstensi berkas
            $petaJenis = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'); // memetakan ekstensi ke MIME yang diizinkan
            if (!isset($petaJenis[$ekstensi])) { // mengecek apakah ekstensi diizinkan
                $pesanError = 'Format foto harus JPG, JPEG, atau PNG.'; // menyimpan error ekstensi
            } elseif (!is_uploaded_file($berkas['tmp_name'])) { // memastikan berkas benar-benar hasil upload
                $pesanError = 'Berkas tidak valid, silakan coba lagi.'; // menyimpan error berkas
            } else { // jika ekstensi dan asal berkas valid
                if (function_exists('finfo_open')) { // mengecek apakah ekstensi finfo tersedia
                    $info = finfo_open(FILEINFO_MIME_TYPE); // membuka pembaca MIME
                    $jenis = finfo_file($info, $berkas['tmp_name']); // membaca MIME dari berkas
                    finfo_close($info); // menutup pembaca MIME
                } else { // jika finfo tidak tersedia
                    $jenis = mime_content_type($berkas['tmp_name']); // membaca MIME dengan fungsi cadangan
                } // menutup pengecekan finfo
                if ($jenis !== $petaJenis[$ekstensi]) { // membandingkan MIME dengan ekstensi
                    $pesanError = 'Jenis foto tidak sesuai dengan ekstensi berkas.'; // menyimpan error MIME
                } else { // jika MIME sesuai
                    $cekGambar = @getimagesize($berkas['tmp_name']); // memastikan berkas benar-benar gambar
                    if ($cekGambar === false) { // mengecek hasil pemeriksaan gambar
                        $pesanError = 'File bukan gambar yang valid.'; // menyimpan error gambar
                    } else { // jika gambar valid
                        $namaUnik = bin2hex(random_bytes(16)) . '.' . $ekstensi; // membuat nama berkas unik
                        $tujuan = BASE_PATH . 'assets/uploads/profil/' . $namaUnik; // merangkai path tujuan di server
                        if (move_uploaded_file($berkas['tmp_name'], $tujuan)) { // memindahkan berkas ke folder upload
                            try { // memulai percobaan penyimpanan ke database
                                $pathDatabase = 'assets/uploads/profil/' . $namaUnik; // menyiapkan path untuk database
                                $stmt = $pdo->prepare('UPDATE users SET foto = :foto WHERE id = :id'); // menyiapkan query update foto
                                $stmt->execute(array(':foto' => $pathDatabase, ':id' => $userLogin['id'])); // menjalankan update foto
                                hapus_gambar_lama($userLogin['foto']); // menghapus foto lama hasil upload jika ada
                                $_SESSION['foto'] = $pathDatabase; // memperbarui foto di session
                                set_flash('sukses', 'Foto profil berhasil diperbarui.'); // menyimpan pesan sukses
                                redirect(BASE_URL . 'user/profil.php'); // mengarahkan ke halaman profil
                            } catch (PDOException $e) { // menangkap error database tanpa menampilkannya mentah
                                error_log('Gagal simpan foto: ' . $e->getMessage()); // menyimpan detail error ke log server
                                hapus_gambar_lama($pathDatabase); // menghapus berkas baru jika database gagal
                                $pesanError = 'Foto sementara gagal disimpan, silakan coba lagi.'; // memberi pesan umum yang aman
                            } // menutup percobaan database
                        } else { // jika berkas gagal dipindahkan
                            $pesanError = 'Foto gagal disimpan di server, silakan coba lagi.'; // menyimpan error pemindahan
                        } // menutup pengecekan pemindahan
                    } // menutup blok gambar valid
                } // menutup pengecekan MIME
            } // menutup pengecekan asal berkas
        } // menutup pengecekan ukuran
    } // menutup pengecekan berkas
} // menutup blok upload
$judul = 'Ganti Foto Profil'; // menentukan judul halaman
$halamanAktif = ''; // tidak ada menu navbar yang aktif
require_once __DIR__ . '/../includes/header.php'; // memuat header dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul upload foto -->
    <div class="container"> <!-- wadah judul -->
      <h1>Ganti Foto Profil</h1> <!-- judul halaman -->
      <p>Pilih foto JPG, JPEG, atau PNG dengan ukuran maksimal 2 MB.</p> <!-- deskripsi aturan upload -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir upload -->
    <div class="container"> <!-- wadah formulir upload -->
      <?php if ($pesanError !== '') { // mengecek apakah ada pesan error ?> <!-- awal pesan error -->
        <div class="flash flash-gagal"><?php echo e($pesanError); // menampilkan pesan error ?></div> <!-- kotak pesan error -->
      <?php } // menutup pengecekan pesan error ?> <!-- akhir pesan error -->
      <form method="post" action="<?php echo e(BASE_URL . 'user/foto.php'); // alamat aksi formulir ?>" enctype="multipart/form-data" class="form-kartu"> <!-- formulir upload foto -->
        <?php echo csrf_field(); // menyisipkan token CSRF ?> <!-- token keamanan formulir -->
        <h1>Pratinjau Foto</h1> <!-- judul kartu upload -->
        <img id="pratinjau" src="<?php echo e(BASE_URL . $fotoSaatIni); // alamat foto saat ini ?>" alt="Pratinjau foto profil" class="pratinjau" width="180" height="180" /> <!-- pratinjau foto -->
        <div id="info-file" class="info-file">Pilih berkas untuk melihat pratinjau.</div> <!-- keterangan berkas -->
        <div class="form-baris"> <!-- baris pilih berkas -->
          <label for="foto" class="label-form">Foto Profil</label> <!-- label berkas -->
          <input type="file" id="foto" name="foto" class="form-kolom" /> <!-- kolom pilih berkas -->
        </div> <!-- menutup baris berkas -->
        <div class="form-bawah"> <!-- area tombol -->
          <input type="submit" value="Simpan Foto" class="tombol" /> <!-- tombol menyimpan foto -->
          <a href="<?php echo e(BASE_URL . 'user/profil.php'); // alamat profil ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir upload -->
      <script type="text/javascript" src="<?php echo e(BASE_URL . 'assets/js/foto-preview.js'); // alamat skrip pratinjau ?>"></script> <!-- memuat pratinjau foto -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian upload -->
<?php require_once __DIR__ . '/../includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
