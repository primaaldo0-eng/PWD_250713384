var wadahPeta = document.getElementById('peta-detail'); // mengambil wadah peta detail rental
var kolomLat = document.getElementById('lat-detail'); // mengambil kolom latitude detail
var kolomLng = document.getElementById('lng-detail'); // mengambil kolom longitude detail
var infoPeta = document.getElementById('info-peta'); // mengambil keterangan peta
var lat = parseFloat(kolomLat ? kolomLat.value : ''); // membaca latitude dari halaman
var lng = parseFloat(kolomLng ? kolomLng.value : ''); // membaca longitude dari halaman
if (typeof L === 'undefined') { // memastikan Leaflet termuat
    if (infoPeta) { infoPeta.textContent = 'Peta tidak dapat dimuat.'; } // menulis pesan bila Leaflet gagal
} else if (isNaN(lat) || isNaN(lng)) { // mengecek ketersediaan koordinat
    if (infoPeta) { infoPeta.textContent = 'Koordinat lokasi jemput tidak tersedia.'; } // menulis pesan tanpa koordinat
} else { // membuat peta bila koordinat tersedia
    var peta = L.map(wadahPeta).setView([lat, lng], 15); // membuat peta dengan pusat lokasi jemput
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { // menambahkan lapisan peta OpenStreetMap
        attribution: '&copy; OpenStreetMap contributors', // mencantumkan atribusi peta
        maxZoom: 19 // membatasi zoom maksimum
    }).addTo(peta); // menampilkan lapisan peta
    L.marker([lat, lng]).addTo(peta).bindPopup('Lokasi jemput terjadwal').openPopup(); // menampilkan penanda lokasi jemput
    if (infoPeta) { infoPeta.textContent = 'Koordinat: ' + lat.toFixed(7) + ', ' + lng.toFixed(7); } // menampilkan koordinat
} // menutup pengecekan peta
