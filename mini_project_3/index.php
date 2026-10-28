<?php
require 'config.php'; require 'functions.php'; require_login();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalCategories = (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$totalStock = (int)$pdo->query('SELECT COALESCE(SUM(stock),0) FROM products')->fetchColumn();
$inCount = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE type='in' AND DATE(created_at)=CURDATE()")->fetchColumn();
$outCount = (int)$pdo->query("SELECT COUNT(*) FROM transactions WHERE type='out' AND DATE(created_at)=CURDATE()")->fetchColumn();
$recent = $pdo->query("SELECT t.id,t.type,t.created_at,t.note,u.name AS user_name, SUM(d.quantity) AS total_qty FROM transactions t JOIN users u ON u.id=t.user_id JOIN transaction_details d ON d.transaction_id=t.id GROUP BY t.id ORDER BY t.id DESC LIMIT 8")->fetchAll();
page_header('Dashboard');
?>
<div class="stats">
<div class="stat"><span>Total Produk</span><strong><?= $totalProducts ?></strong></div>
<div class="stat"><span>Total Kategori</span><strong><?= $totalCategories ?></strong></div>
<div class="stat"><span>Total Stok</span><strong><?= $totalStock ?></strong></div>
<div class="stat"><span>Transaksi Hari Ini</span><strong><?= $inCount + $outCount ?></strong><small>Masuk <?= $inCount ?> · Keluar <?= $outCount ?></small></div>
</div>
<div class="panel"><div class="panel-head"><h2>Transaksi Terbaru</h2><a class="button" href="transactions.php">Buka transaksi</a></div>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Jenis</th><th>Jumlah item</th><th>Petugas</th><th>Tanggal</th><th>Catatan</th></tr></thead><tbody>
<?php if (!$recent): ?><tr><td colspan="6">Belum ada transaksi.</td></tr><?php endif; ?>
<?php foreach ($recent as $r): ?><tr><td>#<?= (int)$r['id'] ?></td><td><span class="badge <?= $r['type']==='in'?'green':'orange' ?>"><?= $r['type']==='in'?'Barang masuk':'Barang keluar' ?></span></td><td><?= (int)$r['total_qty'] ?></td><td><?= e($r['user_name']) ?></td><td><?= e($r['created_at']) ?></td><td><?= e($r['note'] ?: '-') ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php page_footer(); ?>
