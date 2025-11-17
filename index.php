<?php
session_start();

// Redirect ke dashboard sesuai role jika sudah login
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: pages/dashboard_$role.php");
    exit();
}

// Redirect ke home page jika belum login
header('Location: pages/home.php');
exit();
?>