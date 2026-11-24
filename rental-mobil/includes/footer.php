<?php // membuka PHP sebelum ada output
require_once __DIR__ . '/../config/koneksi.php'; // mengambil koneksi dan session
require_once __DIR__ . '/fungsi.php'; // mengambil fungsi escape
$base = e(BASE_URL); // menyiapkan URL dasar yang aman
$urlLogoText = e(BASE_URL . 'assets/img/logo/logo-text.png'); // menyiapkan alamat logo teks
?> <!-- menutup blok PHP sebelum menulis XHTML -->
    </div> <!-- menutup area konten utama -->
    <div class="footer"> <!-- area kaki halaman -->
      <div class="container"> <!-- wadah isi footer -->
        <img src="<?php echo $urlLogoText; // alamat logo teks ?>" alt="Rental Mobil" width="130" height="34" /> <!-- logo di footer -->
        <p>Sistem Rental Mobil - Tugas Besar Pemrograman Web Dasar.</p> <!-- keterangan singkat aplikasi -->
        <p>Hubungi kami di rental@example.com atau telepon 0812-0000-0000.</p> <!-- data kontak contoh -->
      </div> <!-- menutup wadah footer -->
    </div> <!-- menutup footer -->
  </div> <!-- menutup pembungkus halaman -->
  <script type="text/javascript" src="<?php echo e(BASE_URL . 'assets/js/main.js'); // alamat JavaScript eksternal ?>"></script> <!-- memuat JavaScript eksternal -->
</body> <!-- menutup badan dokumen -->
</html> <!-- menutup dokumen XHTML -->
