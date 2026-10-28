<?php
require 'config.php'; require 'functions.php'; require_login();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $type=$_POST['type']??'';$productId=(int)($_POST['product_id']??0);$qty=$_POST['quantity']??'';$note=trim($_POST['note']??'');
    if(!in_array($type,['in','out'],true)||$productId<1||filter_var($qty,FILTER_VALIDATE_INT)===false||(int)$qty<1){
        flash('Validasi gagal: pilih produk dan masukkan jumlah berupa bilangan bulat lebih dari 0.','error');redirect('transactions.php');
    }
    try{
        $pdo->beginTransaction();
        $s=$pdo->prepare('SELECT id,stock,price FROM products WHERE id=? FOR UPDATE');$s->execute([$productId]);$product=$s->fetch();
        if(!$product)throw new RuntimeException('Produk tidak ditemukan.');
        if($type==='out' && (int)$product['stock']<(int)$qty)throw new RuntimeException('Stok tidak mencukupi. Transaksi dibatalkan.');
        $newStock=$type==='in'?(int)$product['stock']+(int)$qty:(int)$product['stock']-(int)$qty;
        $s=$pdo->prepare('UPDATE products SET stock=? WHERE id=?');$s->execute([$newStock,$productId]);
        $s=$pdo->prepare('INSERT INTO transactions(user_id,type,note) VALUES(?,?,?)');$s->execute([$_SESSION['user']['id'],$type,$note]);
        $transactionId=(int)$pdo->lastInsertId();
        $s=$pdo->prepare('INSERT INTO transaction_details(transaction_id,product_id,quantity,price_at_time) VALUES(?,?,?,?)');$s->execute([$transactionId,$productId,(int)$qty,$product['price']]);
        $pdo->commit();flash('Transaksi berhasil disimpan dan stok diperbarui.');
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash($e instanceof RuntimeException?$e->getMessage():'Transaksi gagal. Periksa data dan koneksi database.','error');}
    redirect('transactions.php');
}
$products=$pdo->query('SELECT id,code,name,stock,price FROM products ORDER BY name')->fetchAll();
$rows=$pdo->query("SELECT t.id,t.type,t.note,t.created_at,u.name AS user_name,p.code,p.name AS product_name,d.quantity,d.price_at_time FROM transactions t JOIN users u ON u.id=t.user_id JOIN transaction_details d ON d.transaction_id=t.id JOIN products p ON p.id=d.product_id ORDER BY t.id DESC LIMIT 100")->fetchAll();
page_header('Transaksi Stok');
?>
<div class="panel"><h2>Catat Barang Masuk/Keluar</h2><?php if(!$products): ?><p class="alert error">Tambahkan produk terlebih dahulu.</p><?php else: ?><form method="post" class="form-grid"><div><label>Jenis transaksi *</label><select name="type" required><option value="in">Barang masuk (+ stok)</option><option value="out">Barang keluar (- stok)</option></select></div><div><label>Produk *</label><select name="product_id" required><option value="">Pilih produk</option><?php foreach($products as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['code'].' - '.$p['name'].' (stok: '.$p['stock'].')') ?></option><?php endforeach; ?></select></div><div><label>Jumlah *</label><input type="number" name="quantity" min="1" step="1" required></div><div><label>Catatan</label><input name="note" maxlength="255" placeholder="Contoh: restock dari pemasok"></div><div class="form-actions"><button class="button">Simpan transaksi</button></div></form><?php endif; ?></div>
<div class="panel"><h2>Riwayat Transaksi (100 terakhir)</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Tanggal</th><th>Jenis</th><th>Produk</th><th>Jumlah</th><th>Harga saat transaksi</th><th>Petugas</th><th>Catatan</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td>#<?= (int)$r['id'] ?></td><td><?= e($r['created_at']) ?></td><td><span class="badge <?= $r['type']==='in'?'green':'orange' ?>"><?= $r['type']==='in'?'Masuk':'Keluar' ?></span></td><td><?= e($r['code'].' - '.$r['product_name']) ?></td><td><?= (int)$r['quantity'] ?></td><td>Rp <?= number_format((float)$r['price_at_time'],0,',','.') ?></td><td><?= e($r['user_name']) ?></td><td><?= e($r['note']?:'-') ?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="8">Belum ada transaksi.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_footer(); ?>
