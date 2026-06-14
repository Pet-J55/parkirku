<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'pimpinan']);

$data_log = mysqli_query($koneksi, "
    SELECT 
        log_aktivitas.id_log,
        log_aktivitas.aktivitas,
        log_aktivitas.keterangan,
        log_aktivitas.waktu_log,
        users.nama,
        users.username,
        users.role
    FROM log_aktivitas
    LEFT JOIN users ON log_aktivitas.id_user = users.id_user
    ORDER BY log_aktivitas.waktu_log DESC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Log Aktivitas</h2>
        <p>Menampilkan catatan aktivitas yang terjadi pada sistem parkir digital kampus.</p>
    </div>

    <div class="card">
        <h3>Daftar Log Aktivitas</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Nama User</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aktivitas</th>
                <th>Keterangan</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_log)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['waktu_log']; ?></td>
                    <td>
                        <?php if ($row['nama'] == NULL) { ?>
                            Sistem
                        <?php } else { ?>
                            <?= $row['nama']; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['username'] == NULL) { ?>
                            -
                        <?php } else { ?>
                            <?= $row['username']; ?>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['role'] == NULL) { ?>
                            -
                        <?php } else { ?>
                            <span class="badge badge-blue">
                                <?= $row['role']; ?>
                            </span>
                        <?php } ?>
                    </td>
                    <td><?= $row['aktivitas']; ?></td>
                    <td><?= $row['keterangan']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <div class="card">
        <h3>Catatan Query</h3>
        <p>
            Halaman ini menggunakan SELECT dengan LEFT JOIN untuk menggabungkan tabel log_aktivitas dan users.
            LEFT JOIN digunakan agar log tetap tampil meskipun id_user bernilai NULL.
        </p>
    </div>
</div>

<?php include "layouts/footer.php"; ?>