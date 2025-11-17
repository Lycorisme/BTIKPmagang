<?php
// Cek apakah user sudah login
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../pages/login.php');
        exit();
    }
}

// Cek role user
function checkRole($allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Akses Ditolak',
                text: 'Anda tidak memiliki akses ke halaman ini'
            }).then(() => {
                window.location.href = '../index.php';
            });
        </script>";
        exit();
    }
}

// Redirect jika sudah login
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'];
        header("Location: dashboard_$role.php");
        exit();
    }
}
?>