<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PHP Basic 3 - Menu</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 40px 16px;
        }
        .card {
            max-width: 520px;
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
            margin: 0 0 18px;
        }
        .menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu li {
            margin-bottom: 12px;
        }
        .menu a {
            display: block;
            padding: 14px 16px;
            border: 1px solid #d9e1ea;
            border-radius: 8px;
            text-decoration: none;
            color: #1f3a5f;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .menu a:hover {
            background: #eef4ff;
            border-color: #1f6feb;
        }
        .menu strong {
            display: block;
            font-size: 15px;
            margin-bottom: 3px;
        }
        .menu span {
            color: #667085;
            font-size: 13px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Menu Utama</h1>
    <p class="desc">Silahkan pilih menu di bawah ini.</p>
    <ul class="menu">
        <li>
            <a href="pendaftaran.php">
                <strong>Pendaftaran Staff</strong>
                <span>Daftar sebagai kasir atau merchandiser.</span>
            </a>
        </li>
        <li>
            <a href="belanja.php">
                <strong>Belanja</strong>
                <span>Pilih makanan, minuman, dan alat tulis.</span>
            </a>
        </li>
    </ul>
</div>
</body>
</html>
