<?php // membuka tag PHP dengan komentar agar tidak ada output sebelum session dimulai
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi PDO dan memastikan session sudah aktif
function e($nilai) { // membuat fungsi untuk mengamankan setiap output ke halaman
    return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // mengubah karakter khusus menjadi entitas HTML
} // menutup fungsi e
function csrf_token() { // membuat fungsi untuk mengambil atau membuat token CSRF
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) { // mengecek apakah token belum ada atau tidak valid
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // membuat token acak baru dan menyimpannya di session
    } // menutup blok pengecekan token
    return $_SESSION['csrf_token']; // mengembalikan token yang siap dipakai
} // menutup fungsi csrf_token
function csrf_regenerate() { // membuat fungsi untuk mengganti token setelah login
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // membuat token acak baru
    return $_SESSION['csrf_token']; // mengembalikan token baru
} // menutup fungsi csrf_regenerate
function csrf_field() { // membuat fungsi untuk mencetak input hidden berisi token
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '" />'; // mengembalikan markup XHTML dengan nilai yang sudah aman
} // menutup fungsi csrf_field
function csrf_verify($token) { // membuat fungsi untuk memeriksa token CSRF yang dikirim form
    if (!isset($_SESSION['csrf_token']) || !is_string($token)) { // mengecek kecukupan data token
        return false; // menolak permintaan jika token tidak ada
    } // menutup blok pengecekan data token
    return hash_equals($_SESSION['csrf_token'], $token); // membandingkan token dengan cara yang aman
} // menutup fungsi csrf_verify
function redirect($lokasi) { // membuat fungsi untuk berpindah halaman
    header('Location: ' . $lokasi); // mengirim header pindah halaman
    exit; // menghentikan kode berikutnya
} // menutup fungsi redirect
function set_flash($jenis, $pesan) { // membuat fungsi untuk menyimpan pesan sementara
    $_SESSION['flash'] = array('jenis' => $jenis, 'pesan' => $pesan); // menyimpan jenis dan isi pesan di session
} // menutup fungsi set_flash
function ambil_flash() { // membuat fungsi untuk mengambil pesan sementara
    if (empty($_SESSION['flash'])) { // mengecek apakah tidak ada pesan
        return null; // mengembalikan nilai kosong
    } // menutup blok pengecekan pesan
    $flash = $_SESSION['flash']; // menyalin pesan ke variabel lokal
    unset($_SESSION['flash']); // menghapus pesan dari session agar tidak muncul dua kali
    return $flash; // mengembalikan pesan yang sudah diambil
} // menutup fungsi ambil_flash
function input_post($kunci, $default = '') { // membuat fungsi untuk membaca data POST dengan aman
    if (!isset($_POST[$kunci]) || !is_string($_POST[$kunci])) { // mengecek apakah data POST tidak ada atau bukan teks
        return $default; // mengembalikan nilai bawaan
    } // menutup blok pengecekan data POST
    return trim($_POST[$kunci]); // mengembalikan data yang sudah dibersihkan dari spasi tepi
} // menutup fungsi input_post
function sanitasi_redirect($lokasi) { // membuat fungsi untuk membatasi arah pindah halaman agar tetap internal
    if (!is_string($lokasi) || $lokasi === '') { // mengecek apakah lokasi kosong atau bukan teks
        return BASE_URL . 'index.php'; // mengembalikan halaman beranda sebagai nilai aman
    } // menutup pengecekan lokasi kosong
    if (strpos($lokasi, '\\') !== false || strpos($lokasi, '..') !== false || strpos($lokasi, '//') === 0 || preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*:#', $lokasi)) { // menolak backslash, traversal, alamat relatif protokol, dan skema URL
        return BASE_URL . 'index.php'; // mengembalikan beranda jika lokasi mencurigakan
    } // menutup pengecekan lokasi mencurigakan
    if (strpos($lokasi, '/') === 0) { // mengecek apakah lokasi diawali garis miring
        if (strpos($lokasi, BASE_URL) !== 0) { // mengecek apakah lokasi berada di dalam folder aplikasi
            return BASE_URL . 'index.php'; // menolak path lokal di luar aplikasi
        } // menutup pengecekan folder aplikasi
        return $lokasi; // mengembalikan lokasi internal yang sudah aman
    } // menutup pengecekan lokasi absolut
    return BASE_URL . ltrim($lokasi, '/'); // mengubah lokasi relatif menjadi alamat penuh aplikasi
} // menutup fungsi sanitasi_redirect
function hapus_gambar_lama($path) { // membuat fungsi untuk menghapus gambar lama hasil upload
    if (!is_string($path) || strpos($path, 'assets/uploads/') !== 0) { // memastikan path berada di folder upload aplikasi
        return false; // menolak penghapusan file di luar folder upload
    } // menutup pengecekan folder upload
    if (strpos($path, '..') !== false) { // menolak path yang mengandung traversal
        return false; // menghentikan penghapusan
    } // menutup pengecekan traversal
    $lengkap = BASE_PATH . $path; // merangkai path lengkap di server
    if (!is_file($lengkap)) { // mengecek apakah file benar-benar ada
        return false; // menghentikan penghapusan jika file tidak ada
    } // menutup pengecekan file
    return unlink($lengkap); // menghapus file lama dan mengembalikan hasilnya
} // menutup fungsi hapus_gambar_lama
function unggah_gambar($field, $folder, $ukuranMaks = 2097152) { // membuat fungsi upload gambar dengan validasi lengkap
    $hasil = array('sukses' => false, 'path' => '', 'pesan' => ''); // menyiapkan hasil upload
    $folderDiizinkan = array('mobil', 'profil'); // membatasi folder tujuan upload
    if (!in_array($folder, $folderDiizinkan, true)) { // mengecek folder tujuan
        $hasil['pesan'] = 'Folder upload tidak dikenal.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan folder
    if (!isset($_FILES[$field])) { // mengecek keberadaan berkas
        $hasil['pesan'] = 'Berkas tidak ditemukan.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan berkas
    $berkas = $_FILES[$field]; // mengambil data berkas
    if ($berkas['error'] !== UPLOAD_ERR_OK) { // mengecek status upload PHP
        $hasil['pesan'] = 'Gagal mengunggah berkas.'; // menulis pesan error umum
        return $hasil; // menghentikan upload
    } // menutup pengecekan status upload
    if ($berkas['size'] <= 0 || $berkas['size'] > $ukuranMaks) { // membatasi ukuran berkas
        $hasil['pesan'] = 'Ukuran gambar harus lebih dari 0 dan maksimal 2 MB.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan ukuran
    $ekstensi = strtolower(pathinfo($berkas['name'], PATHINFO_EXTENSION)); // mengambil ekstensi huruf kecil
    $petaJenis = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'); // memetakan ekstensi ke MIME
    if (!isset($petaJenis[$ekstensi])) { // mengecek ekstensi yang diizinkan
        $hasil['pesan'] = 'Format gambar harus JPG, JPEG, atau PNG.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan ekstensi
    if (!is_uploaded_file($berkas['tmp_name'])) { // memastikan berkas berasal dari HTTP POST
        $hasil['pesan'] = 'Berkas bukan hasil unggahan yang valid.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan asal berkas
    $jenis = ''; // menyiapkan variabel MIME
    if (function_exists('finfo_open')) { // memakai finfo bila tersedia
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // membuka pendeteksi MIME
        $jenis = finfo_file($finfo, $berkas['tmp_name']); // membaca MIME asli berkas
        finfo_close($finfo); // menutup pendeteksi MIME
    } else { // memakai fallback bila finfo tidak ada
        $jenis = function_exists('mime_content_type') ? mime_content_type($berkas['tmp_name']) : ''; // membaca MIME dengan cara alternatif
    } // menutup pengecekan finfo
    if ($jenis !== $petaJenis[$ekstensi]) { // mencocokkan MIME asli dengan ekstensi
        $hasil['pesan'] = 'Jenis berkas tidak sesuai dengan ekstensi.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan MIME
    $infoGambar = @getimagesize($berkas['tmp_name']); // memastikan berkas benar-benar gambar
    if ($infoGambar === false) { // mengecek hasil deteksi gambar
        $hasil['pesan'] = 'Berkas bukan gambar yang valid.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan gambar
    $namaUnik = bin2hex(random_bytes(16)) . '.' . $ekstensi; // membuat nama berkas unik
    $path = 'assets/uploads/' . $folder . '/' . $namaUnik; // merangkai path relatif aplikasi
    if (!move_uploaded_file($berkas['tmp_name'], BASE_PATH . $path)) { // memindahkan berkas ke folder tujuan
        $hasil['pesan'] = 'Gagal menyimpan gambar di server.'; // menulis pesan error
        return $hasil; // menghentikan upload
    } // menutup pengecekan pemindahan berkas
    $hasil['sukses'] = true; // menandai upload berhasil
    $hasil['path'] = $path; // menyimpan path hasil upload
    $hasil['pesan'] = 'Gambar berhasil diunggah.'; // menulis pesan sukses
    return $hasil; // mengembalikan hasil upload
} // menutup fungsi unggah_gambar
function tanggal_valid($nilai) { // membuat fungsi untuk memvalidasi tanggal format YYYY-MM-DD
    if (!is_string($nilai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) { // mengecek pola dasar tanggal
        return false; // menolak format yang salah
    } // menutup pengecekan pola
    $bagian = explode('-', $nilai); // memecah tahun, bulan, dan tanggal
    return checkdate((int)$bagian[1], (int)$bagian[2], (int)$bagian[0]); // memastikan tanggal benar secara kalender
} // menutup fungsi tanggal_valid
function hitung_hari($mulai, $selesai) { // membuat fungsi menghitung selisih hari sewa
    if (!tanggal_valid($mulai) || !tanggal_valid($selesai)) { // memastikan kedua tanggal valid
        return 0; // mengembalikan nol bila tanggal tidak valid
    } // menutup pengecekan tanggal
    $awal = DateTime::createFromFormat('!Y-m-d', $mulai); // membuat objek tanggal mulai
    $akhir = DateTime::createFromFormat('!Y-m-d', $selesai); // membuat objek tanggal selesai
    if ($akhir <= $awal) { return 0; } // menolak tanggal selesai yang tidak setelah tanggal mulai
    $selisih = (int)$awal->diff($akhir)->days; // mengambil selisih hari
    return $selisih >= 1 ? $selisih : 0; // mengembalikan minimal satu hari sewa
} // menutup fungsi hitung_hari
function rupiah($angka) { // membuat fungsi format mata uang rupiah
    return 'Rp ' . number_format((float)$angka, 0, ',', '.'); // mengembalikan angka dengan format Indonesia
} // menutup fungsi rupiah
function label_status_rental($status) { // membuat fungsi label status rental dalam bahasa Indonesia
    $peta = array('pending' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'); // memetakan status ke label
    return isset($peta[$status]) ? $peta[$status] : ucfirst((string)$status); // mengembalikan label yang sesuai
} // menutup fungsi label_status_rental
function rental_aktif_bertabrakan($pdo, $carId, $mulai, $selesai, $kecualiId = 0) { // membuat fungsi cek bentrok jadwal sewa aktif
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE car_id = :car_id AND id <> :kecuali AND status IN (:s1, :s2) AND tanggal_mulai <= :selesai AND tanggal_selesai >= :mulai'); // menyiapkan query pencarian bentrok
    $stmt->execute(array(':car_id' => (int)$carId, ':kecuali' => (int)$kecualiId, ':s1' => 'pending', ':s2' => 'dikonfirmasi', ':selesai' => $selesai, ':mulai' => $mulai)); // menjalankan query dengan parameter
    return $stmt->fetchColumn() > 0; // mengembalikan true bila ada jadwal aktif yang bertabrakan
} // menutup fungsi rental_aktif_bertabrakan
function sinkronkan_status_mobil($pdo, $carId) { // membuat fungsi menyelaraskan status mobil setelah transaksi berubah
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE car_id = :car_id AND status IN (:s1, :s2)'); // menyiapkan hitungan sewa aktif
    $stmt->execute(array(':car_id' => (int)$carId, ':s1' => 'pending', ':s2' => 'dikonfirmasi')); // menjalankan hitungan sewa aktif
    $status = $stmt->fetchColumn() > 0 ? 'disewa' : 'tersedia'; // menentukan status mobil berdasarkan sewa aktif
    $ubah = $pdo->prepare('UPDATE cars SET status = :status WHERE id = :car_id'); // menyiapkan update status mobil
    $ubah->execute(array(':status' => $status, ':car_id' => (int)$carId)); // menjalankan update status sesuai sewa aktif
} // menutup fungsi sinkronkan_status_mobil
