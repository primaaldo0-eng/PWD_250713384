<?php
function tambah($a, $b)
{
    return $a + $b;
}

function kurang($a, $b)
{
    return $a - $b;
}

function kali($a, $b)
{
    return $a * $b;
}

function bagi($a, $b)
{
    if ($b == 0) {
        return null;
    }
    return $a / $b;
}

function formatAngka($nilai)
{
    if ($nilai == (int) $nilai) {
        return (string) (int) $nilai;
    }
    return rtrim(rtrim(number_format($nilai, 4, ".", ""), "0"), ".");
}

$errors = [];
$angka1 = "";
$angka2 = "";
$operator = "";
$simbol = "";
$namaOperator = "";
$hasil = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $angka1 = trim($_POST["angka1"] ?? "");
    $angka2 = trim($_POST["angka2"] ?? "");
    $operator = $_POST["operator"] ?? "";

    if ($angka1 === "") {
        $errors[] = "Angka pertama tidak boleh kosong.";
    } elseif (!is_numeric($angka1)) {
        $errors[] = "Angka pertama harus berupa angka.";
    }

    if ($angka2 === "") {
        $errors[] = "Angka kedua tidak boleh kosong.";
    } elseif (!is_numeric($angka2)) {
        $errors[] = "Angka kedua harus berupa angka.";
    }

    if ($operator === "") {
        $errors[] = "Operasi perhitungan harus dipilih.";
    }

    if (empty($errors)) {
        $a = (float) $angka1;
        $b = (float) $angka2;

        if ($operator === "tambah") {
            $hasil = tambah($a, $b);
            $simbol = "+";
            $namaOperator = "Penjumlahan";
        } elseif ($operator === "kurang") {
            $hasil = kurang($a, $b);
            $simbol = "-";
            $namaOperator = "Pengurangan";
        } elseif ($operator === "kali") {
            $hasil = kali($a, $b);
            $simbol = "x";
            $namaOperator = "Perkalian";
        } elseif ($operator === "bagi") {
            if ($b == 0) {
                $errors[] = "Pembagian dengan nol tidak diperbolehkan.";
            } else {
                $hasil = bagi($a, $b);
                $simbol = ":";
                $namaOperator = "Pembagian";
            }
        } else {
            $errors[] = "Operasi perhitungan tidak valid.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PHP Basic 2 - Hasil Perhitungan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px 16px;
        }
        .card {
            max-width: 480px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 32px 32px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        h1 {
            font-size: 22px;
            margin: 0 0 14px;
            color: #1f3a5f;
        }
        h2 {
            font-size: 17px;
            margin: 0 0 10px;
            color: #256029;
        }
        .error {
            background: #fdecea;
            border-left: 4px solid #d93025;
            color: #8a1c13;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
        }
        .error ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }
        .success {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 16px;
            border-radius: 6px;
        }
        .success p {
            margin: 6px 0;
            color: #333333;
            font-size: 14px;
        }
        .hasil {
            font-size: 26px;
            font-weight: bold;
            color: #1f3a5f;
            margin: 10px 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
        }
        th, td {
            text-align: left;
            padding: 9px 10px;
            border-bottom: 1px solid #e6eaee;
            font-size: 14px;
        }
        th {
            width: 38%;
            color: #555555;
        }
        .btn {
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
        }
        .btn:hover {
            background: #1858c4;
        }
    </style>
</head>
<body>
<div class="card">
<?php if ($_SERVER["REQUEST_METHOD"] !== "POST"): ?>
    <h1>Hasil Perhitungan</h1>
    <p>Silahkan isi form kalkulator terlebih dahulu.</p>
    <a class="btn" href="index.xhtml">Kembali ke Form</a>
<?php elseif (!empty($errors)): ?>
    <h1>Terjadi Kesalahan</h1>
    <div class="error">
        <strong>Periksa kembali input Anda:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <a class="btn" href="index.xhtml">Kembali ke Form</a>
<?php else: ?>
    <h1>Hasil Perhitungan</h1>
    <div class="success">
        <h2>Perhitungan Berhasil</h2>
        <table>
            <tr><th>Angka Pertama</th><td><?php echo htmlspecialchars(formatAngka((float) $angka1)); ?></td></tr>
            <tr><th>Angka Kedua</th><td><?php echo htmlspecialchars(formatAngka((float) $angka2)); ?></td></tr>
            <tr><th>Operasi</th><td><?php echo htmlspecialchars($namaOperator); ?> (<?php echo htmlspecialchars($simbol); ?>)</td></tr>
        </table>
        <p class="hasil"><?php echo htmlspecialchars(formatAngka((float) $angka1)); ?> <?php echo htmlspecialchars($simbol); ?> <?php echo htmlspecialchars(formatAngka((float) $angka2)); ?> = <?php echo htmlspecialchars(formatAngka($hasil)); ?></p>
    </div>
    <a class="btn" href="index.xhtml">Hitung Lagi</a>
<?php endif; ?>
</div>
</body>
</html>
