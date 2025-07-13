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


-- =================================================================
-- DATABASE
-- =================================================================
CREATE DATABASE IF NOT EXISTS `dinasgo`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `dinasgo`;

-- =================================================================
-- 1️⃣ USER (MASTER)
-- =================================================================
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai','atasan','bendahara') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` VALUES
  (1, 'Admin Utama', 'admin01', 'admin123', 'admin', 'aktif'),
  (2, 'Rina Ayu', 'pegawai01', 'user123', 'pegawai', 'aktif'),
  (3, 'Andi Atasan', 'atasan01', 'atasan123', 'atasan', 'aktif'),
  (4, 'Fajar Santoso', 'bendahara01', 'bendahara123', 'bendahara', 'aktif'),
  (5, 'Bahri', 'bahri123', 'test123', 'pegawai', 'aktif'),
  (7, 'test1', 'tes', 'test123', 'pegawai', 'nonaktif');

-- =================================================================
-- 2️⃣ KEPALA (MASTER)
-- =================================================================
CREATE TABLE IF NOT EXISTS `kepala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(30) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `ttd_gambar` varchar(255) DEFAULT NULL,
  `tahun` varchar(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kepala` VALUES
  (1, 'Dr. Ir. Sutrisno, MT', '196504011993031001', 'Kepala Balai Wilayah Sungai', 'ttd_sutrisno.png', '2025', '2025-06-25 07:21:21');

-- =================================================================
-- 3️⃣ PEGAWAI (MASTER)
-- =================================================================
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `id_atasan` int DEFAULT NULL,
  `nip` varchar(30) UNIQUE,
  `nama` varchar(100),
  `jabatan` varchar(100),
  `no_hp` varchar(15),
  `email` varchar(100),
  `alamat` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`id_atasan`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pegawai` VALUES
  (1, 2, 3, '197812312001011001', 'Rina Ayu', 'Pegawai', '081234567890', 'rina@dinas.go.id', 'Jl. Merdeka No. 1'),
  (2, 4, 3, '198006152002121002', 'Fajar Santoso', 'Kasubag Keuangan', '081298889999', 'fajar@dinas.go.id', 'Jl. Mataram No. 3'),
  (4, 5, 3, '198512312022011001', 'Bahri', 'Pegawai', '085978984565', 'bahri@dinas.go.id', 'Jl.Pramuka No 44');

-- =================================================================
-- 4️⃣ PENGAJUAN PERJALANAN
-- =================================================================
CREATE TABLE IF NOT EXISTS `pengajuan_perjalanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pegawai` int NOT NULL,
  `tujuan` varchar(150),
  `tanggal_berangkat` date,
  `tanggal_kembali` date,
  `keperluan` text,
  `estimasi_biaya` decimal(12,2),
  `status` enum('diajukan','disetujui','ditolak','selesai') DEFAULT 'diajukan',
  `diverifikasi_oleh` int DEFAULT NULL,
  `catatan_verifikasi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pengajuan_perjalanan` VALUES
  (1, 1, 'Hulu Sungai', '2025-07-14', '2025-07-18', 'Sosialisasi Aplikasi DinasGO', 5000000.00, 'disetujui', 3, 'Disetujui karena mendukung target kegiatan', '2025-07-08 10:49:14'),
  (2, 1, 'Banjarmasin', '2025-07-07', '2025-07-09', 'Sosialisasi aplikasi dinasgo', 2500000.00, 'diajukan', NULL, NULL, '2025-07-10 06:22:48');

-- =================================================================
-- 5️⃣ PERSETUJUAN
-- =================================================================
CREATE TABLE IF NOT EXISTS `persetujuan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_verifikator` int DEFAULT NULL,
  `catatan` text,
  `status` enum('disetujui','ditolak') NOT NULL,
  `tanggal_persetujuan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_verifikator`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `persetujuan` VALUES
  (1, 1, 3, 'Disetujui karena mendukung target kegiatan', 'disetujui', '2025-07-08');

-- =================================================================
-- 6️⃣ DOKUMEN
-- =================================================================
CREATE TABLE IF NOT EXISTS `dokumen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_file` varchar(255),
  `jenis` enum('surat_tugas','bukti_pengeluaran','sppd','lainnya'),
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unik_pengajuan_jenis` (`id_pengajuan`,`jenis`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `dokumen` VALUES
  (2, 1, 2, '1752134221_sppd.pdf', 'sppd', '2025-07-10 07:57:01'),
  (3, 1, 2, '1752134236_dokumen_relevan_lainnya.pdf', 'lainnya', '2025-07-10 07:57:16'),
  (4, 1, 2, '1752139259_nota_bukti_pengeluaran_dinas.pdf', 'bukti_pengeluaran', '2025-07-10 07:57:30');

-- =================================================================
-- 7️⃣ SPT
-- =================================================================
CREATE TABLE IF NOT EXISTS `spt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_spt` varchar(100) NOT NULL UNIQUE,
  `tanggal_spt` date NOT NULL,
  `maksud_perjalanan` text NOT NULL,
  `lama_perjalanan` varchar(50) NOT NULL,
  `transportasi` varchar(100) NOT NULL,
  `status` enum('draft','ditandatangani','dibatalkan') DEFAULT 'draft',
  `ditandatangani_oleh` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`ditandatangani_oleh`) REFERENCES `kepala`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `spt` VALUES
  (1, 1, 'SPT-001/2025', '2025-07-11', 'Sosialisasi Aplikasi DinasGO', '5 hari', 'Mobil Dinas', 'ditandatangani', 1, '2025-07-08 19:28:26', '2025-07-08 19:35:25');

