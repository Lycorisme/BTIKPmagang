<?php
// 1. Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// --- HELPER FUNCTION UNTUK SWEETALERT (Agar tidak blank screen) ---
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
// ------------------------------------------------------------------

// ============= MAHASISWA =============
if ($action == 'create_mahasiswa') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'mahasiswa')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);
    
    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Berhasil!', 'Mahasiswa berhasil ditambahkan', '../pages/dashboard_admin.php');
    } else {
        showError('Email sudah terdaftar atau terjadi kesalahan');
    }
}

// UPDATE MAHASISWA (Dari Admin Dashboard)
if ($action == 'update_mahasiswa') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = ?, email = ? WHERE id = ? AND role = 'mahasiswa'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Berhasil!', 'Data mahasiswa diupdate', '../pages/dashboard_admin.php');
    } else {
        showError('Gagal update data');
    }
}

// DELETE MAHASISWA
if ($action == 'delete_mahasiswa') {
    $user_id = intval($_POST['user_id']);
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "DELETE FROM jurnal WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM lamaran WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role = 'mahasiswa'");
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'Mahasiswa dihapus', '../pages/dashboard_admin.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal menghapus data');
    }
}

// ============= MENTOR (ADMIN SIDE) =============
if ($action == 'create_mentor') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'mentor')");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);
        mysqli_stmt_execute($stmt);
        $user_id = mysqli_insert_id($conn);
        
        $stmt_m = mysqli_prepare($conn, "INSERT INTO mentors (user_id, keahlian, bio, status_open) VALUES (?, ?, ?, 1)");
        mysqli_stmt_bind_param($stmt_m, "iss", $user_id, $keahlian, $bio);
        mysqli_stmt_execute($stmt_m);
        
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'Mentor ditambahkan', '../pages/dashboard_admin.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Email sudah terdaftar');
    }
}

// ... (Update Mentor & Delete Mentor logic existing - skipped for brevity, use same showAlert pattern) ...
// Jika Anda butuh full code bagian ini, beri tahu saya. Logikanya sama dengan create_mentor di atas.

// ============= UPDATE PROFILE SENDIRI =============

// UPDATE PROFILE MAHASISWA (Self)
if ($action == 'update_profile_mahasiswa') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        showAlert('success', 'Berhasil', 'Profil berhasil diupdate', '../pages/profile_mahasiswa.php');
    } else {
        showError('Email sudah digunakan');
    }
}

// UPDATE PROFILE ADMIN (Self)
if ($action == 'update_profile_admin') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        showAlert('success', 'Berhasil', 'Profil Admin berhasil diupdate', '../pages/profile_admin.php');
    } else {
        showError('Update gagal');
    }
}

// CHANGE PASSWORD (ALL ROLES)
if ($action == 'change_password') {
    $user_id = intval($_POST['user_id']);
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Cek password lama
    $query = "SELECT password, role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    
    $password_valid = password_verify($password_lama, $user['password']);
    // Fallback jika password di db masih plain text
    if (!$password_valid && $user['password'] === $password_lama) {
        $password_valid = true;
    }
    
    if (!$password_valid) showError('Password lama salah');
    if ($password_baru !== $konfirmasi_password) showError('Konfirmasi password tidak cocok');
    
    $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $update = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt_up, "si", $password_hash, $user_id);
    
    if (mysqli_stmt_execute($stmt_up)) {
        // Redirect dinamis berdasarkan Role
        $redirect = '../pages/profile_mahasiswa.php'; // Default
        if ($user['role'] == 'admin') $redirect = '../pages/profile_admin.php';
        if ($user['role'] == 'mentor') $redirect = '../pages/profile_mentor.php';
        
        showAlert('success', 'Berhasil', 'Password berhasil diubah', $redirect);
    } else {
        showError('Gagal mengubah password');
    }
}
?>