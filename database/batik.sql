-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 04, 2026 at 04:47 PM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `batik_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2b$10$0KdJv6.MEfO3wLzjPeJ67ud0HzTaAs1XB9NyceeV3FP6WOTNFVeya', '2026-07-01 14:15:03');

-- --------------------------------------------------------

--
-- Table structure for table `batik`
--

CREATE TABLE `batik` (
  `id` int(11) NOT NULL,
  `nama_batik` varchar(100) NOT NULL,
  `ukuran` enum('S','M','L','XL') NOT NULL,
  `warna` varchar(50) NOT NULL,
  `harga_per_hari` decimal(10,2) NOT NULL,
  `status` enum('Tersedia','Sedang Dipinjam') NOT NULL DEFAULT 'Tersedia',
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batik`
--

INSERT INTO `batik` (`id`, `nama_batik`, `ukuran`, `warna`, `harga_per_hari`, `status`, `gambar`, `deskripsi`, `created_at`) VALUES
(1, 'Batik Encim Elegan', 'M', 'Merah Marun', 80000.00, 'Tersedia', 'default.jpg', 'Batik encim klasik dengan motif bunga, cocok untuk acara formal.', '2026-07-01 14:15:03'),
(2, 'Batik Wisuda Modern', 'M', 'Soft Pink', 80000.00, 'Tersedia', 'default.jpg', 'Batik modern dengan potongan elegan, cocok untuk wisuda.', '2026-07-01 14:15:03'),
(3, 'Batik Kombinasi Kebaya', 'L', 'Merah Bata', 100000.00, 'Tersedia', 'default.jpg', 'Perpaduan batik dan kebaya modern untuk acara resmi.', '2026-07-01 14:15:03'),
(4, 'Batik Prada Mewah', 'S', 'Pink Fuchsia', 120000.00, 'Tersedia', 'default.jpg', 'Batik prada dengan sentuhan emas, tampil mewah dan anggun.', '2026-07-01 14:15:03'),
(5, 'Batik Casual Harian', 'M', 'Salem', 60000.00, 'Tersedia', 'default.jpg', 'Batik santai untuk pemakaian sehari-hari yang tetap stylish.', '2026-07-01 14:15:03'),
(6, 'Batik Pesta Eksklusif', 'L', 'Merah Maroon', 150000.00, 'Tersedia', 'default.jpg', 'Batik mewah untuk acara pesta dan gala dinner.', '2026-07-01 14:15:03');

-- --------------------------------------------------------

--
-- Table structure for table `penyewaan`
--

CREATE TABLE `penyewaan` (
  `id` int(11) NOT NULL,
  `nama_penyewa` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `batik_id` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `jumlah_hari` int(11) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status_sewa` enum('Berlangsung','Selesai') NOT NULL DEFAULT 'Berlangsung',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `penyewaan`
--

INSERT INTO `penyewaan` (`id`, `nama_penyewa`, `no_hp`, `batik_id`, `tanggal_pinjam`, `tanggal_kembali`, `jumlah_hari`, `total_harga`, `status_sewa`, `created_at`) VALUES
(1, 'ayu', '', 5, '2026-07-01', '2026-07-02', 0, 60000.00, 'Selesai', '2026-07-01 15:32:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `batik`
--
ALTER TABLE `batik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batik_id` (`batik_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `batik`
--
ALTER TABLE `batik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penyewaan`
--
ALTER TABLE `penyewaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD CONSTRAINT `penyewaan_ibfk_1` FOREIGN KEY (`batik_id`) REFERENCES `batik` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
