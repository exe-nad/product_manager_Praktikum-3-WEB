<?php
// index.php - SweetBakes Studio: Etalase Menu Pastry & Cake (READ, Search & Filter)
require_once __DIR__ . '/config/db.php';

$page_title = 'Etalase Menu';

// Parameter GET Pencarian & Filter Aman (Slide 18)
$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(name LIKE :q OR category LIKE :q OR description LIKE :q)";
    $params['q'] = "%{$q}%";
}

if ($category !== '') {
    $where[] = "category = :cat";
    $params['cat'] = $category;
}

$whereSql = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Metrik Dashboard (Seperti Repo Referensi)
$statsTotalItems = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$statsTotalValuation = $pdo->query("SELECT SUM(price * stock) FROM products")->fetchColumn() ?: 0;
$statsLowStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();

// Query List Produk dengan Prepared Statements
$sql = "SELECT * FROM products" . $whereSql . " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Daftar Kategori Dinamis
$catsStmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
$allCategories = $catsStmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/header.php';
?>

<!-- Metrik Dashboard (Stats Bar) -->
<div class="dashboard-metrics">
    <div class="metric-card">
        <div class="metric-icon">🧁</div>
        <div class="metric-data">
            <h4>Total Varian Menu</h4>
            <div class="metric-val"><?= (int)$statsTotalItems; ?> Item</div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon">💰</div>
        <div class="metric-data">
            <h4>Valuasi Etalase</h4>
            <div class="metric-val"><?= format_rupiah($statsTotalValuation); ?></div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon">⚠️</div>
        <div class="metric-data">
            <h4>Stok Menipis (&le; 5)</h4>
            <div class="metric-val"><?= (int)$statsLowStock; ?> Item</div>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon">🛡️</div>
        <div class="metric-data">
            <h4>Proteksi CSRF</h4>
            <div class="metric-val" style="font-size: 0.95rem; color: var(--status-success);">CSRF_PASS_ACTIVE</div>
        </div>
    </div>
</div>

<!-- Search & Filter Card (Slide 18) -->
<div class="search-filter-card">
    <form method="GET" action="index.php" class="filter-form">
        <div class="filter-search-box">
            <input type="text" name="q" class="form-control" placeholder="Cari nama kue, pastry, atau deskripsi..." value="<?= e($q); ?>">
        </div>

        <div class="filter-select-box">
            <select name="category" class="form-control">
                <option value="">-- Semua Kategori --</option>
                <?php foreach ($allCategories as $c): ?>
                    <option value="<?= e($c); ?>" <?= $category === $c ? 'selected' : ''; ?>>
                        <?= e($c); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <span>🔍</span> Cari Menu
        </button>

        <?php if ($q !== '' || $category !== ''): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Grid Card Produk Flexbox Responsif (Slide 17, 19, 20) -->
<?php if (empty($products)): ?>
    <div style="text-align: center; padding: 48px 20px; background: var(--bg-card); border: 1px dashed var(--border-color); border-radius: var(--radius);">
        <div style="font-size: 3rem; margin-bottom: 12px;">🥖</div>
        <h3>Menu Tidak Ditemukan</h3>
        <p style="color: var(--text-muted); margin-top: 6px;">Tidak ada kue atau pastry yang cocok dengan filter pencarian Anda.</p>
        <a href="create.php" class="btn btn-primary" style="margin-top: 16px;">+ Tambah Menu Baru</a>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($products as $item): ?>
            <div class="pastry-card">
                <div class="card-top">
                    <span class="sku-code">#SB-<?= str_pad((string)$item['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    <span class="category-badge"><?= e($item['category']); ?></span>
                </div>

                <div class="card-body">
                    <!-- Output Escaping e() untuk mencegah XSS (Slide 20: <b>Promo</b> tampil sebagai teks murni) -->
                    <h3 class="pastry-title"><?= e($item['name']); ?></h3>
                    <p class="pastry-desc"><?= e($item['description'] ?: 'Pilihan kue artisan lezat yang dipanggang segar setiap pagi.'); ?></p>

                    <div class="card-footer-meta">
                        <div class="pastry-price"><?= format_rupiah($item['price']); ?></div>
                        <div class="stock-tag <?= $item['stock'] == 0 ? 'stock-empty' : ($item['stock'] <= 5 ? 'stock-warning' : 'stock-fresh'); ?>">
                            <?= $item['stock'] == 0 ? 'Habis Terjual' : 'Sisa Stok: ' . (int)$item['stock'] . ' pcs'; ?>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="edit.php?id=<?= (int)$item['id']; ?>" class="btn btn-secondary btn-sm">
                            ✏️ Edit
                        </a>

                        <!-- Operasi Delete Aman: POST Method + Token CSRF (Slide 17 & 19) -->
                        <form method="POST" action="delete.php" style="flex: 1;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu <?= addslashes(e($item['name'])); ?>?');">
                            <input type="hidden" name="id" value="<?= (int)$item['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="width: 100%;">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
