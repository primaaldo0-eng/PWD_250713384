// Mengambil tombol yang dipakai untuk membuka dan menutup menu
var tombolMenu = document.getElementById('btn-menu'); // menyimpan elemen tombol menu
var menu = document.getElementById('menu'); // menyimpan elemen daftar menu
if (tombolMenu && menu) { // memastikan kedua elemen tersedia
  tombolMenu.onclick = function () { // menjalankan fungsi saat tombol diklik
    if (menu.className.indexOf('tampil') === -1) { // mengecek apakah menu belum tampil
      menu.className = menu.className + ' tampil'; // menambahkan kelas tampil pada menu
    } else { // jika menu sudah tampil
      menu.className = menu.className.replace(' tampil', ''); // menghapus kelas tampil dari menu
    } // menutup pilihan kondisi menu
  }; // menutup fungsi klik tombol
} // menutup pengecekan elemen menu
