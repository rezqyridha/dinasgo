-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- CREATE DATABASE (opsional, bisa dihapus jika sudah buat manual)
CREATE DATABASE IF NOT EXISTS `dinasgo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `dinasgo`;

-- =========================
-- 1. TABEL UTAMA (tanpa FK)
-- =========================
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','pegawai','atasan','bendahara') NOT NULL,
  `status` ENUM('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `user` (`id`, `nama`, `username`, `password`, `role`, `status`) VALUES
(1, 'Admin Utama', 'admin01', 'admin123', 'admin', 'aktif'),
(2, 'Budi Pegawai', 'pegawai01', 'pegawai123', 'pegawai', 'aktif'),
(3, 'Andi Atasan', 'atasan01', 'atasan123', 'atasan', 'aktif'),
(4, 'Sari Bendahara', 'bendahara01', 'bendahara123', 'bendahara', 'aktif'),
(5, 'test', 'testpegawai12', 'test123', 'pegawai', 'nonaktif');

-- ============================
-- 2. TABEL BERGANTUNG PADA USER
-- ============================
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `nip` VARCHAR(30) DEFAULT NULL,
  `nama` VARCHAR(100) DEFAULT NULL,
  `jabatan` VARCHAR(100) DEFAULT NULL,
  `no_hp` VARCHAR(15) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `alamat` TEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `pegawai` (`id`, `id_user`, `nip`, `nama`, `jabatan`, `no_hp`, `email`, `alamat`) VALUES
(1, 2, '198512312022011001', 'Ahmad Faisal', 'Staf Teknik', '081234567890', 'ahmad@example.com', 'Jl. Sungai Martapura No. 12'),
(2, 1, '45789652', 'tets', 'tets edit', '785151', 'contoh@example.com', 'JL Test'),
(4, 1, '78521415', 'Dolor sunt quaerat', 'Consequatur saepe an', '147862255', 'kamami@mailinator.com', 'Ut debitis a corpori');

CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `pesan` TEXT,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT '0',
  `dibuat_pada` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================
-- 3. TABEL PENGAJUAN (butuh pegawai)
-- =====================================
CREATE TABLE IF NOT EXISTS `pengajuan_perjalanan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pegawai` INT NOT NULL,
  `tujuan` VARCHAR(150) DEFAULT NULL,
  `tanggal_berangkat` DATE DEFAULT NULL,
  `tanggal_kembali` DATE DEFAULT NULL,
  `keperluan` TEXT,
  `estimasi_biaya` DECIMAL(12,2) DEFAULT NULL,
  `status` ENUM('diajukan','disetujui','ditolak','selesai') DEFAULT 'diajukan',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  CONSTRAINT `pengajuan_perjalanan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- =====================================
-- 4. TABEL BERGANTUNG PADA PENGAJUAN
-- =====================================
CREATE TABLE IF NOT EXISTS `dokumen` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `id_user` INT NOT NULL,
  `nama_file` VARCHAR(255) DEFAULT NULL,
  `jenis` ENUM('surat_tugas','bukti_biaya','lainnya') DEFAULT NULL,
  `uploaded_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dokumen_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.dokumen: ~2 rows (approximately)
INSERT INTO `dokumen` (`id`, `id_pengajuan`, `id_user`, `nama_file`, `jenis`, `uploaded_at`) VALUES
	(1, 1, 1, 'surat_tugas_jakarta.pdf', 'surat_tugas', '2025-06-05 14:11:01'),
	(2, 2, 2, 'bukti_biaya_bandung.jpg', 'bukti_biaya', '2025-06-05 14:11:01');

-- Dumping structure for table dinasgo.dokumentasi_kegiatan
CREATE TABLE IF NOT EXISTS `dokumentasi_kegiatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `jenis_dokumentasi` enum('foto','laporan','video') DEFAULT 'foto',
  `nama_file` varchar(255) DEFAULT NULL,
  `keterangan` text,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  CONSTRAINT `dokumentasi_kegiatan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.dokumentasi_kegiatan: ~0 rows (approximately)

-- Dumping structure for table dinasgo.evaluasi_perjalanan
CREATE TABLE IF NOT EXISTS `evaluasi_perjalanan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `id_pegawai` INT NOT NULL,
  `kendala` TEXT,
  `hasil` TEXT,
  `saran` TEXT,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_pegawai` (`id_pegawai`),
  CONSTRAINT `evaluasi_perjalanan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `evaluasi_perjalanan_ibfk_2` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.evaluasi_perjalanan: ~2 rows (approximately)
INSERT INTO `evaluasi_perjalanan` (`id`, `id_pengajuan`, `id_pegawai`, `kendala`, `hasil`, `saran`) VALUES
	(1, 1, 1, 'Cuaca sempat menghambat', 'Rapat berjalan lancar', 'Perlu backup transportasi'),
	(2, 2, 2, 'Kendala teknis laptop', 'Sosialisasi selesai tepat waktu', 'Siapkan cadangan peralatan');

