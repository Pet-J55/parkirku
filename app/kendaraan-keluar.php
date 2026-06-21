<?php
include "config/koneksi.php";
include "layouts/header.php";
include "layouts/sidebar.php";
include "auth/cek-role.php";
cekRole(['admin', 'petugas']);

$pesan = "";
$tipe_pesan = "";

if (isset($_POST['proses_keluar'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $id_user = $_SESSION['id_user'];

    try {
        $query = mysqli_query($koneksi, "
            CALL sp_kendaraan_keluar('$id_transaksi', '$id_user')
        ");

        if ($query) {
            $pesan = "Kendaraan berhasil keluar. Transaksi selesai dan slot kembali Kosong.";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal memproses kendaraan keluar: " . mysqli_error($koneksi);
            $tipe_pesan = "error";
        }

        while (mysqli_more_results($koneksi) && mysqli_next_result($koneksi)) {
            mysqli_store_result($koneksi);
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "Gagal memproses kendaraan keluar: " . $e->getMessage();
        $tipe_pesan = "error";
    }
}

$data_aktif = mysqli_query($koneksi, "
    SELECT * FROM view_kendaraan_aktif
    ORDER BY waktu_masuk ASC
");
?>

<div class="content">
    <div class="topbar">
        <h2>Kendaraan Keluar</h2>
        <p>Proses kendaraan yang selesai menggunakan area parkir.</p>
    </div>

    <?php if ($pesan != "") { ?>
        <div class="message message-<?= $tipe_pesan; ?>">
            <?= $pesan; ?>
        </div>
    <?php } ?>

    <div class="card">
        <h3>Form Kendaraan Keluar</h3>

        <form method="POST">
            <select name="id_transaksi" required>
                <option value="">-- Pilih Kendaraan Aktif --</option>

                <?php while ($aktif = mysqli_fetch_assoc($data_aktif)) { ?>
                    <option value="<?= $aktif['id_transaksi']; ?>">
                        <?= $aktif['nomor_plat']; ?> -
                        <?= $aktif['nama_pemilik']; ?> -
                        Slot <?= $aktif['kode_slot']; ?> -
                        <?= $aktif['nama_area']; ?> -
                        Masuk: <?= $aktif['waktu_masuk']; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit" name="proses_keluar">
                Proses Kendaraan Keluar
            </button>
        </form>
    </div>

    <div class="card">
        <h3>Catatan Proses</h3>
        <p>
            Halaman ini mengambil kendaraan aktif dari view_kendaraan_aktif.
            Setelah petugas memilih kendaraan, PHP memanggil stored procedure
            sp_kendaraan_keluar untuk mengisi waktu keluar, menghitung durasi,
            mengubah status transaksi menjadi Selesai, dan trigger otomatis
            mengubah slot menjadi Kosong.
        </p>
    </div>
</div>

<?php include "layouts/footer.php"; ?>