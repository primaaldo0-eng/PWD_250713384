<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/config/koneksi.php'; // mengambil koneksi PDO dan session
require_once __DIR__ . '/includes/fungsi.php'; // mengambil fungsi escape dan flash
$judul = 'Beranda'; // menentukan judul halaman
$halamanAktif = 'beranda'; // menandai menu aktif
$stmt = $pdo->prepare('SELECT id, nama, merek, tahun, harga_per_hari, gambar, status FROM cars WHERE status = :status ORDER BY id ASC LIMIT 3'); // menyiapkan query mobil unggulan
$stmt->execute(array(':status' => 'tersedia')); // menjalankan query dengan status tersedia
$mobilUnggulan = $stmt->fetchAll(); // mengambil semua baris mobil unggulan
require_once __DIR__ . '/includes/header.php'; // memuat kepala halaman dan navbar
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero"> <!-- area judul utama beranda -->
    <div class="container hero-content"> <!-- wadah isi judul -->
      <h1>Sewa Mobil Mewah dengan Mudah</h1> <!-- judul utama aplikasi -->
      <p>Pilih mobil favorit, tentukan tanggal sewa, dan nikmati perjalanan Anda dengan nyaman.</p> <!-- deskripsi singkat -->
      <a href="<?php echo e(BASE_URL . 'katalog.php'); // alamat katalog ?>" class="tombol">Lihat Katalog</a> <!-- tombol ke katalog -->
      <a href="<?php echo e(BASE_URL . 'auth/daftar.php'); // alamat pendaftaran ?>" class="tombol tombol-outline">Daftar Sekarang</a> <!-- tombol ke registrasi -->
    </div> <!-- menutup wadah isi judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian mobil pilihan -->
    <div class="container"> <!-- wadah mobil pilihan -->
      <h2 class="judul-bagian">Mobil Pilihan</h2> <!-- judul bagian -->
      <p class="sub-judul">Beberapa mobil terbaik yang siap disewa hari ini.</p> <!-- keterangan bagian -->
      <?php if (count($mobilUnggulan) === 0) { // mengecek apakah tidak ada mobil ?> <!-- awal kondisi kosong -->
        <div class="kosong">Belum ada mobil tersedia saat ini.</div> <!-- pesan ketika data kosong -->
      <?php } else { // jika ada mobil ditampilkan ?> <!-- awal kondisi ada data -->
        <div class="grid-mobil"> <!-- wadah kartu mobil -->
          <?php foreach ($mobilUnggulan as $mobil) { // mengulang setiap mobil unggulan ?> <!-- awal perulangan mobil -->
            <div class="kartu"> <!-- kartu satu mobil -->
              <a href="detail.php?id=<?php echo e($mobil['id']); // id mobil ?>"><img src="<?php echo e(BASE_URL . $mobil['gambar']); // alamat foto ?>" alt="Foto <?php echo e($mobil['nama']); // nama mobil ?>" width="400" height="190" /></a> <!-- foto mobil yang tautannya ke detail -->
              <div class="kartu-isi"> <!-- isi kartu mobil -->
                <h3><?php echo e($mobil['nama']); // nama mobil ?></h3> <!-- nama mobil -->
                <p><?php echo e($mobil['merek'] . ' ' . $mobil['tahun']); // merek dan tahun ?></p> <!-- merek dan tahun mobil -->
                <p class="harga">Rp <?php echo e(number_format($mobil['harga_per_hari'], 0, ',', '.')); // harga per hari ?> / hari</p> <!-- harga sewa per hari -->
                <a href="detail.php?id=<?php echo e($mobil['id']); // id mobil ?>" class="tombol">Lihat Detail</a> <!-- tombol menuju detail -->
              </div> <!-- menutup isi kartu -->
            </div> <!-- menutup kartu mobil -->
          <?php } // menutup perulangan mobil ?> <!-- akhir perulangan mobil -->
        </div> <!-- menutup wadah kartu mobil -->
      <?php } // menutup kondisi data ?> <!-- akhir kondisi data -->
    </div> <!-- menutup wadah mobil pilihan -->
  </div> <!-- menutup bagian mobil pilihan -->
  <div class="bagian bagian-muted"> <!-- bagian cara menyewa -->
    <div class="container"> <!-- wadah cara menyewa -->
      <h2 class="judul-bagian">Cara Menyewa</h2> <!-- judul cara menyewa -->
      <div class="grid-info"> <!-- wadah tiga langkah -->
        <div class="info-kotak"><h3>1. Pilih Mobil</h3><p>Buka katalog lalu pilih mobil yang paling sesuai dengan kebutuhan Anda.</p></div> <!-- langkah pertama -->
        <div class="info-kotak"><h3>2. Isi Data Sewa</h3><p>Tentukan tanggal sewa dan lokasi penjemputan dengan lengkap.</p></div> <!-- langkah kedua -->
        <div class="info-kotak"><h3>3. Konfirmasi</h3><p>Admin akan memeriksa pengajuan lalu mengubah status transaksi Anda.</p></div> <!-- langkah ketiga -->
      </div> <!-- menutup wadah tiga langkah -->
    </div> <!-- menutup wadah cara menyewa -->
  </div> <!-- menutup bagian cara menyewa -->
<?php require_once __DIR__ . '/includes/footer.php'; // memuat kaki halaman ?> <!-- menutup halaman -->
