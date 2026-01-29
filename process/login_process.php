<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Query untuk cek user berdasarkan email saja (role otomatis dari database)
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifikasi password (support both plain text dan hashed)
        $password_valid = false;
        if (password_verify($password, $user['password'])) {
            $password_valid = true;
        } elseif ($password === $user['password']) {
            // Fallback untuk password plain text (legacy)
            $password_valid = true;
        }
        
        if ($password_valid) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect berdasarkan role dari database
            header("Location: ../pages/dashboard_" . $user['role'] . ".php");
            exit();
        }
    }
    mysqli_stmt_close($stmt);
    
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
                text: 'Email atau password tidak sesuai',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../pages/login.php';
            });
        </script>
    </body>
    </html>
    <?php
    exit();
} else {
    header('Location: ../pages/login.php');
    exit();
}
?>