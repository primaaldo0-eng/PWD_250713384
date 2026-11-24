<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman hanya untuk admin
$q = isset($_GET['q']) ? trim((string)$_GET['q']) : ''; // membaca kata kunci pencarian dari URL
$where = ''; // menyiapkan klausa pencarian
$params = array(); // menyiapkan parameter prepared statement
if ($q !== '') { // menjalankan pencarian bila kata kunci tidak kosong
    $where = ' WHERE nama LIKE :q1 OR merek LIKE :q2 OR model LIKE :q3 OR plat_nomor LIKE :q4'; // mencocokkan beberapa kolom
    $cari = '%' . $q . '%'; // membuat pola pencarian
    $params = array(':q1' => $cari, ':q2' => $cari, ':q3' => $cari, ':q4' => $cari); // mengisi parameter pencarian
} // menutup pengecekan kata kunci
$sql = 'SELECT id, nama, merek, model, tahun, plat_nomor, harga_per_hari, gambar, status FROM cars' . $where . ' ORDER BY id DESC'; // merangkai query daftar mobil
$stmt = $pdo->prepare($sql); // menyiapkan query agar aman dari SQL injection
$stmt->execute($params); // menjalankan query dengan parameter
$daftarMobil = $stmt->fetchAll(); // mengambil seluruh baris hasil
$title = 'Kelola Mobil - Rental Mobil'; // menentukan judul halaman
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul halaman kelola mobil -->
    <div class="container"> <!-- wadah judul -->
      <h1>Kelola Data Mobil</h1> <!-- judul utama halaman -->
      <p>Tambah, ubah, dan hapus data armada rental.</p> <!-- deskripsi singkat halaman -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian tabel data mobil -->
    <div class="container"> <!-- wadah isi tabel -->
      <div class="admin-toolbar"> <!-- bilah alat atas tabel -->
        <form method="get" action="/rental-mobil/admin/mobil.php" class="pencarian"> <!-- formulir pencarian mobil -->
          <label for="q" class="label-form">Cari:</label> <!-- label kolom pencarian -->
          <input type="text" id="q" name="q" value="<?php echo e($q); ?>" class="form-kolom" /> <!-- kolom kata kunci -->
          <input type="submit" value="Cari" class="tombol" /> <!-- tombol menjalankan pencarian -->
          <?php if ($q !== '') { ?> <!-- menampilkan tautan hapus pencarian -->
            <a href="/rental-mobil/admin/mobil.php" class="tombol tombol-outline">Reset</a> <!-- tautan mengosongkan pencarian -->
          <?php } ?> <!-- menutup pengecekan kata kunci -->
        </form> <!-- menutup formulir pencarian -->
        <a href="/rental-mobil/admin/mobil_tambah.php" class="tombol">+ Tambah Mobil</a> <!-- tautan menambah mobil baru -->
      </div> <!-- menutup bilah alat atas -->
      <div class="tabel-wrapper"> <!-- pembungkus tabel agar dapat bergulir -->
        <table class="tabel-admin"> <!-- tabel daftar mobil -->
          <thead> <!-- bagian kepala tabel -->
            <tr> <!-- baris header tabel -->
              <th>Foto</th> <!-- kolom foto mobil -->
              <th>Nama Mobil</th> <!-- kolom nama mobil -->
              <th>Merek / Model</th> <!-- kolom merek dan model -->
              <th>Tahun</th> <!-- kolom tahun pembuatan -->
              <th>Harga per Hari</th> <!-- kolom tarif sewa -->
              <th>Status</th> <!-- kolom ketersediaan -->
              <th>Aksi</th> <!-- kolom tombol aksi -->
            </tr> <!-- menutup baris header -->
          </thead> <!-- menutup bagian kepala tabel -->
          <tbody> <!-- bagian isi tabel -->
            <?php if (count($daftarMobil) === 0) { ?> <!-- menampilkan pesan bila tidak ada data -->
              <tr><td colspan="7" class="kosong">Tidak ada data mobil yang cocok.</td></tr> <!-- baris pesan kosong -->
            <?php } else { ?> <!-- memulai penampilan data -->
              <?php foreach ($daftarMobil as $mobil) { ?> <!-- mengulang setiap baris mobil -->
                <tr> <!-- membuka baris data mobil -->
                  <td><img src="<?php echo e(BASE_URL . $mobil['gambar']); ?>" alt="Foto <?php echo e($mobil['nama']); ?>" class="foto-tabel" width="70" height="50" /></td> <!-- menampilkan foto mobil -->
                  <td><?php echo e($mobil['nama']); ?><br /><small><?php echo e($mobil['plat_nomor']); ?></small></td> <!-- menampilkan nama dan plat -->
                  <td><?php echo e($mobil['merek']); ?><br /><small><?php echo e($mobil['model']); ?></small></td> <!-- menampilkan merek dan model -->
                  <td><?php echo e($mobil['tahun']); ?></td> <!-- menampilkan tahun -->
                  <td>Rp <?php echo number_format((float)$mobil['harga_per_hari'], 0, ',', '.'); ?></td> <!-- menampilkan tarif format rupiah -->
                  <td><span class="label-status status-<?php echo e($mobil['status']); ?>"><?php echo e(ucfirst($mobil['status'])); ?></span></td> <!-- menampilkan label status -->
                  <td> <!-- membuka sel aksi -->
                    <div class="aksi-tabel"> <!-- wadah tombol aksi -->
                      <a href="/rental-mobil/admin/mobil_edit.php?id=<?php echo (int)$mobil['id']; ?>" class="tombol tombol-outline tombol-kecil">Edit</a> <!-- tautan mengubah data -->
                      <form method="post" action="/rental-mobil/admin/mobil_hapus.php" class="form-hapus"> <!-- formulir penghapusan data -->
                        <?php echo csrf_field(); ?> <!-- menyisipkan token CSRF -->
                        <input type="hidden" name="id" value="<?php echo (int)$mobil['id']; ?>" /> <!-- menyimpan ID mobil -->
                        <input type="hidden" name="nama_mobil" value="<?php echo e($mobil['nama']); ?>" class="nama-hapus" /> <!-- menyimpan nama untuk pesan konfirmasi -->
                        <input type="submit" value="Hapus" class="tombol tombol-bahaya tombol-kecil" /> <!-- tombol menghapus data -->
                      </form> <!-- menutup formulir penghapusan -->
                    </div> <!-- menutup wadah tombol aksi -->
                  </td> <!-- menutup sel aksi -->
                </tr> <!-- menutup baris data mobil -->
              <?php } ?> <!-- mengakhiri perulangan mobil -->
            <?php } ?> <!-- menutup pengecekan data -->
          </tbody> <!-- menutup bagian isi tabel -->
        </table> <!-- menutup tabel daftar mobil -->
      </div> <!-- menutup pembungkus tabel -->
      <script type="text/javascript" src="/rental-mobil/assets/js/admin.js"></script> <!-- memuat konfirmasi hapus dari JS eksternal -->
    </div> <!-- menutup wadah isi tabel -->
  </div> <!-- menutup bagian tabel data mobil -->
<?php require __DIR__ . '/../includes/footer.php'; ?> <!-- memuat footer dan menutup halaman -->
