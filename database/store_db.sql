-- =====================================================================
-- Database Schema: store_db
-- Tema Proyek: SweetBakes — Artisan Bakery & Cake Studio Manager
-- Mahasiswa: Nadya Salsabila Zahra (NIM: 250180031)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `store_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `store_db`;

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(150)   NOT NULL UNIQUE,
    `category`    VARCHAR(60)    NOT NULL DEFAULT 'Pastry',
    `description` TEXT           NULL,
    `price`       DECIMAL(12, 2) NOT NULL,
    `stock`       INT            NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed Data: Menu Etalase SweetBakes
INSERT INTO `products` (`name`, `category`, `description`, `price`, `stock`) VALUES
('Butter Croissant Parisienne', 'Pastry', 'Croissant mentega Prancis dengan tekstur renyah dan rongga berlapis sempurna.', 28000.00, 18),
('Pain au Chocolat Belgia', 'Pastry', 'Pastry gulung berisi lelehan cokelat hitam murni Belgia 70%.', 34000.00, 14),
('Signature Dark Chocolate Fudge Cake', 'Artisan Cake', 'Kue bolu cokelat lembut berlapis ganache dark chocolate premium.', 245000.00, 6),
('Uji Matcha Basque Cheesecake', 'Artisan Cake', 'Cheesecake panggang beraroma teh hijau Uji Kyoto dengan lumeran krim lembut.', 275000.00, 4),
('Red Velvet Cream Cheese Cupcake', 'Cupcake & Muffin', 'Cupcake merah beludru klasik berpadu frosting cream cheese vanila lembut.', 32000.00, 24),
('Blueberry Crumble Muffin', 'Cupcake & Muffin', 'Muffin vanila isi selai bluberi segar dengan taburan remah mentega garing.', 29000.00, 16),
('Double Choco Walnut Soft Cookie', 'Cookies & Treats', 'Kue kering empuk berlumur cokelat leleh dan renyahnya kacang walnut.', 22000.00, 30),
('Artisan French Sourdough Loaf', 'Bread & Loaf', 'Roti gandum ragi alami dengan kulit renyah dan tekstur dalam kenyal gurih.', 48000.00, 9);
