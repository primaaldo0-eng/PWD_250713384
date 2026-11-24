<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/includes/fungsi.php'; // mengambil fungsi escape dan flash
$judul = 'Katalog Mobil'; // menentukan judul halaman
$halamanAktif = 'katalog'; // menandai menu aktif
$katakunci = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : ''; // membaca kata kunci pencarian
$sql = 'SELECT id, nama, merek, model, tahun, harga_per_hari, gambar, status FROM cars'; // menyiapkan query dasar
$params = array(); // menyiapkan daftar parameter query
if ($katakunci !== '') { // menambahkan filter jika ada kata kunci
    $sql .= ' WHERE nama LIKE :q1 OR merek LIKE :q2 OR model LIKE :q3'; // menambahkan syarat pencarian dengan tiga parameter berbeda
    $params[':q1'] = '%' . $katakunci . '%'; // mengisi parameter nama
    $params[':q2'] = '%' . $katakunci . '%'; // mengisi parameter merek
    $params[':q3'] = '%' . $katakunci . '%'; // mengisi parameter model
} // menutup penambahan filter
$sql .= ' ORDER BY id ASC'; // mengurutkan hasil berdasarkan id
$stmt = $pdo->prepare($sql); // menyiapkan query dengan prepared statement
$stmt->execute($params); // menjalankan query beserta parameter
$mobilList = $stmt->fetchAll(); // mengambil semua baris mobil
require_once __DIR__ . '/includes/header.php'; // memuat kepala halaman dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-katalog"> <!-- area judul katalog -->
    <div class="container"> <!-- wadah judul katalog -->
      <h1>Katalog Mobil</h1> <!-- judul halaman katalog -->
      <p>Temukan mobil yang sesuai dengan kebutuhan perjalanan Anda.</p> <!-- deskripsi katalog -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul katalog -->
  <div class="bagian"> <!-- bagian daftar mobil -->
    <div class="container"> <!-- wadah daftar mobil -->
      <form method="get" action="<?php echo e(BASE_URL . 'katalog.php'); // alamat aksi formulir ?>" class="pencarian"> <!-- formulir pencarian -->
        <div> <!-- wadah label dan kolom cari -->
          <label for="q" class="label-form">Cari mobil</label> <!-- label kolom pencarian -->
          <input type="text" id="q" name="q" value="<?php echo e($katakunci); // nilai kata kunci ?>" /> <!-- kolom kata kunci -->
        </div> <!-- menutup wadah kolom cari -->
        <input type="submit" value="Cari" class="tombol" /> <!-- tombol menjalankan pencarian -->
      </form> <!-- menutup formulir pencarian -->
      <?php if (count($mobilList) === 0) { // mengecek apakah hasil pencarian kosong ?> <!-- awal kondisi kosong -->
        <div class="kosong">Tidak ada mobil yang cocok dengan pencarian Anda.</div> <!-- pesan hasil kosong -->
      <?php } else { // jika ada hasil ditampilkan ?> <!-- awal kondisi ada hasil -->
        <div class="grid-mobil"> <!-- wadah kartu hasil katalog -->
          <?php foreach ($mobilList as $mobil) { // mengulang setiap mobil hasil query ?> <!-- awal perulangan mobil -->
            <div class="kartu"> <!-- kartu satu mobil -->
              <a href="detail.php?id=<?php echo e($mobil['id']); // id mobil ?>"><img src="<?php echo e(BASE_URL . $mobil['gambar']); // alamat foto ?>" alt="Foto <?php echo e($mobil['nama']); // nama mobil ?>" width="400" height="190" /></a> <!-- foto mobil menuju detail -->
              <div class="kartu-isi"> <!-- isi kartu mobil -->
                <h3><?php echo e($mobil['nama']); // nama mobil ?></h3> <!-- nama mobil -->
                <p><?php echo e($mobil['merek'] . ' ' . $mobil['model'] . ' ' . $mobil['tahun']); // spesifikasi singkat ?></p> <!-- merek, model, dan tahun -->
                <p class="harga">Rp <?php echo e(number_format($mobil['harga_per_hari'], 0, ',', '.')); // harga per hari ?> / hari</p> <!-- harga sewa -->
                <p><span class="label-status <?php echo e($mobil['status']); // status mobil ?>"><?php echo e($mobil['status']); // status mobil ?></span></p> <!-- label status -->
                <a href="detail.php?id=<?php echo e($mobil['id']); // id mobil ?>" class="tombol">Lihat Detail</a> <!-- tombol detail -->
              </div> <!-- menutup isi kartu -->
            </div> <!-- menutup kartu mobil -->
          <?php } // menutup perulangan mobil ?> <!-- akhir perulangan mobil -->
        </div> <!-- menutup wadah kartu katalog -->
      <?php } // menutup kondisi hasil ?> <!-- akhir kondisi hasil -->
    </div> <!-- menutup wadah daftar mobil -->
  </div> <!-- menutup bagian daftar mobil -->
<?php require_once __DIR__ . '/includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
