<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'petugas']);

$data_aktif = mysqli_query($koneksi, "
    SELECT * FROM view_kendaraan_aktif
    ORDER BY waktu_masuk DESC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Kendaraan Aktif</h2>
        <p>Daftar kendaraan yang sedang berada di area parkir kampus.</p>
    </div>

    <div class="card">
        <h3>Daftar Kendaraan yang Sedang Parkir</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis</th>
                <th>Nama Pemilik</th>
                <th>Kategori</th>
                <th>Area Parkir</th>
                <th>Lokasi</th>
                <th>Kode Slot</th>
                <th>Waktu Masuk</th>
                <th>Status</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_aktif)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['nomor_plat']; ?></td>
                    <td><?= $row['jenis_kendaraan']; ?></td>
                    <td><?= $row['nama_pemilik']; ?></td>
                    <td><?= $row['kategori_pengguna']; ?></td>
                    <td><?= $row['nama_area']; ?></td>
                    <td><?= $row['lokasi']; ?></td>
                    <td><?= $row['kode_slot']; ?></td>
                    <td><?= $row['waktu_masuk']; ?></td>
                    <td>
                        <span class="badge badge-blue">
                            <?= $row['status_transaksi']; ?>
                        </span>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>Catatan Query</h3>
        <p>
            Halaman ini memakai view_kendaraan_aktif karena data yang ditampilkan berasal dari gabungan tabel transaksi_parkir, kendaraan, slot_parkir, dan area_parkir.
        </p>
    </div>
</div>

<?php include "layouts/footer.php"; ?>