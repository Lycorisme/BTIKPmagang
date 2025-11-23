<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// --- HELPER FUNCTION SWEETALERT ---
function showAlert($icon, $title, $text, $redirect) {
    echo "<!DOCTYPE html><html><head>";
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "</head><body>";
    echo "<script>
        Swal.fire({
            icon: '$icon',
            title: '$title',
            text: '$text',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = '$redirect';
        });
    </script>";
    echo "</body></html>";
    exit();
}

function showError($text) {
    echo "<!DOCTYPE html><html><head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head><body>";
    echo "<script>Swal.fire({icon: 'error', title: 'Gagal!', text: '$text'}).then(() => { window.history.back(); });</script>";
    echo "</body></html>";
    exit();
}
// ----------------------------------

// UPDATE PROFILE MENTOR (Self Update)
if ($action == 'update_profile') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status_open = intval($_POST['status_open']);

    // Mulai Transaksi (Update tabel users DAN tabel mentors)
    mysqli_begin_transaction($conn);

    try {
        // 1. Update Tabel Users (Nama & Email)
        $query_user = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
        $stmt_user = mysqli_prepare($conn, $query_user);
        mysqli_stmt_bind_param($stmt_user, "ssi", $nama, $email, $user_id);
        mysqli_stmt_execute($stmt_user);

        // 2. Update Tabel Mentors (Keahlian, Bio, Status)
        $query_mentor = "UPDATE mentors SET keahlian = ?, bio = ?, status_open = ? WHERE user_id = ?";
        $stmt_mentor = mysqli_prepare($conn, $query_mentor);
        mysqli_stmt_bind_param($stmt_mentor, "ssii", $keahlian, $bio, $status_open, $user_id);
        mysqli_stmt_execute($stmt_mentor);

        mysqli_commit($conn);
        
        // Update session
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;

        showAlert('success', 'Berhasil!', 'Profil Mentor berhasil diupdate', '../pages/profile_mentor.php');

    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal mengupdate profil: ' . $e->getMessage());
    }
}

// CHANGE PASSWORD (Khusus Mentor, jika form password di profile_mentor mengarah kesini)
if ($action == 'change_password') {
    $user_id = intval($_POST['user_id']);
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $password_valid = password_verify($password_lama, $user['password']);
    if (!$password_valid && $user['password'] === $password_lama) $password_valid = true;
    
    if (!$password_valid) showError('Password lama salah');
    if ($password_baru !== $konfirmasi_password) showError('Konfirmasi password tidak cocok');
    
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $update = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt_up, "si", $password_hash, $user_id);
    
    if (mysqli_stmt_execute($stmt_up)) {
        showAlert('success', 'Berhasil', 'Password berhasil diubah', '../pages/profile_mentor.php');
    } else {
        showError('Gagal mengubah password');
    }
}
?>