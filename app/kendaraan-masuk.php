<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'petugas']);

$pesan = "";
$tipe_pesan = "";

if (isset($_POST['proses_masuk'])) {
    $id_kendaraan = $_POST['id_kendaraan'];
    $id_slot = $_POST['id_slot'];
    $id_user = $_SESSION['id_user'];

    $query = mysqli_query($koneksi, "
        CALL sp_kendaraan_masuk('$id_kendaraan', '$id_slot', '$id_user')
    ");

    if ($query) {
        $pesan = "Kendaraan berhasil masuk. Slot otomatis berubah menjadi Terisi.";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal memproses kendaraan masuk: " . mysqli_error($koneksi);
        $tipe_pesan = "error";
    }

    while (mysqli_more_results($koneksi) && mysqli_next_result($koneksi)) {
        mysqli_store_result($koneksi);
    }
}

$data_kendaraan = mysqli_query($koneksi, "
    SELECT * FROM kendaraan
    ORDER BY nomor_plat ASC
");

$data_slot = mysqli_query($koneksi, "
    SELECT * FROM view_slot_kosong
    ORDER BY kode_slot ASC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Kendaraan Masuk</h2>
        <p>Catat kendaraan yang masuk dan pilih slot parkir yang masih kosong.</p>
    </div>

    <?php if ($pesan != "") { ?>
        <div class="message message-<?= $tipe_pesan; ?>">
            <?= $pesan; ?>
        </div>
    <?php } ?>

    <div class="card">
        <h3>Form Kendaraan Masuk</h3>

        <form method="POST">
            <select name="id_kendaraan" required>
                <option value="">-- Pilih Kendaraan --</option>

                <?php while ($kendaraan = mysqli_fetch_assoc($data_kendaraan)) { ?>
                    <option value="<?= $kendaraan['id_kendaraan']; ?>">
                        <?= $kendaraan['nomor_plat']; ?> -
                        <?= $kendaraan['nama_pemilik']; ?> -
                        <?= $kendaraan['jenis_kendaraan']; ?> -
                        <?= $kendaraan['kategori_pengguna']; ?>
                    </option>
                <?php } ?>
            </select>

            <select name="id_slot" required>
                <option value="">-- Pilih Slot Kosong --</option>

                <?php while ($slot = mysqli_fetch_assoc($data_slot)) { ?>
                    <option value="<?= $slot['id_slot']; ?>">
                        <?= $slot['kode_slot']; ?> -
                        <?= $slot['nama_area']; ?> -
                        <?= $slot['lokasi']; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit" name="proses_masuk">
                Proses Kendaraan Masuk
            </button>
        </form>
    </div>

    <div class="card">
        <h3>Catatan Proses</h3>
        <p>
            Halaman ini memakai SELECT biasa untuk mengambil data kendaraan,
            view untuk mengambil slot kosong, stored procedure untuk memproses kendaraan masuk,
            dan trigger untuk mengubah status slot menjadi Terisi secara otomatis.
        </p>
    </div>
</div>

<?php include "layouts/footer.php"; ?>