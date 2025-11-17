<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validasi password
    if ($password !== $confirm_password) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal!',
                text: 'Password dan konfirmasi password tidak cocok'
            }).then(() => {
                window.location.href = '../pages/register.php';
            });
        </script>";
        exit();
    }
    
    // Cek email sudah terdaftar atau belum
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal!',
                text: 'Email sudah terdaftar'
            }).then(() => {
                window.location.href = '../pages/register.php';
            });
        </script>";
        exit();
    }
    
    // Insert user baru
    $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', '$role')";
    
    if (mysqli_query($conn, $query)) {
        $user_id = mysqli_insert_id($conn);
        
        // Jika role mentor, insert ke tabel mentors
        if ($role == 'mentor') {
            $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
            $bio = mysqli_real_escape_string($conn, $_POST['bio']);
            
            $mentor_query = "INSERT INTO mentors (user_id, keahlian, bio, status_open) 
                            VALUES ('$user_id', '$keahlian', '$bio', 1)";
            mysqli_query($conn, $mentor_query);
        }
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registrasi Berhasil!',
                text: 'Silakan login dengan akun Anda'
            }).then(() => {
                window.location.href = '../pages/login.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal!',
                text: 'Terjadi kesalahan sistem'
            }).then(() => {
                window.location.href = '../pages/register.php';
            });
        </script>";
    }
}
?>