-- Dumping structure for table dinasgo.kepala
CREATE TABLE IF NOT EXISTS `kepala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `ttd_gambar` varchar(255) DEFAULT NULL,
  `tahun` varchar(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.kepala: ~0 rows (approximately)

-- Dumping structure for table dinasgo.notifikasi
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `pesan` text,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.notifikasi: ~2 rows (approximately)
INSERT INTO `notifikasi` (`id`, `id_user`, `pesan`, `link`, `is_read`, `dibuat_pada`) VALUES
	(1, 1, 'Pengajuan perjalanan Anda telah disetujui', '/modules/shared/pengajuan/index.php', 0, '2025-06-05 14:11:01'),
	(2, 2, 'SPT telah diterbitkan', '/modules/shared/spt/index.php', 1, '2025-06-05 14:11:01');

-- Dumping structure for table dinasgo.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.pegawai: ~4 rows (approximately)
INSERT INTO `pegawai` (`id`, `id_user`, `nip`, `nama`, `jabatan`, `no_hp`, `email`, `alamat`) VALUES
	(1, 2, '198512312022011001', 'Ahmad Faisal', 'Staf Teknik', '081234567890', 'ahmad@example.com', 'Jl. Sungai Martapura No. 12'),
	(2, 1, '45789652', 'tets', 'tets edit', '785151', 'contoh@example.com', 'JL Test'),
	(4, 1, '78521415', 'Dolor sunt quaerat', 'Consequatur saepe an', '147862255', 'kamami@mailinator.com', 'Ut debitis a corpori');

-- Dumping structure for table dinasgo.pencairan_dana
CREATE TABLE IF NOT EXISTS `pencairan_dana` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_bendahara` int NOT NULL,
  `jumlah_dana` decimal(12,2) DEFAULT NULL,
  `tanggal_pencairan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_bendahara` (`id_bendahara`),
  CONSTRAINT `pencairan_dana_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pencairan_dana_ibfk_2` FOREIGN KEY (`id_bendahara`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.pencairan_dana: ~2 rows (approximately)
INSERT INTO `pencairan_dana` (`id`, `id_pengajuan`, `id_bendahara`, `jumlah_dana`, `tanggal_pencairan`) VALUES
	(1, 1, 4, 2500000.00, '2024-07-10'),
	(2, 2, 4, 1800000.00, '2024-08-01');

-- Dumping structure for table dinasgo.pengajuan_perjalanan
CREATE TABLE IF NOT EXISTS `pengajuan_perjalanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pegawai` int NOT NULL,
  `tujuan` varchar(150) DEFAULT NULL,
  `tanggal_berangkat` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `keperluan` text,
  `estimasi_biaya` decimal(12,2) DEFAULT NULL,
  `status` enum('diajukan','disetujui','ditolak','selesai') DEFAULT 'diajukan',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  CONSTRAINT `pengajuan_perjalanan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.pengajuan_perjalanan: ~4 rows (approximately)
INSERT INTO `pengajuan_perjalanan` (`id`, `id_pegawai`, `tujuan`, `tanggal_berangkat`, `tanggal_kembali`, `keperluan`, `estimasi_biaya`, `status`, `created_at`) VALUES
	(1, 1, 'Jakarta', '2024-07-10', '2024-07-13', 'Menghadiri rapat koordinasi', 2500000.00, 'diajukan', '2025-06-05 14:11:01'),
	(2, 2, 'Bandung', '2024-08-01', '2024-08-03', 'Sosialisasi aplikasi DinasGo', 1800000.00, 'disetujui', '2025-06-05 14:11:01'),
	(3, 1, 'Jakarta', '2024-07-10', '2024-07-13', 'Menghadiri rapat koordinasi', 2500000.00, 'diajukan', '2025-06-05 14:11:15'),
	(4, 2, 'Bandung', '2024-08-01', '2024-08-03', 'Sosialisasi aplikasi DinasGo', 1800000.00, 'disetujui', '2025-06-05 14:11:15');

-- Dumping structure for table dinasgo.persetujuan
CREATE TABLE IF NOT EXISTS `persetujuan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_atasan` int NOT NULL,
  `catatan` text,
  `status` enum('disetujui','ditolak') NOT NULL,
  `tanggal_persetujuan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_atasan` (`id_atasan`),
  CONSTRAINT `persetujuan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `persetujuan_ibfk_2` FOREIGN KEY (`id_atasan`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.persetujuan: ~2 rows (approximately)
INSERT INTO `persetujuan` (`id`, `id_pengajuan`, `id_atasan`, `catatan`, `status`, `tanggal_persetujuan`) VALUES
	(1, 1, 3, 'Disetujui karena penting', 'disetujui', '2024-07-09'),
	(2, 2, 3, 'Silakan lanjutkan', 'disetujui', '2024-07-28');

-- Dumping structure for table dinasgo.rencana_perjalanan
CREATE TABLE IF NOT EXISTS `rencana_perjalanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pegawai` int NOT NULL,
  `tujuan` varchar(150) DEFAULT NULL,
  `tanggal_berangkat` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `jenis_transportasi` varchar(100) DEFAULT NULL,
  `tujuan_kegiatan` text,
  `status` enum('direncanakan','diajukan','dibatalkan') DEFAULT 'direncanakan',
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  CONSTRAINT `rencana_perjalanan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.rencana_perjalanan: ~0 rows (approximately)

-- Dumping structure for table dinasgo.sppd
CREATE TABLE IF NOT EXISTS `sppd` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `nomor_sppd` VARCHAR(50) DEFAULT NULL,
  `tanggal_terbit` DATE DEFAULT NULL,
  `catatan` TEXT,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  CONSTRAINT `sppd_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.sppd: ~2 rows (approximately)
INSERT INTO `sppd` (`id`, `id_pengajuan`, `nomor_sppd`, `tanggal_terbit`, `catatan`) VALUES
	(1, 1, 'SPPD-001/2024', '2024-07-09', 'Diterbitkan oleh sekretariat'),
	(2, 2, 'SPPD-002/2024', '2024-07-30', 'Sesuai hasil persetujuan atasan');

-- Dumping structure for table dinasgo.spt
CREATE TABLE IF NOT EXISTS `spt` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `nomor_spt` VARCHAR(100) NOT NULL,
  `tanggal_spt` DATE NOT NULL,
  `maksud_perjalanan` TEXT NOT NULL,
  `lama_perjalanan` VARCHAR(50) NOT NULL,
  `transportasi` VARCHAR(100) NOT NULL,
  `status` ENUM('draft','ditandatangani','dibatalkan') DEFAULT 'draft',
  `ditandatangani_oleh` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_spt` (`nomor_spt`),
  KEY `fk_spt_pengajuan` (`id_pengajuan`),
  KEY `fk_spt_user` (`ditandatangani_oleh`),
  CONSTRAINT `fk_spt_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_spt_user` FOREIGN KEY (`ditandatangani_oleh`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.spt: ~2 rows (approximately)
INSERT INTO `spt` (`id`, `id_pengajuan`, `nomor_spt`, `tanggal_spt`, `maksud_perjalanan`, `lama_perjalanan`, `transportasi`, `status`, `ditandatangani_oleh`, `created_at`, `updated_at`) VALUES
	(1, 1, 'SPT-001/2024', '2024-07-08', 'Menghadiri rapat koordinasi', '3 hari', 'Pesawat', 'draft', NULL, '2025-06-05 21:11:01', '2025-06-05 21:11:01'),
	(2, 2, 'SPT-002/2024', '2024-07-29', 'Sosialisasi aplikasi DinasGo', '2 hari', 'Kereta Api', 'ditandatangani', 3, '2025-06-05 21:11:01', '2025-06-05 21:11:01');

-- Dumping structure for table dinasgo.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai','atasan','bendahara') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.user: ~4 rows (approximately)
INSERT INTO `user` (`id`, `nama`, `username`, `password`, `role`, `status`) VALUES
	(1, 'Admin Utama', 'admin01', 'admin123', 'admin', 'aktif'),
	(2, 'Budi Pegawai', 'pegawai01', 'pegawai123', 'pegawai', 'aktif'),
	(3, 'Andi Atasan', 'atasan01', 'atasan123', 'atasan', 'aktif'),
	(4, 'Sari Bendahara', 'bendahara01', 'bendahara123', 'bendahara', 'aktif'),
	(5, 'test', 'testpegawai12', 'test123', 'pegawai', 'nonaktif');
CREATE TABLE IF NOT EXISTS `pencairan_dana` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `id_bendahara` INT NOT NULL,
  `jumlah_dana` DECIMAL(12,2) DEFAULT NULL,
  `tanggal_pencairan` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_bendahara` (`id_bendahara`),
  CONSTRAINT `pencairan_dana_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pencairan_dana_ibfk_2` FOREIGN KEY (`id_bendahara`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `persetujuan` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pengajuan` INT NOT NULL,
  `id_atasan` INT NOT NULL,
  `catatan` TEXT,
  `status` ENUM('disetujui','ditolak') NOT NULL,
  `tanggal_persetujuan` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_atasan` (`id_atasan`),
  CONSTRAINT `persetujuan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `persetujuan_ibfk_2` FOREIGN KEY (`id_atasan`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