-- =================================================================
-- 8️⃣ SPPD
-- =================================================================
CREATE TABLE IF NOT EXISTS `sppd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_sppd` varchar(50),
  `tanggal_terbit` date,
  `catatan` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `sppd` VALUES
  (1, 1, 'SPPD/001/2025', '2025-07-12', 'Koordinasi dengan pihak terkait di lokasi tujuan.');

-- =================================================================
-- 9️⃣ RINCIAN BIAYA
-- =================================================================
CREATE TABLE IF NOT EXISTS `rincian_biaya` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_rincian` varchar(100) NOT NULL UNIQUE,
  `tanggal_rincian` date NOT NULL,
  `jumlah_total` decimal(15,2) NOT NULL,
  `status` enum('draft','diajukan','disetujui','ditolak','selesai') DEFAULT 'draft',
  `id_bendahara_verifikasi` int DEFAULT NULL,
  `dibuat_oleh` int DEFAULT NULL,
  `id_pemilik` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dibuat_oleh`) REFERENCES `user`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_pemilik`) REFERENCES `user`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_bendahara_verifikasi`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rincian_biaya` VALUES
  (4, 1, 'RB-001/2025', '2025-07-10', 5000000.00, 'disetujui', 4, 1, 2, '2025-07-10 17:58:12', '2025-07-10 18:32:23');

-- =================================================================
-- 🔟 RINCIAN BIAYA DETAIL
-- =================================================================
CREATE TABLE IF NOT EXISTS `rincian_biaya_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rincian` int NOT NULL,
  `jenis_biaya` varchar(100) NOT NULL,
  `keterangan` text,
  `jumlah` int NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `total` decimal(15,2) GENERATED ALWAYS AS (`jumlah` * `harga_satuan`) STORED,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_rincian`) REFERENCES `rincian_biaya`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `rincian_biaya_detail` VALUES
  (1, 4, 'Transportasi', 'Perjalanan dinas ke luar kota', 2, 'orang', 1000000.00),
  (2, 4, 'Penginapan', 'Hotel 2 malam', 2, 'malam', 750000.00),
  (3, 4, 'Uang Makan', 'Uang makan harian', 2, 'hari', 500000.00),
  (4, 4, 'Lain-lain', 'Biaya tak terduga', 1, 'paket', 500000.00);

-- =================================================================
-- 1️⃣1️⃣ PENCAIRAN DANA
-- =================================================================
CREATE TABLE IF NOT EXISTS `pencairan_dana` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_rincian_biaya` int DEFAULT NULL,
  `id_bendahara` int NOT NULL,
  `jumlah_dana` varchar(50),
  `tanggal_pencairan` date,
  `tanggal_finalisasi` date,
  `id_admin_finalisasi` int DEFAULT NULL,
  `status` enum('draft','dicairkan','selesai') DEFAULT 'draft',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_rincian_biaya`) REFERENCES `rincian_biaya`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`id_bendahara`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_admin_finalisasi`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `pencairan_dana` VALUES
  (2, 1, 4, 4, '5.000.000', '2025-08-02', '2025-08-04', 1, 'selesai');

-- =================================================================
-- 1️⃣2️⃣ EVALUASI PERJALANAN
-- =================================================================
CREATE TABLE IF NOT EXISTS `evaluasi_perjalanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_pegawai` int NOT NULL,
  `status` enum('draft','diajukan','disetujui','ditolak','selesai') DEFAULT 'draft',
  `kendala` text,
  `hasil` text,
  `saran` text,
  `lampiran` varchar(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `evaluasi_perjalanan` VALUES
  (3, 1, 1, 'selesai', 'Terjadi keterlambatan transportasi akibat cuaca buruk.', 'Kegiatan kunjungan kerja tetap terlaksana meskipun ada penyesuaian jadwal.', 'Melakukan koordinasi transportasi lebih awal dan menyiapkan rencana alternatif.', '1752164749_dummy_evaluasi_perjalanan.pdf');

-- =================================================================
-- 1️⃣3️⃣ NOTIFIKASI
-- =================================================================
CREATE TABLE IF NOT EXISTS `notifikasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `pesan` text,
  `link` varchar(255),
  `is_read` tinyint(1) DEFAULT '0',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notifikasi` VALUES
  (1, 2, 'Pengajuan perjalanan Anda disetujui', '/modules/shared/pengajuan/index.php', 0, '2025-06-25 07:21:21'),
  (2, 2, 'SPT dan SPPD telah diterbitkan', '/modules/shared/spt/index.php', 1, '2025-06-25 07:21:21'),
  (3, 2, 'Dana telah dicairkan oleh bendahara', '/modules/bendahara/pencairan/index.php', 0, '2025-06-25 07:21:21');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
