<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID rental dari URL
$stmt = $pdo->prepare('SELECT r.id, r.tanggal_mulai, r.tanggal_selesai, r.total_hari, r.total_harga, r.status, c.id AS car_id, c.nama AS nama_mobil, c.harga_per_hari FROM rentals r JOIN cars c ON c.id = r.car_id WHERE r.id = :id AND r.user_id = :user LIMIT 1'); // menyiapkan query rental milik pengguna
$stmt->execute(array(':id' => $id, ':user' => $_SESSION['user_id'])); // menjalankan query dengan pembatas pengguna
$rental = $stmt->fetch(); // mengambil data rental
if (!$rental) { // memastikan rental ditemukan
    set_flash('gagal', 'Data rental tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/riwayat.php'); // kembali ke riwayat
} // menutup pengecekan rental
if ($rental['status'] !== 'pending' && $rental['status'] !== 'dikonfirmasi') { // membatasi perubahan pada rental aktif
    set_flash('gagal', 'Status rental ini tidak dapat diubah lagi.'); // menyimpan pesan error
    redirect(BASE_URL . 'user/rental_detail.php?id=' . (int)$rental['id']); // kembali ke detail
} // menutup pengecekan status
$errors = array(); // menyiapkan daftar error
$data = array( // menyiapkan nilai formulir
    'id' => (int)$rental['id'], // ID rental
    'tanggal_mulai' => $rental['tanggal_mulai'], // tanggal mulai lama
    'tanggal_selesai' => $rental['tanggal_selesai'] // tanggal selesai lama
); // menutup nilai formulir
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') { // memproses perubahan tanggal
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
        $errors[] = 'Token keamanan tidak valid.'; // mencatat error CSRF
    } // menutup pengecekan token
    if ((int)input_post('id') !== (int)$rental['id']) { // memastikan ID tidak diubah
        $errors[] = 'ID rental tidak sesuai.'; // mencatat error ID
    } // menutup pengecekan ID
    $data['tanggal_mulai'] = input_post('tanggal_mulai'); // membaca tanggal mulai baru
    $data['tanggal_selesai'] = input_post('tanggal_selesai'); // membaca tanggal selesai baru
    if (!tanggal_valid($data['tanggal_mulai']) || !tanggal_valid($data['tanggal_selesai'])) { // memvalidasi format tanggal
        $errors[] = 'Format tanggal harus YYYY-MM-DD.'; // mencatat error format
    } elseif (hitung_hari($data['tanggal_mulai'], $data['tanggal_selesai']) < 1) { // memastikan rentang valid
        $errors[] = 'Tanggal selesai harus setelah tanggal mulai.'; // mencatat error rentang
    } elseif ($data['tanggal_mulai'] < date('Y-m-d')) { // mencegah tanggal mulai di masa lalu
        $errors[] = 'Tanggal mulai tidak boleh sebelum hari ini.'; // mencatat error tanggal
    } // menutup pengecekan tanggal
    if (count($errors) === 0 && rental_aktif_bertabrakan($pdo, (int)$rental['car_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], (int)$rental['id'])) { // mengecek bentrok dengan rental lain
        $errors[] = 'Mobil sudah disewa pada rentang tanggal tersebut.'; // mencatat error bentrok
    } // menutup pengecekan bentrok
    if (count($errors) === 0) { // menyimpan perubahan bila valid
        try { // menjalankan query dalam pengaman exception
            $jumlahHari = hitung_hari($data['tanggal_mulai'], $data['tanggal_selesai']); // menghitung ulang lama sewa
            $totalHarga = $jumlahHari * (float)$rental['harga_per_hari']; // menghitung ulang total
            $ubah = $pdo->prepare('UPDATE rentals SET tanggal_mulai = :mulai, tanggal_selesai = :selesai, total_hari = :hari, total_harga = :total WHERE id = :id'); // menyiapkan query update
            $ubah->execute(array(':mulai' => $data['tanggal_mulai'], ':selesai' => $data['tanggal_selesai'], ':hari' => $jumlahHari, ':total' => $totalHarga, ':id' => (int)$rental['id'])); // menjalankan update tanggal
            set_flash('sukses', 'Tanggal sewa berhasil diperbarui.'); // menyimpan pesan sukses
            redirect(BASE_URL . 'user/rental_detail.php?id=' . (int)$rental['id']); // kembali ke detail rental
        } catch (PDOException $exception) { // menangani kegagalan database
            $errors[] = 'Gagal memperbarui tanggal sewa.'; // mencatat error database
        } // menutup pengaman exception
    } // menutup pengecekan penyimpanan
} // menutup pemrosesan POST
$judul = 'Ubah Tanggal Rental #' . $rental['id']; // menentukan judul halaman
$halamanAktif = 'riwayat'; // menandai menu riwayat
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul ubah tanggal -->
    <div class="container"> <!-- wadah judul -->
      <h1>Ubah Tanggal Sewa</h1> <!-- judul halaman -->
      <p>Mobil: <?php echo e($rental['nama_mobil']); ?></p> <!-- nama mobil yang disewa -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir ubah tanggal -->
    <div class="container"> <!-- wadah formulir -->
      <?php if (count($errors) > 0) { ?> <!-- menampilkan error -->
        <div class="flash flash-gagal"> <!-- kotak error -->
          <ul> <!-- daftar error -->
            <?php foreach ($errors as $error) { ?> <!-- mengulang error -->
              <li><?php echo e($error); ?></li> <!-- menampilkan error -->
            <?php } ?> <!-- menutup perulangan error -->
          </ul> <!-- menutup daftar error -->
        </div> <!-- menutup kotak error -->
      <?php } ?> <!-- menutup pengecekan error -->
      <form method="post" action="/rental-mobil/user/rental_ubah.php?id=<?php echo (int)$rental['id']; ?>" class="form-kartu"> <!-- formulir ubah tanggal -->
        <?php echo csrf_field(); ?> <!-- token CSRF -->
        <input type="hidden" name="id" value="<?php echo (int)$rental['id']; ?>" /> <!-- ID rental -->
        <div class="form-baris"> <!-- baris tanggal mulai -->
          <label for="tanggal_mulai" class="label-form">Tanggal Mulai</label> <!-- label mulai -->
          <input type="text" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo e($data['tanggal_mulai']); ?>" class="form-kolom" /> <!-- kolom mulai -->
        </div> <!-- menutup baris mulai -->
        <div class="form-baris"> <!-- baris tanggal selesai -->
          <label for="tanggal_selesai" class="label-form">Tanggal Selesai</label> <!-- label selesai -->
          <input type="text" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo e($data['tanggal_selesai']); ?>" class="form-kolom" /> <!-- kolom selesai -->
        </div> <!-- menutup baris selesai -->
        <p class="info-koordinat">Tarif per hari: <?php echo e(rupiah($rental['harga_per_hari'])); ?>. Total dihitung ulang otomatis.</p> <!-- keterangan tarif -->
        <div class="form-bawah"> <!-- area tombol -->
          <input type="submit" value="Simpan Tanggal" class="tombol" /> <!-- tombol simpan -->
          <a href="/rental-mobil/user/rental_detail.php?id=<?php echo (int)$rental['id']; ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
