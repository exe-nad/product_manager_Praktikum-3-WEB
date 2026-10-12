<?php
// create.php - SweetBakes: Form Registrasi Menu Baru (Validasi Server-Side & Pola PRG)
require_once __DIR__ . '/config/db.php';

$page_title = 'Tambah Menu Baru';

$errors = [];
$name = '';
$category = '';
$price = '';
$stock = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // 1. Validasi Server-Side Ketat Sesuai Kriteria Slide 17 & 20
    if ($name === '') {
        $errors['name'] = 'Nama menu kue/pastry wajib diisi.';
    } elseif (mb_strlen($name) < 3) {
        $errors['name'] = 'Nama menu minimal harus 3 karakter.';
    }

    if ($category === '') {
        $errors['category'] = 'Silakan pilih kategori kue.';
    }

    if ($price === '') {
        $errors['price'] = 'Harga menu wajib diisi.';
    } elseif (!is_numeric($price)) {
        $errors['price'] = 'Harga harus berupa angka valid.';
    } elseif ((float)$price <= 0) {
        $errors['price'] = 'Harga tidak boleh 0 atau bernilai negatif. Masukkan harga positif!';
    }

    if ($stock === '') {
        $errors['stock'] = 'Jumlah stok wajib diisi.';
    } elseif (!ctype_digit(strval($stock))) {
        $errors['stock'] = 'Stok harus berupa bilangan bulat tanpa desimal.';
    } elseif ((int)$stock < 0) {
        $errors['stock'] = 'Stok tidak boleh bernilai negatif!';
    }

    // 2. Simpan ke Database Menggunakan PDO Prepared Statements
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO products (name, category, price, stock, description) 
                    VALUES (:name, :category, :price, :stock, :description)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'        => $name,
                ':category'    => $category,
                ':price'       => (float)$price,
                ':stock'       => (int)$stock,
                ':description' => $description !== '' ? $description : null
            ]);

            // 3. Pola PRG (Post-Redirect-Get) Anti-Duplikasi
            $_SESSION['flash_success'] = 'Menu "' . $name . '" berhasil ditambahkan ke etalase SweetBakes!';
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            // Penanganan Duplikasi Nama Unik (Kode 23000)
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'UNIQUE') !== false) {
                $errors['name'] = 'Menu dengan nama "' . $name . '" sudah terdaftar di etalase. Gunakan nama lain!';
            } else {
                $errors['general'] = 'Gagal menyimpan menu ke database: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/header.php';
?>

<div style="margin: 24px 0 16px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700;">Tambah Menu Baru</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Daftarkan resep pastry atau cake artisan terbaru ke etalase.</p>
    </div>
    <a href="index.php" class="btn btn-secondary">
        &larr; Kembali ke Etalase
    </a>
</div>

<div class="form-box">
    <?php if (!empty($errors['general'])): ?>
        <div class="alert-banner alert-danger">
            <span><?= e($errors['general']); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="create.php" novalidate>
        <div class="form-group">
            <label for="name">Nama Menu Pastry / Cake *</label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                   placeholder="Contoh: Strawberry Mille-Feuille" value="<?= e($name); ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-msg"><?= e($errors['name']); ?></div>
            <?php else: ?>
                <div class="hint">Minimal 3 karakter. Nama harus unik.</div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category">Kategori *</label>
                <select id="category" name="category" class="form-control <?= isset($errors['category']) ? 'is-invalid' : ''; ?>" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php 
                    $categoriesPreset = ['Pastry', 'Artisan Cake', 'Cupcake & Muffin', 'Cookies & Treats', 'Bread & Loaf'];
                    foreach ($categoriesPreset as $c): ?>
                        <option value="<?= e($c); ?>" <?= $category === $c ? 'selected' : ''; ?>>
                            <?= e($c); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category'])): ?>
                    <div class="invalid-msg"><?= e($errors['category']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="price">Harga Satuan (Rp) *</label>
                <input type="number" id="price" name="price" step="500" min="1" class="form-control <?= isset($errors['price']) ? 'is-invalid' : ''; ?>" 
                       placeholder="Contoh: 35000" value="<?= e($price); ?>" required>
                <?php if (isset($errors['price'])): ?>
                    <div class="invalid-msg"><?= e($errors['price']); ?></div>
                <?php else: ?>
                    <div class="hint">Wajib lebih besar dari 0 (tidak boleh negatif).</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="stock">Stok Tersedia (pcs) *</label>
            <input type="number" id="stock" name="stock" min="0" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : ''; ?>" 
                   placeholder="Contoh: 15" value="<?= e($stock); ?>" required>
            <?php if (isset($errors['stock'])): ?>
                <div class="invalid-msg"><?= e($errors['stock']); ?></div>
            <?php else: ?>
                <div class="hint">Minimal 0 (tidak boleh bernilai negatif).</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Rasa &amp; Bahan (Opsional)</label>
            <textarea id="description" name="description" rows="3" class="form-control" placeholder="Contoh: Lapisan puff pastry renyah dengan krim diplomat vanila Madagaskar dan stroberi segar."><?= e($description); ?></textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                Simpan ke Etalase
            </button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
