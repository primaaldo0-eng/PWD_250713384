function fn_ValForm() {
    var nama = document.getElementById("nama").value.replace(/^\s+|\s+$/g, "");
    var email = document.getElementById("email").value.replace(/^\s+|\s+$/g, "");
    var umur = document.getElementById("umur").value.replace(/^\s+|\s+$/g, "");
    var paket = document.getElementById("paket").value.replace(/^\s+|\s+$/g, "");

    var sMsg = "";
    var polaEmail = /^[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9]@[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9][\.][a-z0-9]{2,4}$/;
    var polaAngka = /^[0-9]+$/;

    if (nama == "") {
        sMsg += "\n* Nama tidak boleh kosong";
    }

    if (email == "") {
        sMsg += "\n* Email tidak boleh kosong";
    } else if (!polaEmail.test(email)) {
        sMsg += "\n* Format email salah (gunakan huruf kecil, contoh: budi@mail.com)";
    }

    if (umur == "") {
        sMsg += "\n* Umur tidak boleh kosong";
    } else if (!polaAngka.test(umur)) {
        sMsg += "\n* Umur harus berupa angka bulat";
    } else if (Number(umur) < 17 || Number(umur) > 65) {
        sMsg += "\n* Umur harus di antara 17 sampai 65 tahun";
    }

    if (paket == "") {
        sMsg += "\n* Kode paket tidak boleh kosong";
    } else if (paket.toUpperCase() != "S" && paket.toUpperCase() != "G" && paket.toUpperCase() != "P") {
        sMsg += "\n* Kode paket harus S, G, atau P";
    }

    if (sMsg != "") {
        alert("Peringatan:" + sMsg);
        return false;
    }

    var info;
    if (Number(umur) <= 25) {
        info = "Paket Pelajar";
    } else {
        info = "Paket Umum";
    }

    var pilihan;
    switch (paket.toUpperCase()) {
        case "S":
            pilihan = "Silver";
            break;
        case "G":
            pilihan = "Gold";
            break;
        case "P":
            pilihan = "Platinum";
            break;
        default:
            pilihan = "-";
            break;
    }

    alert("Terima kasih " + nama + " ! Anda terdaftar di paket " + pilihan + " (" + info + ").");
    return false;
}
