var kolomMulai = document.getElementById('tanggal_mulai'); // mengambil kolom tanggal mulai
var kolomSelesai = document.getElementById('tanggal_selesai'); // mengambil kolom tanggal selesai
var kolomHarga = document.getElementById('harga_satuan'); // mengambil kolom harga satuan
var ringkasanHarga = document.getElementById('ringkasan-harga'); // mengambil tempat ringkasan harga
var pembentukRupiah = new Intl.NumberFormat('id-ID'); // menyiapkan format angka Indonesia
function perbaruiHarga() { // membuat fungsi menghitung total sewa secara langsung
    if (!kolomMulai || !kolomSelesai || !ringkasanHarga || !kolomHarga) { return; } // berhenti bila elemen tidak ada
    var mulai = new Date(kolomMulai.value + 'T00:00:00'); // membuat objek tanggal mulai
    var selesai = new Date(kolomSelesai.value + 'T00:00:00'); // membuat objek tanggal selesai
    var harga = parseFloat(kolomHarga.value) || 0; // mengambil harga per hari
    if (isNaN(mulai.getTime()) || isNaN(selesai.getTime())) { ringkasanHarga.textContent = 'Tanggal belum valid'; return; } // menampilkan pesan bila tanggal salah
    var jumlahHari = Math.round((selesai.getTime() - mulai.getTime()) / 86400000); // menghitung selisih hari
    if (jumlahHari < 1) { jumlahHari = 0; } // menolak rentang tanggal yang tidak valid
    var total = jumlahHari * harga; // menghitung total biaya
    ringkasanHarga.textContent = jumlahHari + ' hari - Rp ' + pembentukRupiah.format(total); // menampilkan ringkasan harga
} // menutup fungsi perbaruiHarga
if (kolomMulai && kolomSelesai) { // memastikan tanggal tersedia sebelum memasang pendengar
    kolomMulai.addEventListener('change', perbaruiHarga); // menghitung ulang saat tanggal mulai berubah
    kolomSelesai.addEventListener('change', perbaruiHarga); // menghitung ulang saat tanggal selesai berubah
    perbaruiHarga(); // menghitung harga saat halaman pertama dibuka
} // menutup pengecekan tanggal
if (typeof L === 'undefined') { // memastikan pustaka Leaflet sudah termuat
    alert('Peta tidak dapat dimuat. Periksa koneksi internet Anda.'); // memberi tahu pengguna bila Leaflet gagal
} else { // memulai inisialisasi peta
    var wadahPeta = document.getElementById('peta'); // mengambil wadah peta
    var kolomLat = document.getElementById('latitude'); // mengambil kolom latitude
    var kolomLng = document.getElementById('longitude'); // mengambil kolom longitude
    var infoKoordinat = document.getElementById('info-koordinat'); // mengambil keterangan koordinat
    var tombolLokasi = document.getElementById('tombol-lokasi'); // mengambil tombol geolocation
    var latAwal = parseFloat(kolomLat ? kolomLat.value : ''); // membaca latitude awal
    var lngAwal = parseFloat(kolomLng ? kolomLng.value : ''); // membaca longitude awal
    var pusat = isNaN(latAwal) || isNaN(lngAwal) ? [-6.2088, 106.8456] : [latAwal, lngAwal]; // memakai Jakarta sebagai pusat bawaan
    var peta = L.map(wadahPeta).setView(pusat, 13); // membuat peta dengan pusat tertentu
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { // menambahkan lapisan peta OpenStreetMap
        attribution: '&copy; OpenStreetMap contributors', // mencantumkan atribusi peta
        maxZoom: 19 // membatasi zoom maksimum
    }).addTo(peta); // menampilkan lapisan peta
    var penanda = L.marker(pusat, { draggable: true }).addTo(peta); // membuat penanda yang bisa digeser
    function tulisKoordinat(lat, lng) { // membuat fungsi menulis koordinat ke formulir
        if (kolomLat) { kolomLat.value = lat.toFixed(7); } // menyimpan latitude
        if (kolomLng) { kolomLng.value = lng.toFixed(7); } // menyimpan longitude
        if (infoKoordinat) { infoKoordinat.textContent = 'Koordinat: ' + lat.toFixed(7) + ', ' + lng.toFixed(7); } // menampilkan koordinat
    } // menutup fungsi tulisKoordinat
    tulisKoordinat(pusat[0], pusat[1]); // mengisi koordinat awal
    penanda.on('dragend', function () { // mendengarkan penanda yang digeser
        var posisi = penanda.getLatLng(); // mengambil posisi terbaru
        tulisKoordinat(posisi.lat, posisi.lng); // memperbarui koordinat
    }); // menutup pendengar geser penanda
    peta.on('click', function (acara) { // mendengarkan klik pada peta
        penanda.setLatLng(acara.latlng); // memindahkan penanda ke titik klik
        tulisKoordinat(acara.latlng.lat, acara.latlng.lng); // memperbarui koordinat
    }); // menutup pendengar klik peta
    if (tombolLokasi) { // memastikan tombol geolocation ada
        tombolLokasi.addEventListener('click', function (acara) { // mendengarkan klik tombol lokasi
            acara.preventDefault(); // mencegah formulir terkirim
            if (!navigator.geolocation) { alert('Browser Anda tidak mendukung geolocation.'); return; } // menangani browser tanpa geolocation
            navigator.geolocation.getCurrentPosition(function (posisi) { // meminta lokasi pengguna
                var titik = [posisi.coords.latitude, posisi.coords.longitude]; // mengambil koordinat pengguna
                penanda.setLatLng(titik); // memindahkan penanda
                peta.setView(titik, 15); // menggeser tampilan peta
                tulisKoordinat(titik[0], titik[1]); // menyimpan koordinat ke formulir
            }, function () { alert('Lokasi tidak dapat diambil. Izinkan akses lokasi pada browser.'); }, { enableHighAccuracy: true, timeout: 10000 }); // menangani gagal geolocation
        }); // menutup pendengar tombol lokasi
    } // menutup pengecekan tombol lokasi
} // menutup inisialisasi peta
