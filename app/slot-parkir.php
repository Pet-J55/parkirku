<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";

cekRole(['admin']);
$pesan = "";
$tipe_pesan="";

if (isset($_POST['simpan'])) {
    $id_area = $_POST['id_area'];
    $kode_slot = mysqli_real_escape_string($koneksi, $_POST['kode_slot']);
    $status_slot = $_POST['status_slot'];

    try {
        $simpan = mysqli_query($koneksi, "
            INSERT INTO slot_parkir (id_area, kode_slot, status_slot)
            VALUES ('$id_area', '$kode_slot', '$status_slot')
        ");

        if ($simpan) {
            $pesan = "Data slot berhasil disimpan.";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal menyimpan slot. Kode slot kemungkinan sudah digunakan slot lain.";
            $tipe_pesan = "error";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "Gagal menyimpan slot. Kode slot kemungkinan sudah digunakan slot lain.";
        $tipe_pesan = "error";
    }
}

if (isset($_POST['update'])) {
    $id_slot = $_POST['id_slot'];
    $id_area = $_POST['id_area'];
    $kode_slot = mysqli_real_escape_string($koneksi, $_POST['kode_slot']);
    $status_slot = $_POST['status_slot'];

    try {
        $update = mysqli_query($koneksi, "
            UPDATE slot_parkir
            SET 
                id_area = '$id_area',
                kode_slot = '$kode_slot',
                status_slot = '$status_slot'
            WHERE id_slot = '$id_slot'
        ");

        if ($update) {
            header("Location: slot-parkir.php");
            exit;
        } else {
            $pesan = "Gagal mengupdate slot. Kode slot kemungkinan sudah digunakan slot lain.";
            $tipe_pesan = "error";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "Gagal mengupdate slot. Kode slot kemungkinan sudah digunakan slot lain.";
        $tipe_pesan = "error";
    }
}

if (isset($_GET['hapus'])) {
    $id_slot = $_GET['hapus'];

    mysqli_query($koneksi, "
        DELETE FROM slot_parkir
        WHERE id_slot = '$id_slot'
    ");

    header("Location: slot-parkir.php");
    exit;
}

$edit_slot = null;

if (isset($_GET['edit'])) {
    $id_slot = $_GET['edit'];

    $query_edit = mysqli_query($koneksi, "
        SELECT * FROM slot_parkir
        WHERE id_slot = '$id_slot'
    ");

    $edit_slot = mysqli_fetch_assoc($query_edit);
}

$data_area = mysqli_query($koneksi, "
    SELECT * FROM area_parkir
    ORDER BY nama_area ASC
");

$data_slot = mysqli_query($koneksi, "
    SELECT 
        slot_parkir.id_slot,
        slot_parkir.id_area,
        slot_parkir.kode_slot,
        slot_parkir.status_slot,
        area_parkir.nama_area,
        area_parkir.lokasi
    FROM slot_parkir
    JOIN area_parkir ON slot_parkir.id_area = area_parkir.id_area
    ORDER BY slot_parkir.id_slot DESC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Data Slot Parkir</h2>
        <p>Kelola data slot parkir berdasarkan area parkir kampus.</p>
    </div>

    <?php if ($pesan != "") { ?>
        <div class="message message-<?= $tipe_pesan; ?>">
            <?= $pesan; ?>
        </div>
    <?php } ?>

    <div class="card">
        <?php if ($edit_slot) { ?>
            <h3>Edit Slot Parkir</h3>

            <form method="POST">
                <input type="hidden" name="id_slot" value="<?= $edit_slot['id_slot']; ?>">

                <select name="id_area" required>
                    <option value="">-- Pilih Area Parkir --</option>

                    <?php while ($area = mysqli_fetch_assoc($data_area)) { ?>
                        <option 
                            value="<?= $area['id_area']; ?>"
                            <?php if ($area['id_area'] == $edit_slot['id_area']) echo "selected"; ?>
                        >
                            <?= $area['nama_area']; ?> - <?= $area['lokasi']; ?>
                        </option>
                    <?php } ?>
                </select>

                <input 
                    type="text" 
                    name="kode_slot" 
                    value="<?= $edit_slot['kode_slot']; ?>" 
                    required
                >

                <select name="status_slot" required>
                    <option value="Kosong" <?php if ($edit_slot['status_slot'] == 'Kosong') echo "selected"; ?>>Kosong</option>
                    <option value="Terisi" <?php if ($edit_slot['status_slot'] == 'Terisi') echo "selected"; ?>>Terisi</option>
                    <option value="Rusak" <?php if ($edit_slot['status_slot'] == 'Rusak') echo "selected"; ?>>Rusak</option>
                    <option value="Tidak Aktif" <?php if ($edit_slot['status_slot'] == 'Tidak Aktif') echo "selected"; ?>>Tidak Aktif</option>
                </select>

                <button type="submit" name="update">Update Slot</button>
                <a href="slot-parkir.php" class="btn btn-success">Batal</a>
            </form>
        <?php } else { ?>
            <h3>Tambah Slot Parkir</h3>

            <form method="POST">
                <select name="id_area" required>
                    <option value="">-- Pilih Area Parkir --</option>

                    <?php while ($area = mysqli_fetch_assoc($data_area)) { ?>
                        <option value="<?= $area['id_area']; ?>">
                            <?= $area['nama_area']; ?> - <?= $area['lokasi']; ?>
                        </option>
                    <?php } ?>
                </select>

                <input type="text" name="kode_slot" placeholder="Kode slot, contoh A-01" required>

                <select name="status_slot" required>
                    <option value="Kosong">Kosong</option>
                    <option value="Terisi">Terisi</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>

                <button type="submit" name="simpan">Simpan Slot</button>
            </form>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Daftar Slot Parkir</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Area Parkir</th>
                <th>Lokasi</th>
                <th>Kode Slot</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_slot)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['nama_area']; ?></td>
                    <td><?= $row['lokasi']; ?></td>
                    <td><?= $row['kode_slot']; ?></td>
                    <td>
                        <?php if ($row['status_slot'] == 'Kosong') { ?>
                            <span class="badge badge-green">Kosong</span>
                        <?php } elseif ($row['status_slot'] == 'Terisi') { ?>
                            <span class="badge badge-red">Terisi</span>
                        <?php } else { ?>
                            <span class="badge badge-blue"><?= $row['status_slot']; ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="slot-parkir.php?edit=<?= $row['id_slot']; ?>" class="btn">
                            Edit
                        </a>

                        <a 
                            href="slot-parkir.php?hapus=<?= $row['id_slot']; ?>" 
                            class="btn btn-danger"
                            onclick="return confirm('Yakin ingin menghapus slot ini?')"
                        >
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<?php include "layouts/footer.php"; ?>