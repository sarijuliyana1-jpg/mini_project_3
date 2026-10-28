<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function require_login(): void {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}
function require_admin(): void {
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('<h2>403 - Akses ditolak</h2><p>Halaman ini hanya dapat diakses admin.</p><p><a href="index.php">Kembali ke dashboard</a></p>');
    }
}
function redirect(string $url): void {
    header("Location: $url");
    exit;
}
function flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert '.e($f['type']).'">'.e($f['message']).'</div>';
        unset($_SESSION['flash']);
    }
}
function page_header(string $title): void {
    require_login();
    $user = $_SESSION['user'];
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>'.e($title).' - StokKu</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="topbar"><a class="brand" href="index.php">StokKu</a><div>'.e($user['name']).' <span class="role">'.e(strtoupper($user['role'])).'</span> <a class="logout" href="logout.php">Keluar</a></div></header>';
    echo '<div class="layout"><aside class="sidebar"><a href="index.php">Dashboard</a><a href="products.php">Produk</a><a href="categories.php">Kategori</a><a href="transactions.php">Transaksi Stok</a>';
    if ($user['role'] === 'admin') echo '<a href="users.php">Pengguna</a>';
    echo '</aside><main class="content"><h1>'.e($title).'</h1>';
    show_flash();
}
function page_footer(): void {
    echo '</main></div></body></html>';
}
