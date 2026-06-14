<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";

function ambilTotal($koneksi, $query) {
    $hasil = mysqli_query($koneksi, $query);

    if (!$hasil) {
        return 0;
    }

    $data = mysqli_fetch_assoc($hasil);
    return $data['total'];
}

$total_area = ambilTotal($koneksi, "SELECT COUNT(*) AS total FROM area_parkir");
$total_slot = ambilTotal($koneksi, "SELECT COUNT(*) AS total FROM slot_parkir");
$total_kendaraan = ambilTotal($koneksi, "SELECT COUNT(*) AS total FROM kendaraan");

$slot_kosong = ambilTotal($koneksi, "
    SELECT COUNT(*) AS total 
    FROM slot_parkir 
    WHERE status_slot = 'Kosong'
");

$slot_terisi = ambilTotal($koneksi, "
    SELECT COUNT(*) AS total 
    FROM slot_parkir 
    WHERE status_slot = 'Terisi'
");

$kendaraan_aktif = ambilTotal($koneksi, "
    SELECT COUNT(*) AS total 
    FROM transaksi_parkir 
    WHERE status_transaksi = 'Aktif'
");

$kendaraan_hari_ini = ambilTotal($koneksi, "
    SELECT COUNT(*) AS total 
    FROM transaksi_parkir 
    WHERE DATE(waktu_masuk) = CURDATE()
");

$role = $_SESSION['role'];
?>

<div class="content">
    <div class="topbar">
        <div>
            <h2>Dashboard</h2>
            <p>Selamat datang kembali di sistem ParkirKu.</p>
        </div>
        <div class="user-badge">
            <i class="fa-solid fa-user-shield"></i>
            <span><?= $_SESSION['nama']; ?> (<?= ucfirst($_SESSION['role']); ?>)</span>
        </div>
    </div>

    <div class="grid">
        <div class="stat-card">
            <div class="stat-card-info">
                <h3><?= $total_area; ?></h3>
                <p>Total Area Parkir</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <h3><?= $total_slot; ?></h3>
                <p>Total Slot Parkir</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-square-parking"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <h3><?= $total_kendaraan; ?></h3>
                <p>Total Kendaraan</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-car"></i>
            </div>
        </div>

        <div class="stat-card active-cars">
            <div class="stat-card-info">
                <h3><?= $kendaraan_aktif; ?></h3>
                <p>Kendaraan Aktif</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-car-side"></i>
            </div>
        </div>

        <div class="stat-card empty">
            <div class="stat-card-info">
                <h3><?= $slot_kosong; ?></h3>
                <p>Slot Kosong</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="stat-card filled">
            <div class="stat-card-info">
                <h3><?= $slot_terisi; ?></h3>
                <p>Slot Terisi</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-info">
                <h3><?= $kendaraan_hari_ini; ?></h3>
                <p>Kendaraan Masuk Hari Ini</p>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>
    </div>

    <div class="card">
        <?php if ($role == 'admin') { ?>
            <h3><i class="fa-solid fa-user-gear"></i> Dashboard Admin</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.7;">
                Admin memiliki akses penuh untuk mengelola data area parkir,
                slot parkir, kendaraan, proses kendaraan masuk dan keluar,
                laporan, rekap slot, serta log aktivitas.
            </p>
        <?php } elseif ($role == 'petugas') { ?>
            <h3><i class="fa-solid fa-user-shield"></i> Dashboard Petugas</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.7;">
                Petugas dapat mencatat kendaraan masuk, memproses kendaraan keluar,
                dan memantau kendaraan aktif.
            </p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="kendaraan-masuk.php" class="btn">
                    <i class="fa-solid fa-right-to-bracket"></i> Proses Kendaraan Masuk
                </a>
                <a href="kendaraan-keluar.php" class="btn btn-success">
                    <i class="fa-solid fa-right-from-bracket"></i> Proses Kendaraan Keluar
                </a>
            </div>
        <?php } elseif ($role == 'pimpinan') { ?>
            <h3><i class="fa-solid fa-user-tie"></i> Dashboard Pimpinan</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px; line-height: 1.7;">
                Pimpinan dapat melihat laporan parkir, rekap slot,
                dan log aktivitas sistem.
            </p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="laporan.php" class="btn">
                    <i class="fa-solid fa-file-invoice"></i> Lihat Laporan
                </a>
                <a href="rekap-slot.php" class="btn btn-success">
                    <i class="fa-solid fa-chart-simple"></i> Lihat Rekap Slot
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<?php include "layouts/footer.php"; ?>