<?php
require 'config.php'; require 'functions.php'; require_login();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_admin();
    $action = $_POST['action'] ?? '';
    if ($action==='save') {
        $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $desc=trim($_POST['description']??'');
        if ($name==='') { flash('Nama kategori wajib diisi.','error'); redirect('categories.php'); }
        try {
            if ($id) { $s=$pdo->prepare('UPDATE categories SET name=?,description=? WHERE id=?'); $s->execute([$name,$desc,$id]); }
            else { $s=$pdo->prepare('INSERT INTO categories(name,description) VALUES(?,?)'); $s->execute([$name,$desc]); }
            flash('Kategori berhasil disimpan.');
        } catch(PDOException $e) { flash('Nama kategori sudah digunakan atau data tidak valid.','error'); }
        redirect('categories.php');
    }
    if ($action==='delete') {
        try { $s=$pdo->prepare('DELETE FROM categories WHERE id=?'); $s->execute([(int)$_POST['id']]); flash('Kategori dihapus.'); }
        catch(PDOException $e) { flash('Kategori tidak bisa dihapus karena masih dipakai produk.','error'); }
        redirect('categories.php');
    }
}
$edit=null;
if (isset($_GET['edit'])) { require_admin(); $s=$pdo->prepare('SELECT * FROM categories WHERE id=?'); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }
$rows=$pdo->query('SELECT c.*,COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
page_header('Kategori');
?>
<?php if (($_SESSION['user']['role']??'')==='admin'): ?>
<div class="panel"><h2><?= $edit?'Edit Kategori':'Tambah Kategori' ?></h2><form method="post" class="form-grid"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><div><label>Nama kategori *</label><input name="name" required maxlength="100" value="<?= e($edit['name']??'') ?>"></div><div><label>Deskripsi</label><input name="description" maxlength="255" value="<?= e($edit['description']??'') ?>"></div><div class="form-actions"><button class="button" type="submit">Simpan</button><?php if($edit): ?><a class="button secondary" href="categories.php">Batal</a><?php endif; ?></div></form></div>
<?php else: ?><p class="muted">Staff dapat melihat kategori, tetapi hanya admin yang dapat mengubahnya.</p><?php endif; ?>
<div class="panel"><h2>Daftar Kategori</h2><div class="table-wrap"><table><thead><tr><th>Nama</th><th>Deskripsi</th><th>Jumlah produk</th><?php if(($_SESSION['user']['role']??'')==='admin'): ?><th>Aksi</th><?php endif; ?></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['description']?:'-') ?></td><td><?= (int)$r['product_count'] ?></td><?php if(($_SESSION['user']['role']??'')==='admin'): ?><td class="actions"><a href="?edit=<?= (int)$r['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Hapus kategori ini?')" class="inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="link danger" type="submit">Hapus</button></form></td><?php endif; ?></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="4">Belum ada kategori.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_footer(); ?>
