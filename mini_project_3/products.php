<?php
require 'config.php'; require 'functions.php'; require_login();
$isAdmin = ($_SESSION['user']['role']??'')==='admin';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_admin();
    $action=$_POST['action']??'';
    if ($action==='save') {
        $id=(int)($_POST['id']??0); $category=(int)($_POST['category_id']??0); $code=trim($_POST['code']??''); $name=trim($_POST['name']??'');
        $price=$_POST['price']??''; $stock=$_POST['stock']??'';
        if(!$category || $code==='' || $name==='' || !is_numeric($price) || (float)$price<0 || filter_var($stock,FILTER_VALIDATE_INT)===false || (int)$stock<0) {
            flash('Validasi gagal: isi semua data dengan benar. Harga dan stok tidak boleh negatif; stok harus bilangan bulat.','error'); redirect('products.php');
        }
        try {
            if($id){ $s=$pdo->prepare('UPDATE products SET category_id=?,code=?,name=?,price=?,stock=? WHERE id=?'); $s->execute([$category,$code,$name,$price,$stock,$id]); }
            else { $s=$pdo->prepare('INSERT INTO products(category_id,code,name,price,stock) VALUES(?,?,?,?,?)'); $s->execute([$category,$code,$name,$price,$stock]); }
            flash('Produk berhasil disimpan.');
        } catch(PDOException $e) { flash('Kode produk sudah dipakai atau data tidak valid.','error'); }
        redirect('products.php');
    }
    if($action==='delete'){
        try{$s=$pdo->prepare('DELETE FROM products WHERE id=?');$s->execute([(int)$_POST['id']]);flash('Produk dihapus.');}
        catch(PDOException $e){flash('Produk tidak bisa dihapus karena sudah tercatat dalam transaksi.','error');}
        redirect('products.php');
    }
}
$edit=null;
if(isset($_GET['edit'])) { require_admin(); $s=$pdo->prepare('SELECT * FROM products WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch(); }
$cats=$pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$q=trim($_GET['q']??''); $catFilter=(int)($_GET['category_id']??0);
$sort=$_GET['sort']??'name';
$sortMap=['name'=>'p.name','price'=>'p.price','stock'=>'p.stock','newest'=>'p.id'];
$order=$sortMap[$sort]??'p.name';
$where=[];$params=[];
if($q!==''){$where[]='(p.name LIKE ? OR p.code LIKE ?)';$params[]="%$q%";$params[]="%$q%";}
if($catFilter){$where[]='p.category_id=?';$params[]=$catFilter;}
$sql='SELECT p.*,c.name AS category_name FROM products p JOIN categories c ON c.id=p.category_id';
if($where)$sql.=' WHERE '.implode(' AND ',$where);
$sql.=" ORDER BY $order ".($sort==='newest'?'DESC':'ASC');
$page=max(1,(int)($_GET['page']??1)); $perPage=5;
$countSql='SELECT COUNT(*) FROM products p'.($where?' WHERE '.implode(' AND ',$where):'');
$countStmt=$pdo->prepare($countSql);$countStmt->execute($params);$total=(int)$countStmt->fetchColumn();$pages=max(1,(int)ceil($total/$perPage));$page=min($page,$pages);
$sql.=' LIMIT '.$perPage.' OFFSET '.(($page-1)*$perPage);
$stmt=$pdo->prepare($sql);$stmt->execute($params);$rows=$stmt->fetchAll();
page_header('Produk');
?>
<?php if($isAdmin): ?><div class="panel"><h2><?= $edit?'Edit Produk':'Tambah Produk' ?></h2><?php if(!$cats): ?><p class="alert error">Buat kategori terlebih dahulu sebelum menambah produk.</p><?php else: ?><form method="post" class="form-grid"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div><label>Kode produk *</label><input name="code" required maxlength="40" value="<?= e($edit['code']??'') ?>"></div><div><label>Nama produk *</label><input name="name" required maxlength="120" value="<?= e($edit['name']??'') ?>"></div>
<div><label>Kategori *</label><select name="category_id" required><option value="">Pilih kategori</option><?php foreach($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)($edit['category_id']??0)===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
<div><label>Harga (Rp) *</label><input type="number" min="0" step="0.01" name="price" required value="<?= e($edit['price']??'0') ?>"></div><div><label>Stok awal *</label><input type="number" min="0" step="1" name="stock" required value="<?= e($edit['stock']??'0') ?>"></div><div class="form-actions"><button class="button">Simpan</button><?php if($edit): ?><a class="button secondary" href="products.php">Batal</a><?php endif; ?></div></form><?php endif; ?></div>
<?php else: ?><p class="muted">Staff dapat melihat produk dan mencatat transaksi stok, tetapi tidak dapat mengubah master produk.</p><?php endif; ?>
<div class="panel"><h2>Daftar Produk</h2><form method="get" class="filters"><input name="q" placeholder="Cari nama/kode..." value="<?= e($q) ?>"><select name="category_id"><option value="0">Semua kategori</option><?php foreach($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $catFilter===(int)$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><select name="sort"><option value="name" <?= $sort==='name'?'selected':'' ?>>Nama A-Z</option><option value="price" <?= $sort==='price'?'selected':'' ?>>Harga termurah</option><option value="stock" <?= $sort==='stock'?'selected':'' ?>>Stok terendah</option><option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option></select><button class="button">Terapkan</button><a class="button secondary" href="products.php">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Kode</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><?php if($isAdmin): ?><th>Aksi</th><?php endif; ?></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['code']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['category_name']) ?></td><td>Rp <?= number_format((float)$r['price'],0,',','.') ?></td><td><?= (int)$r['stock'] ?></td><?php if($isAdmin): ?><td class="actions"><a href="?edit=<?= (int)$r['id'] ?>">Edit</a><form method="post" class="inline" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="link danger">Hapus</button></form></td><?php endif; ?></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6">Produk tidak ditemukan.</td></tr><?php endif; ?></tbody></table></div>
<div class="pagination"><span>Halaman <?= $page ?> dari <?= $pages ?> · <?= $total ?> produk</span><div><?php if($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">‹ Sebelumnya</a><?php endif; ?> <?php if($page<$pages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Berikutnya ›</a><?php endif; ?></div></div></div>
<?php page_footer(); ?>
