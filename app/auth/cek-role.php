<?php
function cekRole($role_diizinkan) {
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        echo "
            <script>
                alert('Akses ditolak! Role Anda tidak memiliki izin membuka halaman ini.');
                window.location.href = 'dashboard.php';
            </script>
        ";
        exit;
    }
}
?>