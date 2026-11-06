<?php
$errors = [];
$nama = "";
$npm = "";
$loker = "";
$jadwalWawancara = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"] ?? "");
    $npm = trim($_POST["npm"] ?? "");
    $loker = $_POST["staff"] ?? "";

    if ($nama === "") {
        $errors[] = "Nama tidak boleh kosong.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $nama)) {
        $errors[] = "Nama hanya boleh mengandung huruf dan spasi.";
    }

    if ($npm === "") {
        $errors[] = "NPM tidak boleh kosong.";
    } elseif (!preg_match("/^[0-9]+$/", $npm)) {
        $errors[] = "NPM hanya boleh mengandung angka.";
    }

    if ($loker === "") {
        $errors[] = "Pilihan staff tidak boleh kosong.";
    } elseif ($loker !== "kasir" && $loker !== "merchandiser") {
        $errors[] = "Pilihan staff tidak valid.";
    }

    if (empty($errors)) {
        if ($loker === "kasir") {
            $jadwalWawancara = "besok pukul 10.00 WIB di market";
        } else {
            $jadwalWawancara = "lusa pukul 12.00 WIB di market";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PHP Basic 3 - Pendaftaran Staff</title>
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
            margin: 0 0 12px;
        }
        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: bold;
            color: #333333;
            font-size: 14px;
        }
        input, select {
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
            width: 38%;
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
    <h1>Pendaftaran Staff</h1>
    <p class="desc">Silahkan isi formulir pendaftaran di bawah ini.</p>

    <form method="post" action="">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" placeholder="Masukkan nama">

        <label for="npm">NPM</label>
        <input type="text" id="npm" name="npm" value="<?php echo htmlspecialchars($npm); ?>" placeholder="Masukkan NPM">

        <label for="staff">Pilihan Staff</label>
        <select id="staff" name="staff">
            <option value="">-- Pilih Staff --</option>
            <option value="kasir"<?php echo $loker === "kasir" ? " selected" : ""; ?>>Kasir</option>
            <option value="merchandiser"<?php echo $loker === "merchandiser" ? " selected" : ""; ?>>Merchandiser</option>
        </select>

        <button type="submit" name="submit">Daftar</button>
    </form>

    <?php if ($_SERVER["REQUEST_METHOD"] !== "POST"): ?>
        <div class="info">Silahkan lengkapi formulir di atas.</div>
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
            <h2>Informasi Pendaftaran</h2>
            <table>
                <tr><th>Nama</th><td><?php echo htmlspecialchars($nama); ?></td></tr>
                <tr><th>NPM</th><td><?php echo htmlspecialchars($npm); ?></td></tr>
                <tr><th>Pilihan Staff</th><td><?php echo htmlspecialchars($loker); ?></td></tr>
                <tr><th>Jadwal Wawancara</th><td><?php echo htmlspecialchars($jadwalWawancara); ?></td></tr>
            </table>
            <p class="keterangan">Anda mendaftar sebagai <?php echo htmlspecialchars($loker); ?>. Selanjutnya, Anda dapat hadir <?php echo htmlspecialchars($jadwalWawancara); ?> untuk wawancara.</p>
        </div>
    <?php endif; ?>

    <div class="nav">
        <a class="btn" href="index.php">Kembali ke Menu</a>
    </div>
</div>
</body>
</html>
