-- =====================================================
-- REVISI DATABASE SISTEM MAGANG BTIKP
-- Jalankan script ini untuk update database
-- =====================================================

-- 1. Update enum role di tabel users (tambah peserta_magang, ubah mahasiswa)
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('peserta_magang','mentor','admin') NOT NULL;

-- Update data lama dari 'mahasiswa' ke 'peserta_magang'
UPDATE `users` SET `role` = 'peserta_magang' WHERE `role` = 'mahasiswa';

-- 2. Buat tabel biodata peserta magang
CREATE TABLE IF NOT EXISTS `peserta_magang` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `no_hp` varchar(20) NOT NULL,
    `jenis_kelamin` enum('L','P') NOT NULL,
    `tanggal_lahir` date NOT NULL,
    `alamat` text NOT NULL,
    `jenis_instansi` varchar(50) NOT NULL COMMENT 'SMK, Universitas, Politeknik, Lainnya',
    `nama_instansi` varchar(255) NOT NULL,
    `jurusan` varchar(255) NOT NULL,
    `semester_kelas` varchar(50) NOT NULL,
    `nim_nis` varchar(50) NOT NULL,
    `surat_pengantar` varchar(255) DEFAULT NULL COMMENT 'File surat pengantar magang',
    `status_biodata` enum('belum_lengkap','lengkap') DEFAULT 'belum_lengkap',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`),
    CONSTRAINT `peserta_magang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Buat tabel pendaftaran magang (mengganti sistem lowongan + lamaran)
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
    CONSTRAINT `pendaftaran_magang_ibfk_2` FOREIGN KEY (`mentor_id`) REFERENCES `mentors` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Buat tabel absensi/kehadiran
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

-- 5. Update tabel jurnal - hapus kebutuhan mentor_id saat submit (nanti diisi setelah ada mentor assigned)
ALTER TABLE `jurnal` 
MODIFY COLUMN `mentor_id` int(11) DEFAULT NULL,
ADD COLUMN `status` enum('pending','dikoreksi','disetujui') DEFAULT 'pending' AFTER `nilai`,
ADD COLUMN `tgl_koreksi` date DEFAULT NULL AFTER `status`;

-- 6. Buat tabel sertifikat
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

-- 7. Buat view untuk cek kelengkapan data peserta
CREATE OR REPLACE VIEW `v_peserta_lengkap` AS
SELECT 
    u.id as user_id,
    u.nama,
    u.email,
    pm.status_biodata,
    pm.surat_pengantar,
    CASE 
        WHEN pm.status_biodata = 'lengkap' AND pm.surat_pengantar IS NOT NULL THEN 'siap_daftar'
        WHEN pm.id IS NULL THEN 'belum_isi_biodata'
        WHEN pm.surat_pengantar IS NULL THEN 'belum_upload_surat'
        ELSE 'belum_lengkap'
    END as status_kelengkapan
FROM users u
LEFT JOIN peserta_magang pm ON u.id = pm.user_id
WHERE u.role = 'peserta_magang';

-- =====================================================
-- END OF MIGRATION
-- =====================================================
