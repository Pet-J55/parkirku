-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 21, 2026 at 03:10 PM
-- Server version: 5.7.44-log
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_parkir_kampus`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_kendaraan_keluar` (IN `p_id_transaksi` INT, IN `p_id_user` INT)   BEGIN
    DECLARE waktu_masuk_data DATETIME;
    DECLARE durasi_data INT;

    SELECT waktu_masuk INTO waktu_masuk_data
    FROM transaksi_parkir
    WHERE id_transaksi = p_id_transaksi
    AND status_transaksi = 'Aktif';

    IF waktu_masuk_data IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transaksi aktif tidak ditemukan';
    END IF;

    SET durasi_data = TIMESTAMPDIFF(MINUTE, waktu_masuk_data, NOW());

    UPDATE transaksi_parkir
    SET 
        waktu_keluar = NOW(),
        durasi_menit = durasi_data,
        status_transaksi = 'Selesai'
    WHERE id_transaksi = p_id_transaksi;

    INSERT INTO log_aktivitas (
        id_user,
        aktivitas,
        keterangan
    ) VALUES (
        p_id_user,
        'Kendaraan Keluar',
        CONCAT('Transaksi ID ', p_id_transaksi, ' selesai. Durasi parkir ', durasi_data, ' menit')
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_kendaraan_masuk` (IN `p_id_kendaraan` INT, IN `p_id_slot` INT, IN `p_id_user` INT)   BEGIN
    DECLARE jumlah_aktif INT DEFAULT 0;
    DECLARE status_slot_sekarang VARCHAR(20);

    SELECT COUNT(*) INTO jumlah_aktif
    FROM transaksi_parkir
    WHERE id_kendaraan = p_id_kendaraan
    AND status_transaksi = 'Aktif';

    IF jumlah_aktif > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Kendaraan ini masih memiliki transaksi aktif';
    END IF;

    SELECT status_slot INTO status_slot_sekarang
    FROM slot_parkir
    WHERE id_slot = p_id_slot;

    IF status_slot_sekarang != 'Kosong' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Slot parkir tidak tersedia';
    END IF;

    INSERT INTO transaksi_parkir (
        id_kendaraan,
        id_slot,
        waktu_masuk,
        status_transaksi
    ) VALUES (
        p_id_kendaraan,
        p_id_slot,
        NOW(),
        'Aktif'
    );

    INSERT INTO log_aktivitas (
        id_user,
        aktivitas,
        keterangan
    ) VALUES (
        p_id_user,
        'Kendaraan Masuk',
        CONCAT('Kendaraan ID ', p_id_kendaraan, ' masuk ke slot ID ', p_id_slot)
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_rekap_slot_per_area` ()   BEGIN
    DECLARE selesai INT DEFAULT 0;
    DECLARE v_id_area INT;
    DECLARE v_nama_area VARCHAR(100);

    DECLARE cur_area CURSOR FOR
        SELECT id_area, nama_area 
        FROM area_parkir
        ORDER BY nama_area ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET selesai = 1;

    CREATE TEMPORARY TABLE IF NOT EXISTS temp_rekap_slot (
        nama_area VARCHAR(100),
        total_slot INT,
        slot_kosong INT,
        slot_terisi INT,
        slot_rusak INT,
        slot_tidak_aktif INT
    );

    TRUNCATE TABLE temp_rekap_slot;

    OPEN cur_area;

    ulang_area: LOOP
        FETCH cur_area INTO v_id_area, v_nama_area;

        IF selesai = 1 THEN
            LEAVE ulang_area;
        END IF;

        INSERT INTO temp_rekap_slot (
            nama_area,
            total_slot,
            slot_kosong,
            slot_terisi,
            slot_rusak,
            slot_tidak_aktif
        )
        SELECT
            v_nama_area,
            COUNT(*),
            SUM(CASE WHEN status_slot = 'Kosong' THEN 1 ELSE 0 END),
            SUM(CASE WHEN status_slot = 'Terisi' THEN 1 ELSE 0 END),
            SUM(CASE WHEN status_slot = 'Rusak' THEN 1 ELSE 0 END),
            SUM(CASE WHEN status_slot = 'Tidak Aktif' THEN 1 ELSE 0 END)
        FROM slot_parkir
        WHERE id_area = v_id_area;
    END LOOP;

    CLOSE cur_area;

    SELECT * FROM temp_rekap_slot;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `area_parkir`
--

CREATE TABLE `area_parkir` (
  `id_area` int(11) NOT NULL,
  `nama_area` varchar(100) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `keterangan` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `area_parkir`
--

INSERT INTO `area_parkir` (`id_area`, `nama_area`, `lokasi`, `keterangan`, `created_at`) VALUES
(1, 'Parkir Gedung A', 'Dekat Gedung A', 'Area parkir utama mahasiswa', '2026-06-14 19:25:53'),
(2, 'Parkir Fakultas Teknik', 'Samping Laboratorium Teknik', 'Area parkir Fakultas Teknik', '2026-06-14 19:25:53'),
(3, 'Parkir Rektorat', 'Depan Gedung Rektorat', 'Area parkir dosen dan staff', '2026-06-14 19:25:53'),
(4, 'Parkir Tamu', 'Dekat Pos Satpam', 'Area khusus tamu kampus', '2026-06-14 19:25:53'),
(5, 'Parkir Prodi PGSD', 'Depan RKBB', 'Area Parkir Khusus PGSD', '2026-06-14 23:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan`
--

CREATE TABLE `kendaraan` (
  `id_kendaraan` int(11) NOT NULL,
  `nomor_plat` varchar(20) NOT NULL,
  `jenis_kendaraan` enum('Motor','Mobil') NOT NULL,
  `nama_pemilik` varchar(100) NOT NULL,
  `kategori_pengguna` enum('Mahasiswa','Dosen','Staff','Tamu') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `kendaraan`
--

INSERT INTO `kendaraan` (`id_kendaraan`, `nomor_plat`, `jenis_kendaraan`, `nama_pemilik`, `kategori_pengguna`, `created_at`) VALUES
(1, 'B 1234 ABC', 'Motor', 'Andi', 'Mahasiswa', '2026-06-14 19:25:54'),
(2, 'L 5678 DEF', 'Mobil', 'Budi', 'Dosen', '2026-06-14 19:25:54'),
(3, 'M 9876 XYZ', 'Motor', 'Sinta', 'Staff', '2026-06-14 19:25:54'),
(4, 'L 2065 CAS', 'Motor', 'jihad', 'Mahasiswa', '2026-06-14 21:10:15'),
(5, 'L 2064 CAS', 'Motor', 'jun', 'Mahasiswa', '2026-06-14 21:10:47'),
(6, 'L 2001 AB', 'Motor', 'SAFRI', 'Mahasiswa', '2026-06-14 23:34:14'),
(8, 'L 2045 CAS', 'Mobil', 'juni', 'Dosen', '2026-06-14 23:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `keterangan` text,
  `waktu_log` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `id_user`, `aktivitas`, `keterangan`, `waktu_log`) VALUES
(2, 2, 'Kendaraan Masuk', 'Kendaraan ID 1 masuk ke slot ID 1', '2026-06-14 21:08:10'),
(3, 2, 'Kendaraan Keluar', 'Transaksi ID 2 selesai. Durasi parkir 0 menit', '2026-06-14 21:08:17'),
(4, 1, 'Kendaraan Masuk', 'Kendaraan ID 1 masuk ke slot ID 1', '2026-06-14 23:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `slot_parkir`
--

CREATE TABLE `slot_parkir` (
  `id_slot` int(11) NOT NULL,
  `id_area` int(11) NOT NULL,
  `kode_slot` varchar(20) NOT NULL,
  `status_slot` enum('Kosong','Terisi','Tidak Aktif') DEFAULT 'Kosong',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `slot_parkir`
--

INSERT INTO `slot_parkir` (`id_slot`, `id_area`, `kode_slot`, `status_slot`, `created_at`) VALUES
(1, 1, 'A-01', 'Terisi', '2026-06-14 19:25:54'),
(2, 1, 'A-02', 'Kosong', '2026-06-14 19:25:54'),
(3, 1, 'A-03', 'Kosong', '2026-06-14 19:25:54'),
(4, 1, 'A-04', 'Kosong', '2026-06-14 19:25:54'),
(5, 2, 'FT-01', 'Kosong', '2026-06-14 19:25:54'),
(6, 2, 'FT-02', 'Kosong', '2026-06-14 19:25:54'),
(7, 2, 'FT-03', 'Kosong', '2026-06-14 19:25:54'),
(8, 3, 'R-01', 'Kosong', '2026-06-14 19:25:54'),
(9, 3, 'R-02', 'Kosong', '2026-06-14 19:25:54'),
(10, 4, 'T-01', 'Kosong', '2026-06-14 19:25:54'),
(11, 4, 'T-02', 'Kosong', '2026-06-14 19:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_parkir`
--

CREATE TABLE `transaksi_parkir` (
  `id_transaksi` int(11) NOT NULL,
  `id_kendaraan` int(11) NOT NULL,
  `id_slot` int(11) NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_keluar` datetime DEFAULT NULL,
  `durasi_menit` int(11) DEFAULT '0',
  `status_transaksi` enum('Aktif','Selesai') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transaksi_parkir`
--

INSERT INTO `transaksi_parkir` (`id_transaksi`, `id_kendaraan`, `id_slot`, `waktu_masuk`, `waktu_keluar`, `durasi_menit`, `status_transaksi`, `created_at`) VALUES
(2, 1, 1, '2026-06-15 04:08:10', '2026-06-15 04:08:17', 0, 'Selesai', '2026-06-14 21:08:10'),
(3, 1, 1, '2026-06-15 06:38:04', NULL, 0, 'Aktif', '2026-06-14 23:38:04');

--
-- Triggers `transaksi_parkir`
--
DELIMITER $$
CREATE TRIGGER `trg_slot_kosong` AFTER UPDATE ON `transaksi_parkir` FOR EACH ROW BEGIN
    IF OLD.status_transaksi = 'Aktif' AND NEW.status_transaksi = 'Selesai' THEN
        UPDATE slot_parkir
        SET status_slot = 'Kosong'
        WHERE id_slot = NEW.id_slot;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_slot_terisi` AFTER INSERT ON `transaksi_parkir` FOR EACH ROW BEGIN
    IF NEW.status_transaksi = 'Aktif' THEN
        UPDATE slot_parkir
        SET status_slot = 'Terisi'
        WHERE id_slot = NEW.id_slot;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','petugas','pimpinan') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', '2026-06-14 17:23:32'),
(2, 'Petugas Parkir', 'petugas', '570c396b3fc856eceb8aa7357f32af1a', 'petugas', '2026-06-14 17:23:32'),
(3, 'Pimpinan Kampus', 'pimpinan', '7d3207a13dc221ac13c2f3dac3011f50', 'pimpinan', '2026-06-14 17:23:32');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_kendaraan_aktif`
-- (See below for the actual view)
--
CREATE TABLE `view_kendaraan_aktif` (
`id_transaksi` int(11)
,`id_kendaraan` int(11)
,`nomor_plat` varchar(20)
,`jenis_kendaraan` enum('Motor','Mobil')
,`nama_pemilik` varchar(100)
,`kategori_pengguna` enum('Mahasiswa','Dosen','Staff','Tamu')
,`nama_area` varchar(100)
,`lokasi` varchar(150)
,`id_slot` int(11)
,`kode_slot` varchar(20)
,`waktu_masuk` datetime
,`status_transaksi` enum('Aktif','Selesai')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_parkir`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_parkir` (
`id_transaksi` int(11)
,`tanggal` date
,`nomor_plat` varchar(20)
,`jenis_kendaraan` enum('Motor','Mobil')
,`nama_pemilik` varchar(100)
,`kategori_pengguna` enum('Mahasiswa','Dosen','Staff','Tamu')
,`nama_area` varchar(100)
,`kode_slot` varchar(20)
,`waktu_masuk` datetime
,`waktu_keluar` datetime
,`durasi_menit` int(11)
,`status_transaksi` enum('Aktif','Selesai')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_slot_kosong`
-- (See below for the actual view)
--
CREATE TABLE `view_slot_kosong` (
`id_slot` int(11)
,`kode_slot` varchar(20)
,`nama_area` varchar(100)
,`lokasi` varchar(150)
,`status_slot` enum('Kosong','Terisi','Tidak Aktif')
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `area_parkir`
--
ALTER TABLE `area_parkir`
  ADD PRIMARY KEY (`id_area`);

--
-- Indexes for table `kendaraan`
--
ALTER TABLE `kendaraan`
  ADD PRIMARY KEY (`id_kendaraan`),
  ADD UNIQUE KEY `nomor_plat` (`nomor_plat`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `slot_parkir`
--
ALTER TABLE `slot_parkir`
  ADD PRIMARY KEY (`id_slot`),
  ADD UNIQUE KEY `kode_slot` (`kode_slot`),
  ADD KEY `id_area` (`id_area`);

--
-- Indexes for table `transaksi_parkir`
--
ALTER TABLE `transaksi_parkir`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kendaraan` (`id_kendaraan`),
  ADD KEY `id_slot` (`id_slot`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `area_parkir`
--
ALTER TABLE `area_parkir`
  MODIFY `id_area` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kendaraan`
--
ALTER TABLE `kendaraan`
  MODIFY `id_kendaraan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `slot_parkir`
--
ALTER TABLE `slot_parkir`
  MODIFY `id_slot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transaksi_parkir`
--
ALTER TABLE `transaksi_parkir`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- --------------------------------------------------------

--
-- Structure for view `view_kendaraan_aktif`
--
DROP TABLE IF EXISTS `view_kendaraan_aktif`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_kendaraan_aktif`  AS SELECT `tp`.`id_transaksi` AS `id_transaksi`, `k`.`id_kendaraan` AS `id_kendaraan`, `k`.`nomor_plat` AS `nomor_plat`, `k`.`jenis_kendaraan` AS `jenis_kendaraan`, `k`.`nama_pemilik` AS `nama_pemilik`, `k`.`kategori_pengguna` AS `kategori_pengguna`, `ap`.`nama_area` AS `nama_area`, `ap`.`lokasi` AS `lokasi`, `sp`.`id_slot` AS `id_slot`, `sp`.`kode_slot` AS `kode_slot`, `tp`.`waktu_masuk` AS `waktu_masuk`, `tp`.`status_transaksi` AS `status_transaksi` FROM (((`transaksi_parkir` `tp` join `kendaraan` `k` on((`tp`.`id_kendaraan` = `k`.`id_kendaraan`))) join `slot_parkir` `sp` on((`tp`.`id_slot` = `sp`.`id_slot`))) join `area_parkir` `ap` on((`sp`.`id_area` = `ap`.`id_area`))) WHERE (`tp`.`status_transaksi` = 'Aktif') ;

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_parkir`
--
DROP TABLE IF EXISTS `view_laporan_parkir`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_parkir`  AS SELECT `tp`.`id_transaksi` AS `id_transaksi`, cast(`tp`.`waktu_masuk` as date) AS `tanggal`, `k`.`nomor_plat` AS `nomor_plat`, `k`.`jenis_kendaraan` AS `jenis_kendaraan`, `k`.`nama_pemilik` AS `nama_pemilik`, `k`.`kategori_pengguna` AS `kategori_pengguna`, `ap`.`nama_area` AS `nama_area`, `sp`.`kode_slot` AS `kode_slot`, `tp`.`waktu_masuk` AS `waktu_masuk`, `tp`.`waktu_keluar` AS `waktu_keluar`, `tp`.`durasi_menit` AS `durasi_menit`, `tp`.`status_transaksi` AS `status_transaksi` FROM (((`transaksi_parkir` `tp` join `kendaraan` `k` on((`tp`.`id_kendaraan` = `k`.`id_kendaraan`))) join `slot_parkir` `sp` on((`tp`.`id_slot` = `sp`.`id_slot`))) join `area_parkir` `ap` on((`sp`.`id_area` = `ap`.`id_area`))) ;

-- --------------------------------------------------------

--
-- Structure for view `view_slot_kosong`
--
DROP TABLE IF EXISTS `view_slot_kosong`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_slot_kosong`  AS SELECT `sp`.`id_slot` AS `id_slot`, `sp`.`kode_slot` AS `kode_slot`, `ap`.`nama_area` AS `nama_area`, `ap`.`lokasi` AS `lokasi`, `sp`.`status_slot` AS `status_slot` FROM (`slot_parkir` `sp` join `area_parkir` `ap` on((`sp`.`id_area` = `ap`.`id_area`))) WHERE (`sp`.`status_slot` = 'Kosong') ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `slot_parkir`
--
ALTER TABLE `slot_parkir`
  ADD CONSTRAINT `slot_parkir_ibfk_1` FOREIGN KEY (`id_area`) REFERENCES `area_parkir` (`id_area`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_parkir`
--
ALTER TABLE `transaksi_parkir`
  ADD CONSTRAINT `transaksi_parkir_ibfk_1` FOREIGN KEY (`id_kendaraan`) REFERENCES `kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_parkir_ibfk_2` FOREIGN KEY (`id_slot`) REFERENCES `slot_parkir` (`id_slot`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
