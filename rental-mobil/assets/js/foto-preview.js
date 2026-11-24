// Mengambil elemen yang dipakai untuk pratinjau foto
var inputFoto = document.getElementById('foto'); // menyimpan kolom pilih berkas
var pratinjau = document.getElementById('pratinjau'); // menyimpan gambar pratinjau
var infoFile = document.getElementById('info-file'); // menyimpan keterangan berkas
var gambarAwal = pratinjau ? pratinjau.getAttribute('src') : ''; // menyimpan alamat foto lama
var ukuranMaks = 2 * 1024 * 1024; // menentukan batas ukuran 2 MB
var jenisDiizinkan = ['image/jpeg', 'image/png']; // menentukan MIME yang diterima
function tulisInfo(teks) { // membuat fungsi untuk menulis keterangan berkas
  if (infoFile) { infoFile.textContent = teks; } // menulis teks jika elemen tersedia
} // menutup fungsi tulisInfo
if (inputFoto && pratinjau) { // memastikan elemen tersedia sebelum memasang penanganan
  inputFoto.addEventListener('change', function () { // menjalankan fungsi saat berkas dipilih
    if (inputFoto.files.length === 0) { // mengecek apakah pilihan dibatalkan
      pratinjau.src = gambarAwal; // mengembalikan pratinjau ke foto lama
      tulisInfo('Pilih berkas untuk melihat pratinjau.'); // menulis keterangan awal
      return; // menghentikan penanganan
    } // menutup pengecekan pilihan kosong
    var berkas = inputFoto.files[0]; // mengambil berkas pertama
    if (berkas.size > ukuranMaks) { // mengecek ukuran berkas di sisi klien
      inputFoto.value = ''; // menghapus pilihan berkas
      pratinjau.src = gambarAwal; // mengembalikan pratinjau ke foto lama
      tulisInfo('Ukuran foto maksimal 2 MB.'); // menampilkan pesan error ukuran
      return; // menghentikan penanganan
    } // menutup pengecekan ukuran
    if (jenisDiizinkan.indexOf(berkas.type) === -1) { // mengecek jenis berkas di sisi klien
      inputFoto.value = ''; // menghapus pilihan berkas
      pratinjau.src = gambarAwal; // mengembalikan pratinjau ke foto lama
      tulisInfo('Jenis foto harus JPG, JPEG, atau PNG.'); // menampilkan pesan error jenis
      return; // menghentikan penanganan
    } // menutup pengecekan jenis
    var pembaca = new FileReader(); // membuat pembaca berkas
    pembaca.onload = function (acara) { // menjalankan fungsi setelah berkas terbaca
      pratinjau.src = acara.target.result; // menampilkan gambar hasil pratinjau
    }; // menutup fungsi setelah berkas terbaca
    pembaca.readAsDataURL(berkas); // membaca berkas sebagai alamat data
    tulisInfo('Berkas siap: ' + berkas.name); // menampilkan nama berkas yang dipilih
  }); // menutup penanganan perubahan berkas
} // menutup pengecekan elemen
