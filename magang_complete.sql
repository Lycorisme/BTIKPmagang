-- =====================================================
-- DATABASE SISTEM MAGANG/PKL BTIKP
-- File: magang_complete.sql
-- Versi: 2.0 (Revisi Lengkap)
-- 
-- PETUNJUK PENGGUNAAN:
-- 1. Untuk InfinityFree: Import file ini di phpMyAdmin
--    (database: if0_40670400_magang)
-- 2. Untuk Localhost: Buat database 'magang' lalu import
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

-- =====================================================
-- TABEL: users
-- Menyimpan data akun pengguna (admin, mentor, peserta_magang)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('peserta_magang','mentor','admin') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: mentors
-- Menyimpan data tambahan untuk mentor
-- =====================================================
CREATE TABLE IF NOT EXISTS `mentors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `keahlian` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status_open` tinyint(1) DEFAULT 1 COMMENT '1=Aktif menerima bimbingan, 0=Tidak aktif',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `mentors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: peserta_magang
-- Menyimpan biodata lengkap peserta magang/PKL
-- =====================================================
CREATE TABLE IF NOT EXISTS `peserta_magang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_instansi` varchar(50) DEFAULT NULL COMMENT 'SMK, Universitas, Politeknik, Lainnya',
  `nama_instansi` varchar(255) DEFAULT NULL,
  `jurusan` varchar(255) DEFAULT NULL,
  `semester_kelas` varchar(50) DEFAULT NULL,
  `nim_nis` varchar(50) DEFAULT NULL,
  `surat_pengantar` varchar(255) DEFAULT NULL COMMENT 'File surat pengantar magang',
  `status_biodata` enum('belum_lengkap','lengkap') DEFAULT 'belum_lengkap',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `peserta_magang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: pendaftaran_magang
-- Menyimpan data pendaftaran magang (mengganti tabel lamaran)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pendaftaran_magang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tgl_daftar` date NOT NULL,
  `tgl_mulai` date DEFAULT NULL COMMENT 'Rencana tanggal mulai magang',
  `tgl_selesai` date DEFAULT NULL COMMENT 'Rencana tanggal selesai magang',
  `mentor_id` int(11) DEFAULT NULL COMMENT 'Diisi oleh admin setelah diterima',
  `status` enum('pending','diterima','ditolak','selesai') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `tgl_diterima` date DEFAULT NULL,
  `tgl_selesai_aktual` date DEFAULT NULL COMMENT 'Tanggal selesai sebenarnya',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mentor_id` (`mentor_id`),
  CONSTRAINT `pendaftaran_magang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_magang_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: absensi
-- Menyimpan data kehadiran harian peserta magang
-- =====================================================
CREATE TABLE IF NOT EXISTS `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_tanggal` (`user_id`, `tanggal`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: jurnal
-- Menyimpan jurnal aktivitas harian peserta magang
-- =====================================================
CREATE TABLE IF NOT EXISTS `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `aktivitas` text NOT NULL,
  `file_penunjang` varchar(255) DEFAULT NULL,
  `nilai` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('pending','dikoreksi','disetujui') DEFAULT 'pending',
  `tgl_koreksi` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `mentor_id` (`mentor_id`),
  CONSTRAINT `jurnal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jurnal_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL: sertifikat
-- Menyimpan data sertifikat setelah magang selesai
-- =====================================================
CREATE TABLE IF NOT EXISTS `sertifikat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pendaftaran_id` int(11) NOT NULL,
  `nomor_sertifikat` varchar(100) NOT NULL,
  `tgl_terbit` date NOT NULL,
  `file_sertifikat` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_sertifikat` (`nomor_sertifikat`),
  KEY `user_id` (`user_id`),
  KEY `pendaftaran_id` (`pendaftaran_id`),
  CONSTRAINT `sertifikat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sertifikat_ibfk_2` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran_magang` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DATA AWAL: Admin Default
-- Email: admin@btikp.go.id | Password: admin123
-- =====================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Administrator', 'admin@btikp.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- =====================================================
-- DATA AWAL: Mentor Contoh
-- Email: mentor@btikp.go.id | Password: mentor123
-- =====================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Budi Santoso', 'mentor@btikp.go.id', '$2y$10$xQh8TCZ8TqOBdBWNt6rFJeJc7.aQ1sFhGnM.fQwLp8.TbX9vXj6Uy', 'mentor');

INSERT INTO `mentors` (`user_id`, `keahlian`, `bio`, `status_open`) VALUES
(LAST_INSERT_ID(), 'Web Development, Database', 'Mentor berpengalaman dalam pengembangan web dan manajemen database.', 1);

-- =====================================================
-- DATA AWAL: Peserta Magang Contoh
-- Email: peserta@example.com | Password: peserta123
-- =====================================================
INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Ahmad Fauzi', 'peserta@example.com', '$2y$10$Y3VQZDl6eVlDQ1pxN3FnYeMvjZPtP0MgfA9lH5kQr.qL2LVhVTvZi', 'peserta_magang');

INSERT INTO `peserta_magang` (`user_id`, `no_hp`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `jenis_instansi`, `nama_instansi`, `jurusan`, `semester_kelas`, `nim_nis`, `status_biodata`) VALUES
(LAST_INSERT_ID(), '081234567890', 'L', '2003-05-15', 'Jl. Contoh No. 123, Kota', 'SMK', 'SMK Negeri 1 Contoh', 'Rekayasa Perangkat Lunak', 'XI', '12345', 'lengkap');

-- =====================================================
-- CATATAN LOGIN:
-- 
-- Admin:
--   Email: admin@btikp.go.id
--   Password: admin123
--
-- Mentor:
--   Email: mentor@btikp.go.id
--   Password: mentor123
--
-- Peserta Magang:
--   Email: peserta@example.com
--   Password: peserta123
--
-- =====================================================
