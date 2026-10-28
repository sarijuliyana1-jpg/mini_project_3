<?php
require 'config.php';
require 'functions.php';
if (!empty($_SESSION['user'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare('SELECT id,name,username,password,role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            unset($user['password']);
            $_SESSION['user'] = $user;
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login StokKu</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page">
<div class="login-card"><h1>StokKu</h1><p class="muted">Sistem Manajemen Persediaan Barang</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><label>Username</label><input name="username" required autofocus><label>Password</label><input type="password" name="password" required><button class="button full" type="submit">Login</button></form>
<p class="muted small">Akun awal dibuat melalui setup.php.</p></div></body></html>
