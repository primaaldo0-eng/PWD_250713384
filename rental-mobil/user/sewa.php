<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_login.php'; // memuat sesi dan memastikan pengguna sudah login
$idMobil = isset($_GET['car_id']) && is_numeric($_GET['car_id']) ? (int)$_GET['car_id'] : 0; // membaca ID mobil dari URL
$stmt = $pdo->prepare('SELECT id, nama, gambar, harga_per_hari, status FROM cars WHERE id = :id LIMIT 1'); // menyiapkan query ambil mobil
$stmt->execute(array(':id' => $idMobil)); // menjalankan query ambil mobil
$mobil = $stmt->fetch(); // mengambil data mobil
if (!$mobil) { // memastikan mobil ditemukan
    set_flash('gagal', 'Mobil yang Anda pilih tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'katalog.php'); // kembali ke katalog
} // menutup pengecekan mobil
$errors = array(); // menyiapkan daftar error
$data = array( // menyiapkan nilai awal formulir
    'car_id' => (int)$mobil['id'], // ID mobil
    'tanggal_mulai' => date('Y-m-d'), // tanggal mulai bawaan hari ini
    'tanggal_selesai' => date('Y-m-d', strtotime('+1 day')), // tanggal selesai bawaan besok
    'lokasi_jemput' => '', // alamat lokasi jemput
    'latitude' => '', // koordinat latitude
    'longitude' => '', // koordinat longitude
    'catatan' => '' // catatan tambahan
); // menutup nilai awal formulir
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') { // memproses pengajuan sewa
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan
        $errors[] = 'Token keamanan tidak valid.'; // mencatat error CSRF
    } // menutup pengecekan token
    if ((int)input_post('car_id') !== (int)$mobil['id']) { // memastikan ID mobil tidak diubah
        $errors[] = 'Mobil yang diajukan tidak sesuai.'; // mencatat error ID
    } // menutup pengecekan ID
    $data['tanggal_mulai'] = input_post('tanggal_mulai'); // membaca tanggal mulai
    $data['tanggal_selesai'] = input_post('tanggal_selesai'); // membaca tanggal selesai
    $data['lokasi_jemput'] = input_post('lokasi_jemput'); // membaca lokasi jemput
    $data['latitude'] = input_post('latitude'); // membaca latitude
    $data['longitude'] = input_post('longitude'); // membaca longitude
    $data['catatan'] = input_post('catatan'); // membaca catatan
    if (!tanggal_valid($data['tanggal_mulai']) || !tanggal_valid($data['tanggal_selesai'])) { // memvalidasi format tanggal
        $errors[] = 'Format tanggal harus YYYY-MM-DD.'; // mencatat error format
    } elseif (hitung_hari($data['tanggal_mulai'], $data['tanggal_selesai']) < 1) { // memastikan selesai setelah mulai
        $errors[] = 'Tanggal selesai harus setelah tanggal mulai.'; // mencatat error rentang
    } elseif ($data['tanggal_mulai'] < date('Y-m-d')) { // mencegah sewa di tanggal lalu
        $errors[] = 'Tanggal mulai tidak boleh sebelum hari ini.'; // mencatat error tanggal lalu
    } // menutup pengecekan tanggal
    if ($data['lokasi_jemput'] === '') { // memastikan lokasi jemput terisi
        $errors[] = 'Lokasi jemput wajib diisi.'; // mencatat error lokasi
    } // menutup pengecekan lokasi
    $lat = (float)$data['latitude']; // mengubah latitude ke angka
    $lng = (float)$data['longitude']; // mengubah longitude ke angka
    if (!is_numeric($data['latitude']) || !is_numeric($data['longitude']) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) { // memvalidasi koordinat
        $errors[] = 'Titik lokasi pada peta wajib ditentukan.'; // mencatat error koordinat
    } // menutup pengecekan koordinat
    if ($mobil['status'] !== 'tersedia') { // memastikan mobil tersedia
        $errors[] = 'Mobil ini sedang tidak tersedia.'; // mencatat error status mobil
    } // menutup pengecekan status mobil
    if (count($errors) === 0 && rental_aktif_bertabrakan($pdo, (int)$mobil['id'], $data['tanggal_mulai'], $data['tanggal_selesai'])) { // mengecek bentrok jadwal
        $errors[] = 'Mobil sudah disewa pada rentang tanggal tersebut.'; // mencatat error bentrok
    } // menutup pengecekan bentrok
    if (count($errors) === 0) { // menyimpan sewa bila valid
        try { // menjalankan query dalam pengaman exception
            $jumlahHari = hitung_hari($data['tanggal_mulai'], $data['tanggal_selesai']); // menghitung lama sewa
            $totalHarga = $jumlahHari * (float)$mobil['harga_per_hari']; // menghitung total tagihan
            $simpan = $pdo->prepare('INSERT INTO rentals (user_id, car_id, tanggal_mulai, tanggal_selesai, lokasi_jemput, latitude, longitude, total_hari, total_harga, status, catatan) VALUES (:user, :car, :mulai, :selesai, :lokasi, :lat, :lng, :hari, :total, :status, :catatan)'); // menyiapkan query insert rental
            $simpan->execute(array( // mengirim nilai rental
                ':user' => $_SESSION['user_id'], // ID penyewa
                ':car' => (int)$mobil['id'], // ID mobil
                ':mulai' => $data['tanggal_mulai'], // tanggal mulai
                ':selesai' => $data['tanggal_selesai'], // tanggal selesai
                ':lokasi' => $data['lokasi_jemput'], // lokasi jemput
                ':lat' => $lat, // latitude
                ':lng' => $lng, // longitude
                ':hari' => $jumlahHari, // total hari
                ':total' => $totalHarga, // total harga
                ':status' => 'pending', // status awal menunggu
                ':catatan' => $data['catatan'] // catatan pengguna
            )); // menutup array nilai rental
            $idRental = (int)$pdo->lastInsertId(); // mengambil ID rental baru
            sinkronkan_status_mobil($pdo, (int)$mobil['id']); // menandai mobil sebagai disewa
            set_flash('sukses', 'Pengajuan sewa berhasil dibuat.'); // menyimpan pesan sukses
            redirect(BASE_URL . 'user/rental_detail.php?id=' . $idRental); // pindah ke detail rental
        } catch (PDOException $exception) { // menangani kegagalan database
            $errors[] = 'Gagal menyimpan pengajuan sewa.'; // mencatat error database
        } // menutup pengaman exception
    } // menutup pengecekan penyimpanan
} // menutup pemrosesan POST
$extraHead = '<link rel="stylesheet" type="text/css" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />'; // memuat stylesheet Leaflet
$judul = 'Sewa ' . $mobil['nama']; // menentukan judul halaman
$halamanAktif = 'katalog'; // menandai menu katalog
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-detail"> <!-- area judul form sewa -->
    <div class="container"> <!-- wadah judul -->
      <h1>Ajukan Sewa</h1> <!-- judul halaman -->
      <p>Isi jadwal dan titik jemput, lalu akan dikonfirmasi oleh admin.</p> <!-- deskripsi formulir -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir sewa -->
    <div class="container rental-grid"> <!-- wadah dua kolom formulir dan peta -->
      <div> <!-- kolom formulir -->
        <?php if (count($errors) > 0) { ?> <!-- menampilkan daftar error -->
          <div class="flash flash-gagal"> <!-- kotak pesan error -->
            <ul> <!-- daftar error -->
              <?php foreach ($errors as $error) { ?> <!-- mengulang error -->
                <li><?php echo e($error); ?></li> <!-- menampilkan satu error -->
              <?php } ?> <!-- menutup perulangan error -->
            </ul> <!-- menutup daftar error -->
          </div> <!-- menutup kotak error -->
        <?php } ?> <!-- menutup pengecekan error -->
        <form method="post" action="/rental-mobil/user/sewa.php?car_id=<?php echo (int)$mobil['id']; ?>" class="form-kartu"> <!-- formulir pengajuan sewa -->
          <?php echo csrf_field(); ?> <!-- menyisipkan token CSRF -->
          <input type="hidden" name="car_id" value="<?php echo (int)$mobil['id']; ?>" /> <!-- menyimpan ID mobil -->
          <input type="hidden" id="harga_satuan" value="<?php echo e($mobil['harga_per_hari']); ?>" /> <!-- menyimpan tarif untuk kalkulasi JS -->
          <div class="form-baris"> <!-- baris mobil dipilih -->
            <img src="<?php echo e(BASE_URL . $mobil['gambar']); ?>" alt="Foto <?php echo e($mobil['nama']); ?>" class="foto-form" width="180" height="130" /> <!-- foto mobil -->
            <div> <!-- keterangan mobil -->
              <h1><?php echo e($mobil['nama']); ?></h1> <!-- nama mobil -->
              <p class="harga"><?php echo rupiah($mobil['harga_per_hari']); ?> / hari</p> <!-- tarif per hari -->
            </div> <!-- menutup keterangan mobil -->
          </div> <!-- menutup baris mobil -->
          <div class="form-baris"> <!-- baris tanggal sewa -->
            <label for="tanggal_mulai" class="label-form">Tanggal Mulai</label> <!-- label tanggal mulai -->
            <input type="text" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo e($data['tanggal_mulai']); ?>" class="form-kolom" /> <!-- kolom tanggal mulai -->
            <label for="tanggal_selesai" class="label-form">Tanggal Selesai</label> <!-- label tanggal selesai -->
            <input type="text" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo e($data['tanggal_selesai']); ?>" class="form-kolom" /> <!-- kolom tanggal selesai -->
          </div> <!-- menutup baris tanggal -->
          <div class="form-baris"> <!-- baris lokasi jemput -->
            <label for="lokasi_jemput" class="label-form">Lokasi Jemput</label> <!-- label lokasi -->
            <input type="text" id="lokasi_jemput" name="lokasi_jemput" value="<?php echo e($data['lokasi_jemput']); ?>" class="form-kolom" /> <!-- kolom lokasi -->
            <button type="button" id="tombol-lokasi" class="tombol tombol-outline">Gunakan Lokasi Saya</button> <!-- tombol geolocation -->
          </div> <!-- menutup baris lokasi -->
          <input type="hidden" id="latitude" name="latitude" value="<?php echo e($data['latitude']); ?>" /> <!-- menyimpan latitude -->
          <input type="hidden" id="longitude" name="longitude" value="<?php echo e($data['longitude']); ?>" /> <!-- menyimpan longitude -->
          <div id="info-koordinat" class="info-koordinat">Geser penanda atau klik peta untuk menentukan titik jemput.</div> <!-- keterangan koordinat -->
          <div id="peta" class="peta"></div> <!-- wadah peta Leaflet -->
          <div class="form-baris"> <!-- baris catatan -->
            <label for="catatan" class="label-form">Catatan</label> <!-- label catatan -->
            <textarea id="catatan" name="catatan" rows="3" class="form-kolom"><?php echo e($data['catatan']); ?></textarea> <!-- kolom catatan -->
          </div> <!-- menutup baris catatan -->
          <p class="harga">Estimasi: <span id="ringkasan-harga">0 hari - Rp 0</span></p> <!-- ringkasan biaya dari JS -->
          <div class="form-bawah"> <!-- area tombol formulir -->
            <input type="submit" value="Kirim Pengajuan" class="tombol" /> <!-- tombol mengirim sewa -->
            <a href="/rental-mobil/detail.php?id=<?php echo (int)$mobil['id']; ?>" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
          </div> <!-- menutup area tombol -->
        </form> <!-- menutup formulir sewa -->
      </div> <!-- menutup kolom formulir -->
      <div> <!-- kolom keterangan tambahan -->
        <h2 class="judul-bagian">Cara Sewa</h2> <!-- judul panduan -->
        <ul class="timeline"> <!-- daftar langkah sewa -->
          <li class="timeline-item">Pilih tanggal sewa dan titik jemput pada peta.</li> <!-- langkah pertama -->
          <li class="timeline-item">Kirim pengajuan dan tunggu konfirmasi admin.</li> <!-- langkah kedua -->
          <li class="timeline-item">Pantau status penyewaan melalui halaman detail rental.</li> <!-- langkah ketiga -->
        </ul> <!-- menutup daftar langkah -->
      </div> <!-- menutup kolom keterangan -->
    </div> <!-- menutup wadah formulir dan peta -->
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script> <!-- memuat pustaka Leaflet -->
    <script type="text/javascript" src="/rental-mobil/assets/js/peta-sewa.js"></script> <!-- memuat logika peta dan harga -->
  </div> <!-- menutup bagian formulir sewa -->
<?php require __DIR__ . '/../includes/footer.php'; // memuat footer dan menutup halaman ?> <!-- menutup halaman -->
