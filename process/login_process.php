<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Query untuk cek user
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND role = '$role'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect langsung tanpa SweetAlert
        header("Location: ../pages/dashboard_" . $user['role'] . ".php");
        exit();
    } else {
        // Login gagal - tampilkan SweetAlert
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Gagal</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: 'Email, password, atau role tidak sesuai',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/login.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
} else {
    header('Location: ../pages/login.php');
    exit();
}
?>