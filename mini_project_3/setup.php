<?php
require 'config.php';
require 'functions.php';

$count = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$message = '';
if ($count === 0) {
    $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['Administrator', 'admin', password_hash('admin123', PASSWORD_DEFAULT)]);
    $message = 'Akun admin awal berhasil dibuat.';
} else {
    $message = 'Tabel pengguna sudah berisi data. Akun awal tidak dibuat ulang.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup StokKu</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><div class="login-card"><h1>Setup StokKu</h1><p><?= e($message) ?></p><p>Jika akun awal baru dibuat:</p><p><b>Username:</b> admin<br><b>Password:</b> admin123</p><p><b>Penting:</b> hapus file <code>setup.php</code> setelah setup selesai.</p><a class="button" href="login.php">Buka halaman login</a></div></body></html>
