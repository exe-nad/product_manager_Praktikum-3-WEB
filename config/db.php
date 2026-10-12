<?php
/**
 * config/db.php - Konfigurasi Koneksi Database PDO & Helper Keamanan
 * SweetBakes — Artisan Bakery & Cake Studio Manager
 * Mahasiswa: Nadya Salsabila Zahra (NIM: 250180031)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi Token CSRF per Sesi
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db_host = '127.0.0.1';
$db_name = 'store_db';
$db_user = 'root';
$db_pass = '';
$db_port = '3306';

$pdo = null;

try {
    // 1. Koneksi PDO ke MySQL
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

    // Pastikan kolom description otomatis ada jika memakai tabel lama
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM products LIKE 'description'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE products ADD COLUMN description TEXT NULL AFTER category;");
        }
    } catch (Exception $colEx) {
        // Abaikan jika tabel belum ada
    }
} catch (PDOException $e) {
    // 2. Jika database belum di-create, buat otomatis di MySQL
    try {
        $rootDsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $tempPdo = new PDO($rootDsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        $pdo = new PDO($dsn, $db_user, $db_pass, $options);

        $schemaPath = dirname(__DIR__) . '/database/store_db.sql';
        if (file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            $pdo->exec($sql);
        }
    } catch (PDOException $ex) {
        // 3. Fallback SQLite otomatis untuk kemudahan saat MySQL dimatikan
        $sqlitePath = dirname(__DIR__) . '/database/store_db.sqlite';
        try {
            $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $pdo->exec("CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                category TEXT NOT NULL DEFAULT 'Pastry',
                description TEXT NULL,
                price REAL NOT NULL,
                stock INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );");

            $check = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            if ($check == 0) {
                $seedSql = "INSERT INTO products (name, category, description, price, stock) VALUES
                ('Butter Croissant Parisienne', 'Pastry', 'Croissant mentega Prancis dengan tekstur renyah dan rongga berlapis sempurna.', 28000.00, 18),
                ('Pain au Chocolat Belgia', 'Pastry', 'Pastry gulung berisi lelehan cokelat hitam murni Belgia 70%.', 34000.00, 14),
                ('Signature Dark Chocolate Fudge Cake', 'Artisan Cake', 'Kue bolu cokelat lembut berlapis ganache dark chocolate premium.', 245000.00, 6),
                ('Uji Matcha Basque Cheesecake', 'Artisan Cake', 'Cheesecake panggang beraroma teh hijau Uji Kyoto dengan lumeran krim lembut.', 275000.00, 4),
                ('Red Velvet Cream Cheese Cupcake', 'Cupcake & Muffin', 'Cupcake merah beludru klasik berpadu frosting cream cheese vanila lembut.', 32000.00, 24),
                ('Blueberry Crumble Muffin', 'Cupcake & Muffin', 'Muffin vanila isi selai bluberi segar dengan taburan remah mentega garing.', 29000.00, 16),
                ('Double Choco Walnut Soft Cookie', 'Cookies & Treats', 'Kue kering empuk berlumur cokelat leleh dan renyahnya kacang walnut.', 22000.00, 30),
                ('Artisan French Sourdough Loaf', 'Bread & Loaf', 'Roti gandum ragi alami dengan kulit renyah dan tekstur dalam kenyal gurih.', 48000.00, 9);";
                $pdo->exec($seedSql);
            }
        } catch (Exception $sqliteErr) {
            die("Koneksi Database Gagal: " . $e->getMessage());
        }
    }
}

/**
 * Helper Anti-XSS (Output Escaping)
 */
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format mata uang Rupiah
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Validasi token CSRF anti timing-attack
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}
