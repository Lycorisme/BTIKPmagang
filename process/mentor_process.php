<?php
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// UPDATE PROFILE
if ($action == 'update_profile') {
    $user_id = $_POST['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status_open = $_POST['status_open'];
    
    // Update users table
    $query_user = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$user_id'";
    
    // Update mentors table
    $query_mentor = "UPDATE mentors SET keahlian = '$keahlian', bio = '$bio', status_open = '$status_open' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $query_user) && mysqli_query($conn, $query_mentor)) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/profile_mentor.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate profil'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// CHANGE PASSWORD
if ($action == 'change_password') {
    $user_id = $_POST['user_id'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Cek password lama
    $query_check = "SELECT password FROM users WHERE id = '$user_id'";
    $result_check = mysqli_query($conn, $query_check);
    $user = mysqli_fetch_assoc($result_check);
    
    if ($user['password'] != $password_lama) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Password lama tidak sesuai'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    if ($password_baru != $konfirmasi_password) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Password baru dan konfirmasi tidak cocok'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $query = "UPDATE users SET password = '$password_baru' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Password berhasil diubah'
            }).then(() => {
                window.location.href = '../pages/profile_mentor.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengubah password'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}
?>
