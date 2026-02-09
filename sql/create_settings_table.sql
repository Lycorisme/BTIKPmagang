-- =====================================================
-- SQL untuk membuat tabel SETTINGS
-- Jalankan di phpMyAdmin hosting InfinityFree
-- Database: if0_40670400_magang
-- =====================================================

-- Buat tabel settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(100) NOT NULL DEFAULT 'Sistem Magang',
  `site_description` text,
  `logo` varchar(255) DEFAULT 'logo.png',
  `favicon` varchar(255) DEFAULT 'favicon.ico',
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `footer_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert data default
INSERT INTO `settings` (`id`, `site_name`, `site_description`, `logo`, `email`, `footer_text`) VALUES
(1, 'BTIKP Magang', 'Sistem Informasi Magang BTIKP', 'logo.png', 'admin@example.com', '© 2026 BTIKP Magang. All rights reserved.');

-- =====================================================
-- SELESAI!
-- Setelah menjalankan SQL ini, coba lagi download PDF
-- =====================================================
