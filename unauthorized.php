<!DOCTYPE html>
<html lang="id">

<head>
 <meta charset="UTF-8">
 <title>Tidak Diizinkan</title>
 <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans&display=swap" rel="stylesheet">
 <style>
 body {
 font-family: 'DM Sans', sans-serif;
 background: #0a0c10;
 color: #e8eaf0;
 display: flex;
 align-items: center;
 justify-content: center;
 min-height: 100vh;
 text-align: center;
 }

 h1 {
 font-family: 'Syne', sans-serif;
 font-size: 28px;
 margin-bottom: 10px;
 }

 p {
 color: #6b7280;
 margin-bottom: 20px;
 }

 a {
 color: #3b82f6;
 text-decoration: none;
 }
 .minimal-denied-icon {
 width: 54px;
 height: 54px;
 border: 2px solid currentColor;
 border-radius: 16px;
 margin: 0 auto 16px;
 position: relative;
 opacity: .9;
 }
 .minimal-denied-icon::before,
 .minimal-denied-icon::after {
 content: '';
 position: absolute;
 left: 15px;
 right: 15px;
 top: 25px;
 height: 2px;
 background: currentColor;
 border-radius: 2px;
 }
 .minimal-denied-icon::before { transform: rotate(45deg); }
 .minimal-denied-icon::after { transform: rotate(-45deg); }
 </style>
</head>

<body>
 <div>
 <div class="minimal-denied-icon"></div>
 <h1>Akses Ditolak</h1>
 <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
 <a href="login.php">← Kembali ke Login</a>
 </div>
</body>

</html>
