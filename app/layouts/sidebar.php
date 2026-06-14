<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <h2><i class="fa-solid fa-square-parking"></i> ParkirKu</h2>

    <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-gauge"></i> Dashboard
    </a>

    <?php if ($_SESSION['role'] == 'admin') { ?>
        <a href="area-parkir.php" class="<?= $current_page == 'area-parkir.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-map-location-dot"></i> Data Area Parkir
        </a>
        <a href="slot-parkir.php" class="<?= $current_page == 'slot-parkir.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-square-parking"></i> Data Slot Parkir
        </a>
        <a href="kendaraan.php" class="<?= $current_page == 'kendaraan.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-car"></i> Data Kendaraan
        </a>
        <a href="kendaraan-masuk.php" class="<?= $current_page == 'kendaraan-masuk.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-right-to-bracket"></i> Kendaraan Masuk
        </a>
        <a href="kendaraan-keluar.php" class="<?= $current_page == 'kendaraan-keluar.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-right-from-bracket"></i> Kendaraan Keluar
        </a>
        <a href="kendaraan-aktif.php" class="<?= $current_page == 'kendaraan-aktif.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-car-side"></i> Kendaraan Aktif
        </a>
        <a href="rekap-slot.php" class="<?= $current_page == 'rekap-slot.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-simple"></i> Rekap Slot
        </a>
        <a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-invoice"></i> Laporan Parkir
        </a>
        <a href="log-aktivitas.php" class="<?= $current_page == 'log-aktivitas.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clock-rotate-left"></i> Log Aktivitas
        </a>
    <?php } ?>

    <?php if ($_SESSION['role'] == 'petugas') { ?>
        <a href="kendaraan-masuk.php" class="<?= $current_page == 'kendaraan-masuk.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-right-to-bracket"></i> Kendaraan Masuk
        </a>
        <a href="kendaraan-keluar.php" class="<?= $current_page == 'kendaraan-keluar.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-right-from-bracket"></i> Kendaraan Keluar
        </a>
        <a href="kendaraan-aktif.php" class="<?= $current_page == 'kendaraan-aktif.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-car-side"></i> Kendaraan Aktif
        </a>
    <?php } ?>

    <?php if ($_SESSION['role'] == 'pimpinan') { ?>
        <a href="rekap-slot.php" class="<?= $current_page == 'rekap-slot.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-chart-simple"></i> Rekap Slot
        </a>
        <a href="laporan.php" class="<?= $current_page == 'laporan.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-file-invoice"></i> Laporan Parkir
        </a>
        <a href="log-aktivitas.php" class="<?= $current_page == 'log-aktivitas.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-clock-rotate-left"></i> Log Aktivitas
        </a>
    <?php } ?>

    <a href="auth/logout.php">
        <i class="fa-solid fa-power-off"></i> Logout
    </a>
</div>