var formulirKonfirmasi = document.querySelectorAll('form.form-konfirmasi'); // mengambil semua formulir yang butuh konfirmasi
for (var i = 0; i < formulirKonfirmasi.length; i++) { // mengulang setiap formulir konfirmasi
    formulirKonfirmasi[i].addEventListener('submit', function (event) { // mendengarkan pengiriman formulir
        var kolomPesan = this.querySelector('input.pesan-konfirmasi'); // mencari pesan khusus pada formulir
        var pesan = kolomPesan ? kolomPesan.value : 'Yakin ingin melanjutkan?'; // memakai pesan khusus atau pesan bawaan
        if (!window.confirm(pesan)) { // mengecek jawaban pengguna
            event.preventDefault(); // membatalkan pengiriman bila dibatalkan
        } // menutup pengecekan jawaban
    }); // menutup pendengar submit
} // menutup perulangan formulir
