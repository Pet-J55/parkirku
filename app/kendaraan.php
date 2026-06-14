<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";

cekRole(['admin']);

$pesan = "";
$tipe_pesan = "";

if (isset($_POST['simpan'])) {
    $nomor_plat = mysqli_real_escape_string($koneksi, $_POST['nomor_plat']);
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $nama_pemilik = mysqli_real_escape_string($koneksi, $_POST['nama_pemilik']);
    $kategori_pengguna = $_POST['kategori_pengguna'];

    $simpan = mysqli_query($koneksi, "
        INSERT INTO kendaraan (
            nomor_plat,
            jenis_kendaraan,
            nama_pemilik,
            kategori_pengguna
        ) VALUES (
            '$nomor_plat',
            '$jenis_kendaraan',
            '$nama_pemilik',
            '$kategori_pengguna'
        )
    ");

    if ($simpan) {
        $pesan = "Data kendaraan berhasil disimpan.";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal menyimpan kendaraan. Nomor plat kemungkinan sudah terdaftar.";
        $tipe_pesan = "error";
    }
}

if (isset($_POST['update'])) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $nomor_plat = mysqli_real_escape_string($koneksi, $_POST['nomor_plat']);
    $jenis_kendaraan = $_POST['jenis_kendaraan'];
    $nama_pemilik = mysqli_real_escape_string($koneksi, $_POST['nama_pemilik']);
    $kategori_pengguna = $_POST['kategori_pengguna'];

    $update = mysqli_query($koneksi, "
        UPDATE kendaraan
        SET 
            nomor_plat = '$nomor_plat',
            jenis_kendaraan = '$jenis_kendaraan',
            nama_pemilik = '$nama_pemilik',
            kategori_pengguna = '$kategori_pengguna'
        WHERE id_kendaraan = '$id_kendaraan'
    ");

    if ($update) {
        $pesan = "Data kendaraan berhasil diupdate.";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal mengupdate kendaraan. Nomor plat kemungkinan sudah digunakan kendaraan lain.";
        $tipe_pesan = "error";
    }
}

if (isset($_GET['hapus'])) {
    $id_kendaraan = $_GET['hapus'];

    mysqli_query($koneksi, "
        DELETE FROM kendaraan
        WHERE id_kendaraan = '$id_kendaraan'
    ");

    header("Location: kendaraan.php");
    exit;
}

$edit_kendaraan = null;

if (isset($_GET['edit'])) {
    $id_kendaraan = $_GET['edit'];

    $query_edit = mysqli_query($koneksi, "
        SELECT * FROM kendaraan
        WHERE id_kendaraan = '$id_kendaraan'
    ");

    $edit_kendaraan = mysqli_fetch_assoc($query_edit);
}

$data_kendaraan = mysqli_query($koneksi, "
    SELECT * FROM kendaraan
    ORDER BY id_kendaraan DESC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Data Kendaraan</h2>
        <p>Kelola data kendaraan pengguna parkir kampus.</p>
    </div>

    <?php if ($pesan != "") { ?>
        <div class="message message-<?= $tipe_pesan; ?>">
            <?= $pesan; ?>
        </div>
    <?php } ?>

    <div class="card">
        <?php if ($edit_kendaraan) { ?>
            <h3>Edit Kendaraan</h3>

            <form method="POST">
                <input 
                    type="hidden" 
                    name="id_kendaraan" 
                    value="<?= $edit_kendaraan['id_kendaraan']; ?>"
                >

                <input 
                    type="text" 
                    name="nomor_plat" 
                    value="<?= $edit_kendaraan['nomor_plat']; ?>" 
                    required
                >

                <select name="jenis_kendaraan" required>
                    <option value="">-- Pilih Jenis Kendaraan --</option>
                    <option value="Motor" <?php if ($edit_kendaraan['jenis_kendaraan'] == 'Motor') echo "selected"; ?>>
                        Motor
                    </option>
                    <option value="Mobil" <?php if ($edit_kendaraan['jenis_kendaraan'] == 'Mobil') echo "selected"; ?>>
                        Mobil
                    </option>
                </select>

                <input 
                    type="text" 
                    name="nama_pemilik" 
                    value="<?= $edit_kendaraan['nama_pemilik']; ?>" 
                    required
                >

                <select name="kategori_pengguna" required>
                    <option value="">-- Pilih Kategori Pengguna --</option>
                    <option value="Mahasiswa" <?php if ($edit_kendaraan['kategori_pengguna'] == 'Mahasiswa') echo "selected"; ?>>
                        Mahasiswa
                    </option>
                    <option value="Dosen" <?php if ($edit_kendaraan['kategori_pengguna'] == 'Dosen') echo "selected"; ?>>
                        Dosen
                    </option>
                    <option value="Staff" <?php if ($edit_kendaraan['kategori_pengguna'] == 'Staff') echo "selected"; ?>>
                        Staff
                    </option>
                    <option value="Tamu" <?php if ($edit_kendaraan['kategori_pengguna'] == 'Tamu') echo "selected"; ?>>
                        Tamu
                    </option>
                </select>

                <button type="submit" name="update">Update Kendaraan</button>
                <a href="kendaraan.php" class="btn btn-success">Batal</a>
            </form>
        <?php } else { ?>
            <h3>Tambah Kendaraan</h3>

            <form method="POST">
                <input 
                    type="text" 
                    name="nomor_plat" 
                    placeholder="Nomor plat, contoh B 1234 ABC" 
                    required
                >

                <select name="jenis_kendaraan" required>
                    <option value="">-- Pilih Jenis Kendaraan --</option>
                    <option value="Motor">Motor</option>
                    <option value="Mobil">Mobil</option>
                </select>

                <input 
                    type="text" 
                    name="nama_pemilik" 
                    placeholder="Nama pemilik kendaraan" 
                    required
                >

                <select name="kategori_pengguna" required>
                    <option value="">-- Pilih Kategori Pengguna --</option>
                    <option value="Mahasiswa">Mahasiswa</option>
                    <option value="Dosen">Dosen</option>
                    <option value="Staff">Staff</option>
                    <option value="Tamu">Tamu</option>
                </select>

                <button type="submit" name="simpan">Simpan Kendaraan</button>
            </form>
        <?php } ?>
    </div>

    <div class="card">
        <h3>Daftar Kendaraan</h3>

        <table>
            <tr>
                <th>No</th>
                <th>Nomor Plat</th>
                <th>Jenis Kendaraan</th>
                <th>Nama Pemilik</th>
                <th>Kategori Pengguna</th>
                <th>Aksi</th>
            </tr>

            <?php $no = 1; while ($row = mysqli_fetch_assoc($data_kendaraan)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $row['nomor_plat']; ?></td>
                    <td><?= $row['jenis_kendaraan']; ?></td>
                    <td><?= $row['nama_pemilik']; ?></td>
                    <td><?= $row['kategori_pengguna']; ?></td>
                    <td>
                        <a href="kendaraan.php?edit=<?= $row['id_kendaraan']; ?>" class="btn">
                            Edit
                        </a>

                        <a 
                            href="kendaraan.php?hapus=<?= $row['id_kendaraan']; ?>" 
                            class="btn btn-danger"
                            onclick="return confirm('Yakin ingin menghapus kendaraan ini?')"
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