<?php
$host = "localhost";
$user = "root";
$pass = "Achmad@186";
$db   = "db_parkir_kampus";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>