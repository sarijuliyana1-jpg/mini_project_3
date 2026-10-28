<?php
require 'config.php'; require 'functions.php'; require_admin();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name=trim($_POST['name']??'');$username=trim($_POST['username']??'');$password=$_POST['password']??'';$role=$_POST['role']??'staff';
    if($name===''||$username===''||strlen($password)<6||!in_array($role,['admin','staff'],true)){flash('Nama, username, role wajib diisi dan password minimal 6 karakter.','error');redirect('users.php');}
    try{$s=$pdo->prepare('INSERT INTO users(name,username,password,role) VALUES(?,?,?,?)');$s->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT),$role]);flash('Pengguna berhasil dibuat.');}
    catch(PDOException $e){flash('Username sudah digunakan. Pilih username lain.','error');}
    redirect('users.php');
}
$users=$pdo->query('SELECT id,name,username,role,created_at FROM users ORDER BY id DESC')->fetchAll();
page_header('Pengguna');
?>
<div class="panel"><h2>Tambah Pengguna</h2><form method="post" class="form-grid"><div><label>Nama *</label><input name="name" required></div><div><label>Username *</label><input name="username" required></div><div><label>Password (minimal 6 karakter) *</label><input type="password" name="password" minlength="6" required></div><div><label>Role *</label><select name="role"><option value="staff">Staff</option><option value="admin">Admin</option></select></div><div class="form-actions"><button class="button">Buat pengguna</button></div></form></div>
<div class="panel"><h2>Daftar Pengguna</h2><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th></tr></thead><tbody><?php foreach($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['username']) ?></td><td><?= e(strtoupper($u['role'])) ?></td><td><?= e($u['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
<?php page_footer(); ?>
