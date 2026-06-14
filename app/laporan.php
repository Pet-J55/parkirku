<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'pimpinan']);

$tanggal_awal = "";
$tanggal_akhir = "";

$where = "";

if (isset($_GET['filter'])) {
    $tanggal_awal = $_GET['tanggal_awal'];
    $tanggal_akhir = $_GET['tanggal_akhir'];

    if ($tanggal_awal != "" && $tanggal_akhir != "") {
        $where = "WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    }
}

$data_laporan = mysqli_query($koneksi, "
    SELECT * FROM view_laporan_parkir
    $where
    ORDER BY waktu_masuk DESC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Laporan Parkir</h2>
        <p>Menampilkan riwayat transaksi parkir kendaraan kampus.</p>
    </div>

    <div class="card">
        <h3>Filter Laporan Berdasarkan Tanggal</h3>

        <form method="GET">
            <label>Tanggal Awal</label>
            <input 
                type="date" 
                name="tanggal_awal" 
                value="<?= $tanggal_awal; ?>"
            >

            <label>Tanggal Akhir</label>
            <input 
                type="date" 
                name="tanggal_akhir" 
                value="<?= $tanggal_akhir; ?>"
            >

            <button type="submit" name="filter">Filter Laporan</button>

            <a href="laporan.php" class="btn btn-success">
                Reset
            </a>
        </form>
    </div>

    <div class="card">
        <h3>Data Laporan Parkir</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Nama Pemilik</th>
                <th>Kategori</th>
                <th>Area</th>
                <th>Slot</th>
                <th>Waktu Masuk</th>
                <th>Waktu Keluar</th>
                <th>Durasi</th>
                <th>Status</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_laporan)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['tanggal']; ?></td>
                    <td><?= $row['nomor_plat']; ?></td>
                    <td><?= $row['jenis_kendaraan']; ?></td>
                    <td><?= $row['nama_pemilik']; ?></td>
                    <td><?= $row['kategori_pengguna']; ?></td>
                    <td><?= $row['nama_area']; ?></td>
                    <td><?= $row['kode_slot']; ?></td>
                    <td><?= $row['waktu_masuk']; ?></td>
                    <td>
                        <?php if ($row['waktu_keluar'] == NULL) { ?>
                            -
                        <?php } else { ?>
                            <?= $row['waktu_keluar']; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?= $row['durasi_menit']; ?> menit
                    </td>
                    <td>
                        <?php if ($row['status_transaksi'] == 'Aktif') { ?>
                            <span class="badge badge-blue">Aktif</span>
                        <?php } else { ?>
                            <span class="badge badge-green">Selesai</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>Catatan Query</h3>
        <p>
            Halaman laporan memakai view_laporan_parkir karena data laporan berasal dari gabungan tabel transaksi_parkir, kendaraan, slot_parkir, dan area_parkir.
        </p>
    </div>
</div>

<?php include "layouts/footer.php"; ?>