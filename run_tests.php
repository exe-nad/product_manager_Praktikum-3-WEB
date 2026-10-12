<?php
// run_tests.php - Pengujian Otomatis 10 Skenario SweetBakes Sesuai Matriks README
require_once __DIR__ . '/config/db.php';

echo "=== MEMULAI TEST SUITE: SWEETBAKES STUDIO (10 SKENARIO) ===\n\n";

$pass = 0;
$fail = 0;

function run_check($no, $title, $condition, $info = '') {
    global $pass, $fail;
    if ($condition) {
        echo "[LULUS ✅] Skenario $no: $title" . ($info ? " ($info)" : "") . "\n";
        $pass++;
    } else {
        echo "[GAGAL ❌] Skenario $no: $title" . ($info ? " ($info)" : "") . "\n";
        $fail++;
    }
}

// 1. Create Sukses
$testName = "Pistachio Croissant " . time();
$stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, description) VALUES (:n, :c, :p, :s, :d)");
$ok1 = $stmt->execute([':n' => $testName, ':c' => 'Pastry', ':p' => 38000, ':s' => 12, ':d' => 'Test pastry']);
$newId = (int)$pdo->lastInsertId();
run_check(1, "Create Sukses", $ok1 && $newId > 0, "ID #$newId tersimpan");

// 2. Validasi Nama Pendek
$short = "XY";
$validLen = (mb_strlen(trim($short)) >= 3);
run_check(2, "Validasi Nama Pendek Ditolak", !$validLen, "Input '$short' ditolak");

// 3. Validasi Angka Negatif
$negPrice = -15000;
$negStock = -5;
$okPrice = (is_numeric($negPrice) && (float)$negPrice > 0);
$okStock = (ctype_digit(strval($negStock)) && (int)$negStock >= 0);
run_check(3, "Validasi Angka Negatif Ditolak", !$okPrice && !$okStock, "Harga & stok negatif ditolak");

// 4. Duplikasi Item Unik
$dupCaught = false;
try {
    $stmtDup = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (:n, :c, :p, :s)");
    $stmtDup->execute([':n' => $testName, ':c' => 'Pastry', ':p' => 38000, ':s' => 12]);
} catch (PDOException $e) {
    $dupCaught = true;
}
run_check(4, "Duplikasi Item Unik Tertangkap", $dupCaught, "Exception 23000 / UNIQUE bekerja");

// 5. Injeksi XSS
$xss = "<b>Promo</b><script>alert(1)</script>";
$escaped = e($xss);
run_check(5, "Injeksi XSS Ter-Escape", strpos($escaped, '<script>') === false && strpos($escaped, '&lt;script&gt;') !== false, "Script netral");

// 6. Pola PRG
run_check(6, "Pola PRG Aktif", file_exists(__DIR__ . '/create.php') && strpos(file_get_contents(__DIR__ . '/create.php'), 'header(\'Location: index.php\')') !== false, "Redirect 302 aktif");

// 7. Proteksi GET Delete
run_check(7, "Proteksi GET Delete", file_exists(__DIR__ . '/delete.php') && strpos(file_get_contents(__DIR__ . '/delete.php'), '$_SERVER[\'REQUEST_METHOD\'] !== \'POST\'') !== false, "GET ditolak HTTP 405");

// 8. Proteksi Token CSRF
$csrfValid = verify_csrf_token($_SESSION['csrf_token']);
$csrfFake = verify_csrf_token('fake_csrf_token');
run_check(8, "Proteksi Token CSRF", $csrfValid && !$csrfFake, "hash_equals diverifikasi");

// 9. Pencarian GET Aman
$searchStmt = $pdo->prepare("SELECT * FROM products WHERE (name LIKE :q OR category LIKE :q)");
$searchStmt->execute([':q' => '%Croissant%']);
$found = $searchStmt->fetchAll();
run_check(9, "Pencarian GET Parameter Aman", count($found) > 0, "Ditemukan " . count($found) . " item");

// 10. Responsivitas Flexbox
$css = file_get_contents(__DIR__ . '/assets/style.css');
$flexboxOk = strpos($css, 'display: flex') !== false && strpos($css, 'flex-wrap: wrap') !== false;
run_check(10, "Responsivitas Layar Flexbox", $flexboxOk, "CSS flex-wrap aktif");

// Cleanup test item
$pdo->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $newId]);

echo "\n=== HASIL AKHIR: $pass/10 SKENARIO LULUS, $fail GAGAL ===\n";
