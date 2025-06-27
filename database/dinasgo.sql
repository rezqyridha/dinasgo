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


-- Dumping database structure for dinasgo
CREATE DATABASE IF NOT EXISTS `dinasgo` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `dinasgo`;

-- Dumping structure for table dinasgo.dokumen
CREATE TABLE IF NOT EXISTS `dokumen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_user` int NOT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `jenis` enum('surat_tugas','bukti_biaya','lainnya') DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `dokumen_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `dokumen_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.dokumen: ~3 rows (approximately)
INSERT INTO `dokumen` (`id`, `id_pengajuan`, `id_user`, `nama_file`, `jenis`, `uploaded_at`) VALUES
	(1, 1, 1, 'spt_001_yogyakarta.pdf', 'surat_tugas', '2025-06-25 07:21:21'),
	(2, 2, 1, 'bukti_kereta.pdf', 'bukti_biaya', '2025-06-25 07:21:21'),
	(3, 2, 1, 'memo_internal.pdf', 'lainnya', '2025-06-25 07:21:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.dokumentasi_kegiatan: ~3 rows (approximately)
INSERT INTO `dokumentasi_kegiatan` (`id`, `id_pengajuan`, `jenis_dokumentasi`, `nama_file`, `keterangan`, `uploaded_at`) VALUES
	(1, 2, 'foto', 'surabaya_site.jpg', 'Lokasi proyek air minum', '2025-06-25 07:21:21'),
	(2, 4, 'laporan', 'laporan_dinasgo.pdf', 'Laporan kegiatan DinasGo', '2025-06-25 07:21:21'),
	(3, 2, 'video', 'video_monitoring.mp4', 'Dokumentasi kunjungan atasan', '2025-06-25 07:21:21');

-- Dumping structure for table dinasgo.evaluasi_perjalanan
CREATE TABLE IF NOT EXISTS `evaluasi_perjalanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_pegawai` int NOT NULL,
  `kendala` text,
  `hasil` text,
  `saran` text,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_pegawai` (`id_pegawai`),
  CONSTRAINT `evaluasi_perjalanan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `evaluasi_perjalanan_ibfk_2` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.evaluasi_perjalanan: ~3 rows (approximately)
INSERT INTO `evaluasi_perjalanan` (`id`, `id_pengajuan`, `id_pegawai`, `kendala`, `hasil`, `saran`) VALUES
	(1, 1, 1, 'Cuaca buruk sempat mengganggu', 'Semua titik survei tercapai', 'Siapkan antisipasi cuaca'),
	(2, 2, 2, 'Perangkat presentasi kurang maksimal', 'Laporan tersampaikan', 'Perlu alat baru'),
	(3, 4, 2, 'Peserta kurang aktif', 'Acara tetap berjalan baik', 'Libatkan lebih banyak SKPD');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.kepala: ~0 rows (approximately)
INSERT INTO `kepala` (`id`, `nama`, `nip`, `jabatan`, `ttd_gambar`, `tahun`, `created_at`) VALUES
	(1, 'Dr. Ir. Sutrisno, MT', '196504011993031001', 'Kepala Balai Wilayah Sungai', 'ttd_sutrisno.png', '2025', '2025-06-25 07:21:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.notifikasi: ~3 rows (approximately)
INSERT INTO `notifikasi` (`id`, `id_user`, `pesan`, `link`, `is_read`, `dibuat_pada`) VALUES
	(1, 2, 'Pengajuan perjalanan Anda disetujui', '/modules/shared/pengajuan/index.php', 0, '2025-06-25 07:21:21'),
	(2, 2, 'SPT dan SPPD telah diterbitkan', '/modules/shared/spt/index.php', 1, '2025-06-25 07:21:21'),
	(3, 2, 'Dana telah dicairkan oleh bendahara', '/modules/bendahara/pencairan/index.php', 0, '2025-06-25 07:21:21');

-- Dumping structure for table dinasgo.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `pegawai_ibfk_1` (`id_user`),
  CONSTRAINT `pegawai_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.pegawai: ~3 rows (approximately)
INSERT INTO `pegawai` (`id`, `id_user`, `nip`, `nama`, `jabatan`, `no_hp`, `email`, `alamat`) VALUES
	(1, 2, '197812312001011001', 'Rina Ayu', 'Pegawai', '081234567890', 'rina@dinas.go.id', 'Jl. Merdeka No. 1'),
	(2, 4, '198006152002121002', 'Fajar Santoso', 'Kasubag Keuangan', '081298889999', 'fajar@dinas.go.id', 'Jl. Mataram No. 3');

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
	(1, 1, 4, 3100000.00, '2025-07-07'),
	(2, 2, 4, 2800000.00, '2025-07-30');

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
  `diverifikasi_oleh` int DEFAULT NULL,
  `catatan_verifikasi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `fk_diverifikasi_user` (`diverifikasi_oleh`),
  CONSTRAINT `fk_diverifikasi_user` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pengajuan_perjalanan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.pengajuan_perjalanan: ~5 rows (approximately)
INSERT INTO `pengajuan_perjalanan` (`id`, `id_pegawai`, `tujuan`, `tanggal_berangkat`, `tanggal_kembali`, `keperluan`, `estimasi_biaya`, `status`, `diverifikasi_oleh`, `catatan_verifikasi`, `created_at`) VALUES
	(1, 1, 'Yogyakarta', '2025-07-10', '2025-07-12', 'Kunjungan lapangan ke proyek sungai', 2500000.00, 'diajukan', NULL, NULL, '2025-06-25 07:21:21'),
	(2, 2, 'Surabaya', '2025-08-01', '2025-08-04', 'Monitoring dan pelaporan realisasi anggaran', 3100000.00, 'disetujui', NULL, NULL, '2025-06-25 07:21:21'),
	(3, 1, 'Semarang', '2025-09-01', '2025-09-02', 'Diskusi Raperda daerah irigasi', 1200000.00, 'ditolak', NULL, NULL, '2025-06-25 07:21:21'),
	(4, 2, 'Balikpapan', '2025-10-05', '2025-10-07', 'Sosialisasi aplikasi DinasGo', 2800000.00, 'selesai', NULL, NULL, '2025-06-25 07:21:21'),
	(6, 1, 'Banjarmasin', '2025-06-30', '2025-07-03', 'Sosialisasi aplikasi Dinasgo Tes ubah', 1500000.00, 'disetujui', 1, NULL, '2025-06-25 15:57:18');

-- Dumping structure for table dinasgo.persetujuan
CREATE TABLE IF NOT EXISTS `persetujuan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `id_verifikator` int DEFAULT NULL,
  `catatan` text,
  `status` enum('disetujui','ditolak') NOT NULL,
  `tanggal_persetujuan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `id_atasan` (`id_verifikator`),
  CONSTRAINT `persetujuan_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `persetujuan_ibfk_2` FOREIGN KEY (`id_verifikator`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.persetujuan: ~4 rows (approximately)
INSERT INTO `persetujuan` (`id`, `id_pengajuan`, `id_verifikator`, `catatan`, `status`, `tanggal_persetujuan`) VALUES
	(1, 1, 3, 'Disetujui karena penting', 'disetujui', '2025-07-01'),
	(2, 2, 3, 'Lanjutkan segera', 'disetujui', '2025-07-25'),
	(3, 3, 3, 'Tidak relevan dengan target dinas', 'ditolak', '2025-08-20'),
	(4, 6, 1, 'test verif admin', 'disetujui', '2025-06-26');

-- Dumping structure for table dinasgo.rincian_biaya
CREATE TABLE IF NOT EXISTS `rincian_biaya` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_rincian` varchar(100) NOT NULL,
  `tanggal_rincian` date NOT NULL,
  `jumlah_total` decimal(15,2) NOT NULL,
  `status` enum('draft','diajukan','disetujui','ditolak','selesai') DEFAULT 'draft',
  `dibuat_oleh` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_rincian` (`nomor_rincian`),
  KEY `id_pengajuan` (`id_pengajuan`),
  KEY `dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `rincian_biaya_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rincian_biaya_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.rincian_biaya: ~1 rows (approximately)
INSERT INTO `rincian_biaya` (`id`, `id_pengajuan`, `nomor_rincian`, `tanggal_rincian`, `jumlah_total`, `status`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
	(3, 6, 'RB-001/2025', '2025-06-01', 1750000.00, 'diajukan', 1, '2025-06-27 09:35:42', '2025-06-27 09:48:04');

-- Dumping structure for table dinasgo.rincian_biaya_detail
CREATE TABLE IF NOT EXISTS `rincian_biaya_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rincian` int NOT NULL,
  `jenis_biaya` varchar(100) NOT NULL,
  `keterangan` text,
  `jumlah` int NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `total` decimal(15,2) GENERATED ALWAYS AS ((`jumlah` * `harga_satuan`)) STORED,
  PRIMARY KEY (`id`),
  KEY `id_rincian` (`id_rincian`),
  CONSTRAINT `rincian_biaya_detail_ibfk_1` FOREIGN KEY (`id_rincian`) REFERENCES `rincian_biaya` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.rincian_biaya_detail: ~2 rows (approximately)
INSERT INTO `rincian_biaya_detail` (`id`, `id_rincian`, `jenis_biaya`, `keterangan`, `jumlah`, `satuan`, `harga_satuan`) VALUES
	(6, 3, 'uang harian', '2 hari', 2, 'hari', 250000.00),
	(7, 3, 'Transportasi', 'PP Banjarmasin', 1, 'TIket', 1250000.00);

-- Dumping structure for table dinasgo.sppd
CREATE TABLE IF NOT EXISTS `sppd` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_sppd` varchar(50) DEFAULT NULL,
  `tanggal_terbit` date DEFAULT NULL,
  `catatan` text,
  PRIMARY KEY (`id`),
  KEY `id_pengajuan` (`id_pengajuan`),
  CONSTRAINT `sppd_ibfk_1` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.sppd: ~3 rows (approximately)
INSERT INTO `sppd` (`id`, `id_pengajuan`, `nomor_sppd`, `tanggal_terbit`, `catatan`) VALUES
	(1, 1, 'SPPD-001/2025', '2025-07-05', 'Dokumen dikeluarkan sesuai SPT-001'),
	(2, 2, 'SPPD-002/2025', '2025-07-26', 'Monitoring lapangan proyek IPA'),
	(3, 4, 'SPPD-003/2025', '2025-10-01', 'Kegiatan akhir tahun');

-- Dumping structure for table dinasgo.spt
CREATE TABLE IF NOT EXISTS `spt` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int NOT NULL,
  `nomor_spt` varchar(100) NOT NULL,
  `tanggal_spt` date NOT NULL,
  `maksud_perjalanan` text NOT NULL,
  `lama_perjalanan` varchar(50) NOT NULL,
  `transportasi` varchar(100) NOT NULL,
  `status` enum('draft','ditandatangani','dibatalkan') DEFAULT 'draft',
  `ditandatangani_oleh` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_spt` (`nomor_spt`),
  KEY `fk_spt_pengajuan` (`id_pengajuan`),
  KEY `fk_spt_user` (`ditandatangani_oleh`),
  CONSTRAINT `fk_spt_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_perjalanan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_spt_user` FOREIGN KEY (`ditandatangani_oleh`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.spt: ~3 rows (approximately)
INSERT INTO `spt` (`id`, `id_pengajuan`, `nomor_spt`, `tanggal_spt`, `maksud_perjalanan`, `lama_perjalanan`, `transportasi`, `status`, `ditandatangani_oleh`, `created_at`, `updated_at`) VALUES
	(1, 1, 'SPT-001/2025', '2025-07-05', 'Kunjungan lapangan ke proyek sungai', '3 hari', 'Pesawat Terbang', 'draft', 3, '2025-06-25 15:21:21', '2025-06-26 15:33:28'),
	(2, 2, 'SPT-002/2025', '2025-07-26', 'Monitoring dan pelaporan', '4 hari', 'Kereta', 'ditandatangani', 3, '2025-06-25 15:21:21', '2025-06-25 15:21:21'),
	(3, 3, 'SPT-003/2025', '2025-08-28', 'Diskusi internal', '2 hari', 'Mobil Dinas', 'dibatalkan', 3, '2025-06-25 15:21:21', '2025-06-25 15:21:21');

-- Dumping structure for table dinasgo.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pegawai','atasan','bendahara') NOT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table dinasgo.user: ~5 rows (approximately)
INSERT INTO `user` (`id`, `nama`, `username`, `password`, `role`, `status`) VALUES
	(1, 'Admin Utama', 'admin01', 'admin123', 'admin', 'aktif'),
	(2, 'Rina Ayu', 'pegawai01', 'user123', 'pegawai', 'aktif'),
	(3, 'Andi Atasan', 'atasan01', 'atasan123', 'atasan', 'aktif'),
	(4, 'Fajar Santoso', 'bendahara01', 'bendahara123', 'bendahara', 'aktif'),
	(5, 'Bahri', 'bahri123', 'test123', 'pegawai', 'nonaktif'),
	(7, 'test1', 'tes', 'test123', 'pegawai', 'nonaktif');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
