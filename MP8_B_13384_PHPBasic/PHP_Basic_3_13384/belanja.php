<?php
function dapatDiskon($totalBayar)
{
    if ($totalBayar >= 10000) {
        return 0.05;
    }
    return 0;
}

function totalHarga($diskon, $totalBayar)
{
    return $totalBayar - ($totalBayar * $diskon);
}

$errors = [];
$makanan = "";
$alatTulis = "";
$hargaMakanan = 0;
$hargaAlatTulis = 0;
$totalBayar = 0;
$diskon = 0;
$totalAkhir = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $makanan = $_POST["makanan"] ?? "";
    $alatTulis = $_POST["alat_tulis"] ?? "";

    if ($makanan === "") {
        $errors[] = "Makanan dan minuman tidak boleh kosong.";
    } elseif ($makanan !== "pop mie" && $makanan !== "air mineral") {
        $errors[] = "Pilihan makanan dan minuman tidak valid.";
    }

    if ($alatTulis === "") {
        $errors[] = "Alat tulis tidak boleh kosong.";
    } elseif ($alatTulis !== "pulpen" && $alatTulis !== "tipe-x") {
        $errors[] = "Pilihan alat tulis tidak valid.";
    }

    if (empty($errors)) {
        if ($makanan === "pop mie") {
            $hargaMakanan = 5000;
        } elseif ($makanan === "air mineral") {
            $hargaMakanan = 4000;
        }

        if ($alatTulis === "pulpen") {
            $hargaAlatTulis = 2000;
        } elseif ($alatTulis === "tipe-x") {
            $hargaAlatTulis = 8000;
        }

        $totalBayar = $hargaMakanan + $hargaAlatTulis;
        $diskon = dapatDiskon($totalBayar);
        $totalAkhir = totalHarga($diskon, $totalBayar);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PHP Basic 3 - Belanja</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px 16px;
        }
        .card {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 32px 32px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        h1 {
            font-size: 22px;
            margin: 0 0 6px;
            color: #1f3a5f;
        }
        h2 {
            font-size: 17px;
            margin: 0 0 10px;
            color: #1f3a5f;
        }
        .desc {
            color: #667085;
            font-size: 14px;
            margin: 0 0 6px;
        }
        .kategori {
            font-weight: bold;
            color: #333333;
            font-size: 14px;
            margin: 16px 0 4px;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
            color: #333333;
            font-size: 14px;
        }
        select {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #cfd6dd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: inherit;
        }
        button, .btn {
            display: inline-block;
            margin-top: 20px;
            background: #1f6feb;
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }
        button:hover, .btn:hover {
            background: #1858c4;
        }
        .info {
            background: #eef4ff;
            color: #1f3a5f;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 22px;
            font-size: 14px;
        }
        .error {
            background: #fdecea;
            border-left: 4px solid #d93025;
            color: #8a1c13;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 22px;
            font-size: 14px;
        }
        .error ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }
        .success {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 16px 18px;
            border-radius: 6px;
            margin-top: 22px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px;
        }
        th, td {
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #dbe7dd;
            font-size: 14px;
            vertical-align: top;
        }
        th {
            width: 42%;
            color: #555555;
        }
        .keterangan {
            margin: 0;
            color: #256029;
            font-size: 14px;
            line-height: 1.5;
        }
        .nav {
            margin-top: 22px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Belanja</h1>
    <p class="desc">Silahkan pilih produk yang ingin dibeli.</p>

    <form method="post" action="">
        <p class="kategori">Daftar Kategori:</p>

        <label for="makanan">Makanan dan Minuman</label>
        <select id="makanan" name="makanan">
            <option value="">-- Pilih Makanan dan Minuman --</option>
            <option value="pop mie"<?php echo $makanan === "pop mie" ? " selected" : ""; ?>>Pop Mie</option>
            <option value="air mineral"<?php echo $makanan === "air mineral" ? " selected" : ""; ?>>Air Mineral</option>
        </select>

        <label for="alat_tulis">Alat Tulis</label>
        <select id="alat_tulis" name="alat_tulis">
            <option value="">-- Pilih Alat Tulis --</option>
            <option value="pulpen"<?php echo $alatTulis === "pulpen" ? " selected" : ""; ?>>Pulpen</option>
            <option value="tipe-x"<?php echo $alatTulis === "tipe-x" ? " selected" : ""; ?>>Tipe-X</option>
        </select>

        <button type="submit" name="submit">Beli</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] !== "POST"): ?>
        <div class="info">Silahkan pilih produk yang ingin dibeli.</div>
    <?php elseif (!empty($errors)): ?>
        <div class="error">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="success">
            <h2>Informasi Belanja</h2>
            <table>
                <tr><th>Makanan dan Minuman</th><td><?php echo htmlspecialchars($makanan); ?></td></tr>
                <tr><th>Alat Tulis</th><td><?php echo htmlspecialchars($alatTulis); ?></td></tr>
                <tr><th>Total Harga</th><td>Rp <?php echo number_format($totalAkhir, 0, ",", "."); ?></td></tr>
                <tr><th>Diskon</th><td><?php echo htmlspecialchars($diskon); ?></td></tr>
            </table>
            <?php if ($diskon == 0): ?>
                <p class="keterangan">Maaf tidak ada diskon yang diberikan.</p>
            <?php else: ?>
                <p class="keterangan">Selamat! Anda mendapatkan diskon <?php echo htmlspecialchars($diskon * 100); ?>%.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="nav">
        <a class="btn" href="index.php">Kembali ke Menu</a>
    </div>
</div>
</body>
</html>
