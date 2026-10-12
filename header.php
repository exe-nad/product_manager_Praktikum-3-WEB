<?php
// header.php - Template Navigasi & Identitas SweetBakes
require_once __DIR__ . '/config/db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' &mdash; ' : '' ?>SweetBakes Studio</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        // Dual Theme Handler (Light & Dark)
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme') || 'light';
            const next = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', next);
            localStorage.setItem('sb_theme', next);
        }
        (function() {
            const saved = localStorage.getItem('sb_theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>
</head>
<body>
    <header class="navbar">
        <div class="container nav-inner">
            <a href="index.php" class="brand-wrapper">
                <div class="brand-icon">🥐</div>
                <div>
                    <div class="brand-title">SweetBakes</div>
                    <div class="brand-subtitle">Artisan Pastry &amp; Cake Studio &bull; <strong>Nadya Salsabila Zahra (250180031)</strong></div>
                </div>
            </a>
            <div class="nav-actions">
                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleTheme()" title="Ubah Tema (Dark/Light)">
                    🌓 Mode
                </button>
                <a href="index.php" class="btn btn-secondary btn-sm">Daftar Menu</a>
                <a href="create.php" class="btn btn-primary btn-sm">
                    <span>+</span> Tambah Menu Baru
                </a>
            </div>
        </div>
    </header>

    <main class="container" style="flex: 1;">
        <!-- Flash Alert Feedback (Pola PRG) -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert-banner alert-success">
                <span>&#10003;</span>
                <span><?= e($_SESSION['flash_success']); ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert-banner alert-danger">
                <span>&#9888;</span>
                <span><?= e($_SESSION['flash_error']); ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
