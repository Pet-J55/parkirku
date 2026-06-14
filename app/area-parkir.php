<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";

cekRole(['admin']);
$pesan = "";
$tipe_pesan = "";

if (isset($_POST['simpan'])) {
    $nama_area = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

$simpan = mysqli_query($koneksi, "
    INSERT INTO area_parkir (nama_area, lokasi, keterangan)
    VALUES ('$nama_area', '$lokasi', '$keterangan')
");

    if ($simpan) {
        $pesan = "Data area berhasil disimpan.";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal menyimpan area. Nama area kemungkinan sudah ada.";
        $tipe_pesan = "error";
    }
}

if (isset($_POST['update'])) {
    $id_area = $_POST['id_area'];
    $nama_area = mysqli_real_escape_string($koneksi, $_POST['nama_area']);
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

$update = mysqli_query($koneksi, "
    UPDATE area_parkir 
    SET 
        nama_area = '$nama_area',
        lokasi = '$lokasi',
        keterangan = '$keterangan'
    WHERE id_area = '$id_area'
");

    if ($update) {
        $pesan = "Data area berhasil diupdate.";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal mengupdate area. Nama area kemungkinan sudah digunakan slot lain.";
        $tipe_pesan = "error";
    }
}

if (isset($_GET['hapus'])) {
    $id_area = $_GET['hapus'];

    mysqli_query($koneksi, "
        DELETE FROM area_parkir
        WHERE id_area = '$id_area'
    ");

    $pesan = "Data area berhasil dihapus.";
    $tipe_pesan = "success";
}

$edit_area = null;

if (isset($_GET['edit'])) {
    $id_area = $_GET['edit'];

    $query_edit = mysqli_query($koneksi, "
        SELECT * FROM area_parkir
        WHERE id_area = '$id_area'
    ");

    $edit_area = mysqli_fetch_assoc($query_edit);
}

$data_area = mysqli_query($koneksi, "
    SELECT * FROM area_parkir
    ORDER BY id_area DESC
");
?>
<div class="content">
    <div class="topbar">
        <h2>Data Area Parkir</h2>
        <p>Kelola data area parkir kampus.</p>
    </div>

    <?php if ($pesan != "") { ?>
        <div class="message message-<?= $tipe_pesan; ?>">
            <?= $pesan; ?>
        </div>
    <?php } ?>

    <div class="card">
        <?php if ($edit_area) { ?>
            <h3>Edit Area Parkir</h3>

            <form method="POST">
                <input type="hidden" name="id_area" value="<?= $edit_area['id_area']; ?>">

                <input 
                    type="text" 
                    name="nama_area" 
                    value="<?= $edit_area['nama_area']; ?>" 
                    required
                >

                <input 
                    type="text" 
                    name="lokasi" 
                    value="<?= $edit_area['lokasi']; ?>" 
                    required
                >

                <textarea name="keterangan"><?= $edit_area['keterangan']; ?></textarea>

                <button type="submit" name="update">Update Area</button>
                <a href="area-parkir.php" class="btn btn-success">Batal</a>
            </form>
        <?php } else { ?>
            <h3>Tambah Area Parkir</h3>

            <form method="POST">
                <input type="text" name="nama_area" placeholder="Nama area parkir" required>

                <input type="text" name="lokasi" placeholder="Lokasi area parkir" required>

                <textarea name="keterangan" placeholder="Keterangan area parkir"></textarea>

                <button type="submit" name="simpan">Simpan Area</button>
            </form>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Daftar Area Parkir</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Nama Area</th>
                <th>Lokasi</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_area)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['nama_area']; ?></td>
                    <td><?= $row['lokasi']; ?></td>
                    <td><?= $row['keterangan']; ?></td>
                    <td>
                        <a href="area-parkir.php?edit=<?= $row['id_area']; ?>" class="btn">
                            Edit
                        </a>

                        <a 
                            href="area-parkir.php?hapus=<?= $row['id_area']; ?>" 
                            class="btn btn-danger"
                            onclick="return confirm('Yakin ingin menghapus area ini?')"
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