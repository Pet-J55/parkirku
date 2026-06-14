<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'pimpinan']);

$data_rekap = mysqli_query($koneksi, "
    CALL sp_rekap_slot_per_area()
");
?>

<div class="content">
    <div class="topbar">
        <h2>Rekap Slot Per Area</h2>
        <p>Menampilkan jumlah slot kosong, terisi, rusak, dan tidak aktif pada setiap area parkir.</p>
    </div>

    <div class="card">
        <h3>Rekap Slot Parkir</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Nama Area</th>
                <th>Total Slot</th>
                <th>Slot Kosong</th>
                <th>Slot Terisi</th>
                <th>Slot Rusak</th>
                <th>Slot Tidak Aktif</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_rekap)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['nama_area']; ?></td>
                    <td><?= $row['total_slot']; ?></td>
                    <td><?= $row['slot_kosong']; ?></td>
                    <td><?= $row['slot_terisi']; ?></td>
                    <td><?= $row['slot_rusak']; ?></td>
                    <td><?= $row['slot_tidak_aktif']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>Catatan Query</h3>
        <p>
            Halaman ini memanggil stored procedure sp_rekap_slot_per_area.
            Di dalam procedure tersebut terdapat cursor yang membaca data area parkir satu per satu,
            lalu menghitung jumlah slot berdasarkan statusnya.
        </p>
    </div>
</div>

<?php
while (mysqli_more_results($koneksi) && mysqli_next_result($koneksi)) {
    mysqli_store_result($koneksi);
}

include "layouts/footer.php";
?>