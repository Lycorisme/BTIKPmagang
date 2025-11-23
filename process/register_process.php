<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validasi role
    $valid_roles = ['mahasiswa', 'mentor'];
    if (!in_array($role, $valid_roles)) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Role tidak valid',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Validasi input kosong
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Semua field harus diisi',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Format email tidak valid',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Password minimal 6 karakter',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Validasi password
    if ($password !== $confirm_password) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Password dan konfirmasi password tidak cocok',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Cek email sudah terdaftar atau belum
    $check_query = "SELECT * FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        mysqli_stmt_close($check_stmt);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Email sudah terdaftar',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
    mysqli_stmt_close($check_stmt);
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // Insert user baru
        $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password_hash, $role);
        mysqli_stmt_execute($stmt);
        
        $user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Jika role mentor, insert ke tabel mentors
        if ($role == 'mentor') {
            $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
            $bio = mysqli_real_escape_string($conn, $_POST['bio']);
            
            // Validasi keahlian dan bio untuk mentor
            if (empty($keahlian) || empty($bio)) {
                mysqli_rollback($conn);
                ?>
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Registrasi Gagal!',
                            text: 'Keahlian dan bio wajib diisi untuk mentor',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '../pages/register.php';
                        });
                    </script>
                </body>
                </html>
                <?php
                exit();
            }
            
            $mentor_query = "INSERT INTO mentors (user_id, keahlian, bio, status_open) 
                            VALUES (?, ?, ?, 1)";
            $mentor_stmt = mysqli_prepare($conn, $mentor_query);
            mysqli_stmt_bind_param($mentor_stmt, "iss", $user_id, $keahlian, $bio);
            mysqli_stmt_execute($mentor_stmt);
            mysqli_stmt_close($mentor_stmt);
        }
        
        // Commit transaksi
        mysqli_commit($conn);
        
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Silakan login dengan akun Anda',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/login.php';
                });
            </script>
        </body>
        </html>
        <?php
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    text: 'Terjadi kesalahan sistem',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '../pages/register.php';
                });
            </script>
        </body>
        </html>
        <?php
    }
    exit();
} else {
    header('Location: ../pages/register.php');
    exit();
}
?>