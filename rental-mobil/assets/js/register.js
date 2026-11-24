// Mengambil elemen-elemen yang dipakai pada formulir pendaftaran
var formDaftar = document.getElementById('form-daftar'); // menyimpan elemen formulir
var kolomUsername = document.getElementById('username'); // menyimpan kolom username
var kolomEmail = document.getElementById('email'); // menyimpan kolom email
var kolomPassword = document.getElementById('password'); // menyimpan kolom kata sandi
var kolomKonfirmasi = document.getElementById('konfirmasi'); // menyimpan kolom konfirmasi kata sandi
var kolomNama = document.getElementById('nama_lengkap'); // menyimpan kolom nama lengkap
var statusUsername = document.getElementById('status-username'); // menyimpan tempat status username
var statusEmail = document.getElementById('status-email'); // menyimpan tempat status email
var penunda; // menyimpan penunda waktu pengecekan
var usernameTersedia = false; // menandai apakah username sudah dinyatakan tersedia
var emailTersedia = false; // menandai apakah email sudah dinyatakan tersedia
function tulisStatus(elemen, teks, sukses) { // membuat fungsi untuk menulis status ketersediaan
  if (!elemen) { return; } // berhenti jika elemen tidak ada
  elemen.textContent = teks; // menulis teks status dengan aman
  elemen.className = sukses ? 'status-cek status-ok' : 'status-cek status-gagal'; // mengubah kelas sesuai hasil
} // menutup fungsi tulisStatus
function tulisError(id, teks) { // membuat fungsi untuk menulis pesan error per kolom
  var elemen = document.getElementById(id); // mencari elemen pesan berdasarkan id
  if (elemen) { elemen.textContent = teks; } // menulis teks error jika elemen tersedia
} // menutup fungsi tulisError
function cekKetersediaan() { // membuat fungsi untuk memeriksa username dan email ke server
  var username = kolomUsername ? kolomUsername.value.trim() : ''; // mengambil nilai username
  var email = kolomEmail ? kolomEmail.value.trim() : ''; // mengambil nilai email
  if (username === '' && email === '') { // mengecek apakah kedua kolom kosong
    tulisStatus(statusUsername, '', false); // mengosongkan status username
    tulisStatus(statusEmail, '', false); // mengosongkan status email
    return; // menghentikan pengecekan
  } // menutup pengecekan kolom kosong
  var alamat = '../api/cek_user.php?username=' + encodeURIComponent(username) + '&email=' + encodeURIComponent(email); // merangkai alamat API dengan data yang aman
  fetch(alamat) // mengirim permintaan ke API pengecekan
    .then(function (respons) { // menangani jawaban dari server
      if (!respons.ok) { throw new Error('Gagal menghubungi server.'); } // melempar error jika status tidak baik
      return respons.json(); // mengubah jawaban menjadi objek JSON
    }) // menutup penanganan respons
    .then(function (data) { // menangani data JSON yang sudah diubah
      if (data.username) { usernameTersedia = data.username.tersedia === true; tulisStatus(statusUsername, data.username.pesan, usernameTersedia); } // menyimpan dan menampilkan hasil username
      if (data.email) { emailTersedia = data.email.tersedia === true; tulisStatus(statusEmail, data.email.pesan, emailTersedia); } // menyimpan dan menampilkan hasil email
    }) // menutup penanganan data
    .catch(function () { // menangani kegagalan jaringan atau server
      usernameTersedia = false; // menganggap username belum tersedia
      emailTersedia = false; // menganggap email belum tersedia
      tulisStatus(statusUsername, 'Pengecekan gagal, silakan coba lagi.', false); // menampilkan pesan gagal pada username
      tulisStatus(statusEmail, 'Pengecekan gagal, silakan coba lagi.', false); // menampilkan pesan gagal pada email
    }); // menutup penanganan error
} // menutup fungsi cekKetersediaan
function cekDenganJeda() { // membuat fungsi untuk menunda pengecekan
  usernameTersedia = false; // mengubah status username menjadi belum dicek
  emailTersedia = false; // mengubah status email menjadi belum dicek
  clearTimeout(penunda); // membatalkan pengecekan sebelumnya
  penunda = setTimeout(cekKetersediaan, 350); // menjalankan pengecekan setelah jeda 350 milidetik
} // menutup fungsi cekDenganJeda
function validasiForm() { // membuat fungsi untuk memeriksa isi formulir di sisi klien
  var daftarError = []; // menyiapkan daftar pesan error
  var nama = kolomNama ? kolomNama.value.trim() : ''; // mengambil nilai nama lengkap
  var username = kolomUsername ? kolomUsername.value.trim() : ''; // mengambil nilai username
  var email = kolomEmail ? kolomEmail.value.trim() : ''; // mengambil nilai email
  var password = kolomPassword ? kolomPassword.value : ''; // mengambil nilai kata sandi
  var konfirmasi = kolomKonfirmasi ? kolomKonfirmasi.value : ''; // mengambil nilai konfirmasi
  tulisError('err-nama', ''); // mengosongkan error nama
  tulisError('err-username', ''); // mengosongkan error username
  tulisError('err-email', ''); // mengosongkan error email
  tulisError('err-password', ''); // mengosongkan error kata sandi
  tulisError('err-konfirmasi', ''); // mengosongkan error konfirmasi
  if (nama.length < 2) { daftarError.push(['err-nama', 'Nama lengkap minimal 2 karakter.']); } // memeriksa nama lengkap
  if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) { daftarError.push(['err-username', 'Username 3-20 karakter, hanya huruf, angka, dan garis bawah.']); } // memeriksa format username
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { daftarError.push(['err-email', 'Format email tidak valid.']); } // memeriksa format email
  if (password.length < 6) { daftarError.push(['err-password', 'Kata sandi minimal 6 karakter.']); } // memeriksa panjang kata sandi
  if (password !== konfirmasi) { daftarError.push(['err-konfirmasi', 'Konfirmasi kata sandi tidak sama.']); } // memeriksa kesamaan kata sandi
  for (var i = 0; i < daftarError.length; i++) { tulisError(daftarError[i][0], daftarError[i][1]); } // menampilkan semua pesan error
  return daftarError.length === 0; // mengembalikan true jika tidak ada error
} // menutup fungsi validasiForm
if (formDaftar) { // memastikan formulir tersedia sebelum memasang penanganan
  if (kolomUsername) { kolomUsername.addEventListener('input', cekDenganJeda); } // memasang pengecekan pada kolom username
  if (kolomEmail) { kolomEmail.addEventListener('input', cekDenganJeda); } // memasang pengecekan pada kolom email
  formDaftar.addEventListener('submit', function (event) { // menjalankan pengecekan saat formulir dikirim
    if (!validasiForm()) { event.preventDefault(); return; } // menghentikan pengiriman jika validasi gagal
    if (!usernameTersedia || !emailTersedia) { // mengecek apakah hasil API belum tersedia
      event.preventDefault(); // menghentikan pengiriman sementara
      tulisStatus(statusUsername, 'Sedang memeriksa username, tunggu sebentar.', false); // memberi tahu pengguna untuk menunggu
      tulisStatus(statusEmail, 'Sedang memeriksa email, tunggu sebentar.', false); // memberi tahu pengguna untuk menunggu
      cekKetersediaan(); // menjalankan pengecekan ulang segera
    } // menutup pengecekan status API
  }); // menutup penanganan submit
} // menutup pengecekan formulir
