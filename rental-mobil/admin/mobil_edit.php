<?php // menandai awal kode PHP
require __DIR__ . '/../includes/cek_admin.php'; // memuat sesi dan membatasi halaman hanya untuk admin
$statusDiizinkan = array('tersedia', 'disewa', 'perbaikan'); // daftar status yang valid
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // membaca ID mobil dari URL
$stmt = $pdo->prepare('SELECT id, nama, merek, model, tahun, warna, plat_nomor, harga_per_hari, gambar, status, deskripsi FROM cars WHERE id = :id'); // menyiapkan query ambil data
$stmt->execute(array(':id' => $id)); // menjalankan query ambil data
$mobil = $stmt->fetch(); // mengambil baris mobil
if (!$mobil) { // memastikan mobil ditemukan
    set_flash('gagal', 'Data mobil tidak ditemukan.'); // menyimpan pesan error
    redirect(BASE_URL . 'admin/mobil.php'); // kembali ke daftar mobil
} // menutup pengecekan mobil
$errors = array(); // menyiapkan daftar pesan error
$data = array( // menyiapkan nilai formulir dari database
    'id' => (int)$mobil['id'], // ID mobil
    'nama' => $mobil['nama'], // nama mobil
    'merek' => $mobil['merek'], // merek mobil
    'model' => $mobil['model'], // model mobil
    'tahun' => (string)$mobil['tahun'], // tahun mobil
    'warna' => $mobil['warna'], // warna mobil
    'plat_nomor' => $mobil['plat_nomor'], // plat nomor
    'harga_per_hari' => (string)$mobil['harga_per_hari'], // tarif harian
    'status' => $mobil['status'], // status mobil
    'deskripsi' => $mobil['deskripsi'], // deskripsi mobil
    'gambar' => $mobil['gambar'] // path gambar lama
); // menutup nilai formulir
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') { // memproses formulir saat dikirim
    if (!csrf_verify(input_post('csrf_token'))) { // memeriksa token keamanan formulir
        $errors[] = 'Token keamanan tidak valid.'; // mencatat error CSRF
    } // menutup pengecekan token
    if ((int)input_post('id') !== $id) { // memastikan ID tidak diubah saat pengiriman
        $errors[] = 'ID mobil tidak sesuai.'; // mencatat error ID
    } // menutup pengecekan ID
    $data['nama'] = input_post('nama'); // membaca nama mobil
    $data['merek'] = input_post('merek'); // membaca merek mobil
    $data['model'] = input_post('model'); // membaca model mobil
    $data['tahun'] = input_post('tahun'); // membaca tahun mobil
    $data['warna'] = input_post('warna'); // membaca warna mobil
    $data['plat_nomor'] = strtoupper(input_post('plat_nomor')); // membaca dan menyeragamkan plat
    $data['harga_per_hari'] = input_post('harga_per_hari'); // membaca tarif harian
    $data['status'] = input_post('status'); // membaca status mobil
    $data['deskripsi'] = input_post('deskripsi'); // membaca deskripsi mobil
    if ($data['nama'] === '' || $data['merek'] === '' || $data['model'] === '') { // memastikan data utama terisi
        $errors[] = 'Nama, merek, dan model wajib diisi.'; // mencatat error data utama
    } // menutup pengecekan data utama
    if (!ctype_digit($data['tahun']) || (int)$data['tahun'] < 1990 || (int)$data['tahun'] > (int)date('Y') + 1) { // memvalidasi tahun
        $errors[] = 'Tahun harus berupa angka antara 1990 dan tahun depan.'; // mencatat error tahun
    } // menutup pengecekan tahun
    $hargaBersih = str_replace(',', '.', $data['harga_per_hari']); // menyeragamkan tanda desimal
    if (!is_numeric($hargaBersih) || (float)$hargaBersih <= 0) { // memvalidasi tarif
        $errors[] = 'Harga per hari harus berupa angka lebih dari 0.'; // mencatat error tarif
    } // menutup pengecekan tarif
    if ($data['plat_nomor'] === '') { // memastikan plat terisi
        $errors[] = 'Plat nomor wajib diisi.'; // mencatat error plat
    } // menutup pengecekan plat
    if (!in_array($data['status'], $statusDiizinkan, true)) { // memvalidasi status
        $errors[] = 'Status tidak dikenal.'; // mencatat error status
    } // menutup pengecekan status
    if ($data['plat_nomor'] !== '') { // mengecek duplikasi plat bila plat terisi
        $cekPlat = $pdo->prepare('SELECT COUNT(*) FROM cars WHERE plat_nomor = :plat AND id <> :id'); // menyiapkan pengecekan plat
        $cekPlat->execute(array(':plat' => $data['plat_nomor'], ':id' => $id)); // menjalankan pengecekan plat
        if ($cekPlat->fetchColumn() > 0) { // mengecek hasil duplikasi
            $errors[] = 'Plat nomor sudah digunakan mobil lain.'; // mencatat error duplikasi
        } // menutup pengecekan hasil
    } // menutup pengecekan duplikasi
    $pathBaru = ''; // menyiapkan path gambar baru
    $adaGambarBaru = isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE; // mengecek pilihan berkas baru
    if ($adaGambarBaru && count($errors) === 0) { // mengunggah gambar baru bila ada dan validasi lolos
        $upload = unggah_gambar('gambar', 'mobil'); // menjalankan upload gambar mobil
        if (!$upload['sukses']) { // mengecek hasil upload
            $errors[] = $upload['pesan']; // mencatat error upload
        } else { // menyiapkan path bila upload berhasil
            $pathBaru = $upload['path']; // menyimpan path gambar baru
        } // menutup pengecekan hasil upload
    } // menutup pengecekan upload
    if (count($errors) === 0) { // menyimpan perubahan bila seluruh validasi lolos
        try { // menjalankan query dalam pengaman exception
            $gambarFinal = $pathBaru !== '' ? $pathBaru : $data['gambar']; // memakai gambar baru atau gambar lama
            $simpan = $pdo->prepare('UPDATE cars SET nama = :nama, merek = :merek, model = :model, tahun = :tahun, warna = :warna, plat_nomor = :plat, harga_per_hari = :harga, gambar = :gambar, status = :status, deskripsi = :deskripsi WHERE id = :id'); // menyiapkan query update
            $simpan->execute(array( // mengirim nilai ke query
                ':nama' => $data['nama'], // nama mobil
                ':merek' => $data['merek'], // merek mobil
                ':model' => $data['model'], // model mobil
                ':tahun' => (int)$data['tahun'], // tahun mobil
                ':warna' => $data['warna'], // warna mobil
                ':plat' => $data['plat_nomor'], // plat nomor
                ':harga' => (float)$hargaBersih, // tarif harian
                ':gambar' => $gambarFinal, // path gambar final
                ':status' => $data['status'], // status mobil
                ':deskripsi' => $data['deskripsi'], // deskripsi mobil
                ':id' => $id // ID mobil
            )); // menutup array nilai query
            if ($pathBaru !== '') { // menghapus gambar lama bila gambar baru berhasil dipakai
                hapus_gambar_lama($data['gambar']); // menghapus file lama dari server
            } // menutup pengecekan gambar baru
            set_flash('sukses', 'Data mobil berhasil diperbarui.'); // menyimpan pesan sukses
            redirect(BASE_URL . 'admin/mobil.php'); // pindah ke daftar mobil
        } catch (PDOException $exception) { // menangani kegagalan database
            hapus_gambar_lama($pathBaru); // menghapus gambar baru bila update gagal
            $errors[] = 'Gagal memperbarui data mobil.'; // mencatat error database
        } // menutup pengaman exception
    } // menutup pengecekan penyimpanan
} // menutup pemrosesan POST
$title = 'Edit Mobil - Rental Mobil'; // menentukan judul halaman
require __DIR__ . '/../includes/header.php'; // memuat header dan pesan flash
?> <!-- menutup blok PHP sebelum menulis XHTML -->
  <div class="hero hero-profil"> <!-- area judul formulir edit -->
    <div class="container"> <!-- wadah judul -->
      <h1>Edit Data Mobil</h1> <!-- judul halaman -->
      <p>Perbarui data armada dan ganti foto bila diperlukan.</p> <!-- deskripsi formulir -->
    </div> <!-- menutup wadah judul -->
  </div> <!-- menutup area judul -->
  <div class="bagian"> <!-- bagian formulir edit mobil -->
    <div class="container"> <!-- wadah formulir -->
      <?php if (count($errors) > 0) { ?> <!-- menampilkan daftar error -->
        <div class="flash flash-gagal"> <!-- kotak pesan error -->
          <ul> <!-- daftar error -->
            <?php foreach ($errors as $error) { ?> <!-- mengulang setiap error -->
              <li><?php echo e($error); ?></li> <!-- menampilkan satu error -->
            <?php } ?> <!-- menutup perulangan error -->
          </ul> <!-- menutup daftar error -->
        </div> <!-- menutup kotak pesan error -->
      <?php } ?> <!-- menutup pengecekan error -->
      <form method="post" action="/rental-mobil/admin/mobil_edit.php?id=<?php echo $id; ?>" enctype="multipart/form-data" class="form-kartu"> <!-- formulir edit mobil -->
        <?php echo csrf_field(); ?> <!-- menyisipkan token CSRF -->
        <input type="hidden" name="id" value="<?php echo $id; ?>" /> <!-- menyimpan ID mobil -->
        <div class="form-baris"> <!-- baris nama mobil -->
          <label for="nama" class="label-form">Nama Mobil</label> <!-- label nama -->
          <input type="text" id="nama" name="nama" value="<?php echo e($data['nama']); ?>" class="form-kolom" /> <!-- kolom nama -->
        </div> <!-- menutup baris nama -->
        <div class="form-baris"> <!-- baris merek dan model -->
          <label for="merek" class="label-form">Merek</label> <!-- label merek -->
          <input type="text" id="merek" name="merek" value="<?php echo e($data['merek']); ?>" class="form-kolom" /> <!-- kolom merek -->
          <label for="model" class="label-form">Model</label> <!-- label model -->
          <input type="text" id="model" name="model" value="<?php echo e($data['model']); ?>" class="form-kolom" /> <!-- kolom model -->
        </div> <!-- menutup baris merek dan model -->
        <div class="form-baris"> <!-- baris tahun dan warna -->
          <label for="tahun" class="label-form">Tahun</label> <!-- label tahun -->
          <input type="text" id="tahun" name="tahun" value="<?php echo e($data['tahun']); ?>" class="form-kolom" /> <!-- kolom tahun -->
          <label for="warna" class="label-form">Warna</label> <!-- label warna -->
          <input type="text" id="warna" name="warna" value="<?php echo e($data['warna']); ?>" class="form-kolom" /> <!-- kolom warna -->
        </div> <!-- menutup baris tahun dan warna -->
        <div class="form-baris"> <!-- baris plat dan harga -->
          <label for="plat_nomor" class="label-form">Plat Nomor</label> <!-- label plat -->
          <input type="text" id="plat_nomor" name="plat_nomor" value="<?php echo e($data['plat_nomor']); ?>" class="form-kolom" /> <!-- kolom plat -->
          <label for="harga_per_hari" class="label-form">Harga per Hari</label> <!-- label tarif -->
          <input type="text" id="harga_per_hari" name="harga_per_hari" value="<?php echo e($data['harga_per_hari']); ?>" class="form-kolom" /> <!-- kolom tarif -->
        </div> <!-- menutup baris plat dan harga -->
        <div class="form-baris"> <!-- baris status -->
          <label for="status" class="label-form">Status</label> <!-- label status -->
          <select id="status" name="status" class="form-kolom"> <!-- pilihan status -->
            <?php foreach ($statusDiizinkan as $status) { ?> <!-- mengulang status yang valid -->
              <option value="<?php echo e($status); ?>" <?php if ($data['status'] === $status) { ?>selected="selected"<?php } ?>><?php echo e(ucfirst($status)); ?></option> <!-- satu pilihan status -->
            <?php } ?> <!-- menutup perulangan status -->
          </select> <!-- menutup pilihan status -->
        </div> <!-- menutup baris status -->
        <div class="form-baris"> <!-- baris deskripsi -->
          <label for="deskripsi" class="label-form">Deskripsi</label> <!-- label deskripsi -->
          <textarea id="deskripsi" name="deskripsi" rows="4" class="form-kolom"><?php echo e($data['deskripsi']); ?></textarea> <!-- kolom deskripsi -->
        </div> <!-- menutup baris deskripsi -->
        <div class="form-baris"> <!-- baris unggah foto -->
          <img id="pratinjau" src="<?php echo e(BASE_URL . $data['gambar']); ?>" alt="Foto mobil saat ini" class="pratinjau" width="180" height="180" /> <!-- pratinjau foto lama -->
          <div id="info-file" class="info-file">Biarkan kosong untuk mempertahankan foto lama.</div> <!-- keterangan berkas -->
          <label for="foto" class="label-form">Ganti Foto Mobil</label> <!-- label berkas -->
          <input type="file" id="foto" name="gambar" class="form-kolom" /> <!-- kolom unggah foto opsional -->
        </div> <!-- menutup baris unggah foto -->
        <div class="form-bawah"> <!-- area tombol formulir -->
          <input type="submit" value="Simpan Perubahan" class="tombol" /> <!-- tombol menyimpan perubahan -->
          <a href="/rental-mobil/admin/mobil.php" class="tombol tombol-outline">Batal</a> <!-- tombol kembali -->
        </div> <!-- menutup area tombol -->
      </form> <!-- menutup formulir edit -->
      <script type="text/javascript" src="/rental-mobil/assets/js/foto-preview.js"></script> <!-- memuat pratinjau foto -->
    </div> <!-- menutup wadah formulir -->
  </div> <!-- menutup bagian formulir edit mobil -->
<?php require __DIR__ . '/../includes/footer.php'; ?> <!-- memuat footer dan menutup halaman -->
