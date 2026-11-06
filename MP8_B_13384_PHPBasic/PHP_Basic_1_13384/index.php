<?php
$isSubmitted = false;
$nama = "";
$npm = "";
$prodi = "";
$alamat = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $isSubmitted = true;
    $nama = $_POST["nama"] ?? "";
    $npm = $_POST["npm"] ?? "";
    $prodi = $_POST["prodi"] ?? "";
    $alamat = $_POST["alamat"] ?? "";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PHP Basic 1 - Informasi</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px 16px;
        }
        .card {
            max-width: 540px;
            margin: 0 auto;
            background: #ffffff;
            padding: 28px 32px 32px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        h1 {
            font-size: 22px;
            margin: 0 0 8px;
            color: #1f3a5f;
        }
        p.desc {
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
        input, select, textarea {
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
        }
        button:hover, .btn:hover {
            background: #1858c4;
        }
        .success {
            background: #e8f5e9;
            color: #256029;
            padding: 10px 14px;
            border-radius: 6px;
            margin: 12px 0 4px;
            font-size: 14px;
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
            vertical-align: top;
        }
        th {
            width: 38%;
            color: #555555;
        }
    </style>
</head>
<body>
<div class="card">
<?php if (!$isSubmitted): ?>
    <h1>Form Input Informasi</h1>
    <p class="desc">Silahkan lengkapi data diri Anda di bawah ini.</p>
    <form method="post" action="">
        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>

        <label for="npm">NPM</label>
        <input type="text" id="npm" name="npm" placeholder="Masukkan NPM" required>

        <label for="prodi">Program Studi</label>
        <select id="prodi" name="prodi" required>
            <option value="">-- Pilih Program Studi --</option>
            <option value="Informatika">Informatika</option>
            <option value="Sistem Informasi">Sistem Informasi</option>
            <option value="Teknik Elektro">Teknik Elektro</option>
        </select>

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat" required></textarea>

        <button type="submit">Submit</button>
    </form>
<?php else: ?>
    <h1>Informasi Data Diri</h1>
    <div class="success">Data berhasil disimpan dan ditampilkan.</div>
    <table>
        <tr><th>Nama Lengkap</th><td><?php echo htmlspecialchars($nama); ?></td></tr>
        <tr><th>NPM</th><td><?php echo htmlspecialchars($npm); ?></td></tr>
        <tr><th>Program Studi</th><td><?php echo htmlspecialchars($prodi); ?></td></tr>
        <tr><th>Alamat</th><td><?php echo nl2br(htmlspecialchars($alamat)); ?></td></tr>
    </table>
    <a class="btn" href="">Kembali / Input Lagi</a>
<?php endif; ?>
</div>
</body>
</html>
