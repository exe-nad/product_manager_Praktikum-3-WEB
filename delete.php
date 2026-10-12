<?php
// delete.php - SweetBakes: Penghapusan Menu Aman (POST Only, Validasi Token CSRF, PRG)
require_once __DIR__ . '/config/db.php';

// 1. Wajib Method POST (Menolak keras akses via GET untuk menutup celah CSRF)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['flash_error'] = 'Metode request tidak sah. Penghapusan menu harus menggunakan POST!';
    header('Location: index.php');
    exit;
}

// 2. Validasi Token CSRF Kriptografik
$submittedToken = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($submittedToken)) {
    http_response_code(403);
    $_SESSION['flash_error'] = 'Token CSRF tidak valid atau telah kedaluwarsa. Permintaan ditolak demi keamanan!';
    header('Location: index.php');
    exit;
}

// 3. Validasi ID Menu
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'ID menu tidak valid.';
    header('Location: index.php');
    exit;
}

try {
    // 4. Cari menu sebelum dihapus
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['flash_error'] = 'Menu tidak ditemukan atau sudah dihapus sebelumnya.';
        header('Location: index.php');
        exit;
    }

    // 5. Eksekusi DELETE dengan Prepared Statement
    $delStmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $delStmt->execute([':id' => $id]);

    // 6. Pola PRG Redirect dengan Flash Message
    $_SESSION['flash_success'] = 'Menu "' . $product['name'] . '" telah berhasil dihapus dari etalase.';
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['flash_error'] = 'Gagal menghapus menu: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}
