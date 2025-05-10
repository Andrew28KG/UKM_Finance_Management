-- Create database
CREATE DATABASE IF NOT EXISTS ukm_finance;
USE ukm_finance;

-- Create UKM table
CREATE TABLE IF NOT EXISTS `ukm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_ukm` varchar(100) NOT NULL,
  `deskripsi` text,
  `ketua` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ukm_id` int(11) DEFAULT NULL,
  `role` enum('admin','bendahara','anggota') NOT NULL DEFAULT 'anggota',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `ukm_id` (`ukm_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`ukm_id`) REFERENCES `ukm` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create transaksi table
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ukm_id` int(11) NOT NULL,
  `jenis` enum('pemasukan','pengeluaran') NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` date NOT NULL,
  `keterangan` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ukm_id` (`ukm_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`ukm_id`) REFERENCES `ukm` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data for UKM
INSERT INTO `ukm` (`nama_ukm`, `deskripsi`, `ketua`) VALUES
('UKM Olahraga', 'Unit Kegiatan Mahasiswa bidang Olahraga', 'Budi Santoso'),
('UKM Musik', 'Unit Kegiatan Mahasiswa bidang Musik', 'Dewi Lestari'),
('UKM Fotografi', 'Unit Kegiatan Mahasiswa bidang Fotografi', 'Andi Wijaya'),
('UKM Jurnalistik', 'Unit Kegiatan Mahasiswa bidang Jurnalistik', 'Siti Nuraini'),
('UKM Pecinta Alam', 'Unit Kegiatan Mahasiswa bidang Pecinta Alam', 'Rudi Hermawan');

-- Insert sample users (password: password123)
INSERT INTO `users` (`nama`, `email`, `password`, `ukm_id`, `role`) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin'),
('Budi Santoso', 'budi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'bendahara'),
('Dewi Lestari', 'dewi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'bendahara'),
('Andi Wijaya', 'andi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'bendahara'),
('Siti Nuraini', 'siti@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'bendahara'),
('Rudi Hermawan', 'rudi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 'bendahara');

-- Insert sample transactions
INSERT INTO `transaksi` (`ukm_id`, `jenis`, `kategori`, `jumlah`, `tanggal`, `keterangan`, `created_by`) VALUES
-- UKM Olahraga
(1, 'pemasukan', 'Iuran Anggota', 500000.00, '2023-01-15', 'Iuran anggota bulan Januari', 2),
(1, 'pemasukan', 'Sponsorship', 2000000.00, '2023-02-10', 'Sponsorship dari PT ABC', 2),
(1, 'pengeluaran', 'Peralatan', 750000.00, '2023-02-15', 'Pembelian bola dan net', 2),
(1, 'pengeluaran', 'Konsumsi', 300000.00, '2023-03-05', 'Konsumsi rapat anggota', 2),
(1, 'pemasukan', 'Iuran Anggota', 500000.00, '2023-03-15', 'Iuran anggota bulan Maret', 2),
(1, 'pengeluaran', 'Transportasi', 450000.00, '2023-04-20', 'Transportasi pertandingan', 2),

-- UKM Musik
(2, 'pemasukan', 'Iuran Anggota', 600000.00, '2023-01-10', 'Iuran anggota bulan Januari', 3),
(2, 'pengeluaran', 'Peralatan', 1500000.00, '2023-01-25', 'Pembelian senar gitar dan stik drum', 3),
(2, 'pemasukan', 'Pentas Seni', 3000000.00, '2023-02-28', 'Hasil pentas seni kampus', 3),
(2, 'pengeluaran', 'Konsumsi', 500000.00, '2023-03-10', 'Konsumsi latihan rutin', 3),
(2, 'pemasukan', 'Iuran Anggota', 600000.00, '2023-04-10', 'Iuran anggota bulan April', 3),
(2, 'pengeluaran', 'Sewa Studio', 800000.00, '2023-04-15', 'Sewa studio untuk rekaman', 3),

-- UKM Fotografi
(3, 'pemasukan', 'Iuran Anggota', 450000.00, '2023-01-05', 'Iuran anggota bulan Januari', 4),
(3, 'pengeluaran', 'Peralatan', 2500000.00, '2023-01-20', 'Pembelian lensa kamera', 4),
(3, 'pemasukan', 'Proyek Foto', 1500000.00, '2023-02-15', 'Proyek foto wisuda', 4),
(3, 'pengeluaran', 'Workshop', 1000000.00, '2023-03-01', 'Workshop fotografi dasar', 4),
(3, 'pemasukan', 'Iuran Anggota', 450000.00, '2023-04-05', 'Iuran anggota bulan April', 4),
(3, 'pengeluaran', 'Pameran', 1200000.00, '2023-04-25', 'Pameran foto tahunan', 4),

-- UKM Jurnalistik
(4, 'pemasukan', 'Iuran Anggota', 400000.00, '2023-01-10', 'Iuran anggota bulan Januari', 5),
(4, 'pengeluaran', 'Percetakan', 1800000.00, '2023-01-30', 'Cetak majalah kampus', 5),
(4, 'pemasukan', 'Penjualan Majalah', 900000.00, '2023-02-20', 'Penjualan majalah edisi Februari', 5),
(4, 'pengeluaran', 'Seminar', 1500000.00, '2023-03-15', 'Seminar jurnalistik', 5),
(4, 'pemasukan', 'Iuran Anggota', 400000.00, '2023-04-10', 'Iuran anggota bulan April', 5),
(4, 'pengeluaran', 'Peralatan', 700000.00, '2023-04-20', 'Pembelian recorder dan alat tulis', 5),

-- UKM Pecinta Alam
(5, 'pemasukan', 'Iuran Anggota', 550000.00, '2023-01-15', 'Iuran anggota bulan Januari', 6),
(5, 'pengeluaran', 'Peralatan', 3000000.00, '2023-01-25', 'Pembelian tenda dan peralatan camping', 6),
(5, 'pemasukan', 'Sponsorship', 2500000.00, '2023-02-10', 'Sponsorship dari toko outdoor', 6),
(5, 'pengeluaran', 'Transportasi', 1200000.00, '2023-03-05', 'Transportasi pendakian gunung', 6),
(5, 'pemasukan', 'Iuran Anggota', 550000.00, '2023-04-15', 'Iuran anggota bulan April', 6),
(5, 'pengeluaran', 'Konsumsi', 900000.00, '2023-04-25', 'Konsumsi kegiatan camping', 6); 