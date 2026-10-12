<?php
// edit.php - SweetBakes: Form Edit Menu (Pre-filled & Update PDO)
require_once __DIR__ . '/config/db.php';

$page_title = 'Edit Menu Pastry';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID menu tidak valid.';
    header('Location: index.php');
    exit;
}

// Ambil Data Produk Eksisting dengan Prepared Statement
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_error'] = 'Menu kue tidak ditemukan di etalase.';
    header('Location: index.php');
    exit;
}

$errors = [];
$name = $product['name'];
$category = $product['category'];
$price = $product['price'];
$stock = $product['stock'];
$description = $product['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validasi Server-Side
    if ($name === '') {
        $errors['name'] = 'Nama menu wajib diisi.';
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
        $errors['price'] = 'Harga tidak boleh bernilai negatif atau 0!';
    }

    if ($stock === '') {
        $errors['stock'] = 'Jumlah stok wajib diisi.';
    } elseif (!ctype_digit(strval($stock))) {
        $errors['stock'] = 'Stok harus berupa bilangan bulat tanpa desimal.';
    } elseif ((int)$stock < 0) {
        $errors['stock'] = 'Stok tidak boleh bernilai negatif!';
    }

    if (empty($errors)) {
        try {
            $updateSql = "UPDATE products SET 
                            name = :name, 
                            category = :category, 
                            price = :price, 
                            stock = :stock, 
                            description = :description 
                          WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':name'        => $name,
                ':category'    => $category,
                ':price'       => (float)$price,
                ':stock'       => (int)$stock,
                ':description' => $description !== '' ? $description : null,
                ':id'          => $id
            ]);

            // Pola PRG Redirect
            $_SESSION['flash_success'] = 'Perubahan data menu "' . $name . '" berhasil diperbarui!';
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate') !== false) {
                $errors['name'] = 'Menu dengan nama "' . $name . '" sudah digunakan item lain.';
            } else {
                $errors['general'] = 'Gagal memperbarui menu: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/header.php';
?>

<div style="margin: 24px 0 16px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 1.6rem; font-weight: 700;">Edit Menu Pastry</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui resep atau data inventaris menu #SB-<?= str_pad((string)$id, 4, '0', STR_PAD_LEFT); ?>.</p>
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

    <form method="POST" action="edit.php?id=<?= (int)$id; ?>" novalidate>
        <div class="form-group">
            <label for="name">Nama Menu Pastry / Cake *</label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                   value="<?= e($name); ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-msg"><?= e($errors['name']); ?></div>
            <?php else: ?>
                <div class="hint">Minimal 3 karakter.</div>
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
                       value="<?= e($price); ?>" required>
                <?php if (isset($errors['price'])): ?>
                    <div class="invalid-msg"><?= e($errors['price']); ?></div>
                <?php else: ?>
                    <div class="hint">Harus lebih besar dari 0.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="stock">Stok Tersedia (pcs) *</label>
            <input type="number" id="stock" name="stock" min="0" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : ''; ?>" 
                   value="<?= e($stock); ?>" required>
            <?php if (isset($errors['stock'])): ?>
                <div class="invalid-msg"><?= e($errors['stock']); ?></div>
            <?php else: ?>
                <div class="hint">Minimal 0.</div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Deskripsi Rasa &amp; Bahan (Opsional)</label>
            <textarea id="description" name="description" rows="3" class="form-control"><?= e($description); ?></textarea>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; justify-content: center;">
                Perbarui Menu
            </button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
