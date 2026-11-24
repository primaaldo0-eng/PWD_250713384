var formulirHapus = document.querySelectorAll('form.form-hapus'); // mengambil semua formulir hapus di halaman
for (var i = 0; i < formulirHapus.length; i++) { // mengulang setiap formulir hapus
    formulirHapus[i].addEventListener('submit', function (event) { // mendengarkan saat formulir dikirim
        var kolomNama = this.querySelector('input.nama-hapus'); // mencari kolom nama mobil pada formulir
        var namaMobil = kolomNama ? kolomNama.value : 'mobil ini'; // memakai nama mobil atau teks cadangan
        var pesan = 'Yakin ingin menghapus ' + namaMobil + '?'; // menyusun kalimat konfirmasi
        if (!window.confirm(pesan)) { // mengecek jawaban pengguna
            event.preventDefault(); // membatalkan pengiriman bila pengguna memilih batal
        } // menutup pengecekan jawaban
    }); // menutup pendengar submit
} // menutup perulangan formulir
