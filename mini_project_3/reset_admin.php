
<?php
require 'config.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE username = ?"
);
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    $stmt = $pdo->prepare(
        "UPDATE users SET password = ? WHERE username = ?"
    );
    $stmt->execute([$hash, $username]);
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO users (name, username, password, role)
         VALUES (?, ?, ?, 'admin')"
    );
    $stmt->execute(['Administrator', $username, $hash]);
}

echo "Akun admin berhasil diperbaiki.";
echo "<br>Username: admin";
echo "<br>Password: admin123";
?>