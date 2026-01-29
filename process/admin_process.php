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

// ============= TAMBAH USER UNIVERSAL (DENGAN PILIHAN ROLE) =============
if ($action == 'create_user') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Validasi role
    $valid_roles = ['admin', 'peserta_magang', 'mentor'];
    if (!in_array($role, $valid_roles)) {
        showError('Role tidak valid');
    }
    
    mysqli_begin_transaction($conn);
    try {
        // Insert user
        $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $password_hash, $role);
        mysqli_stmt_execute($stmt);
        $user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Jika mentor, insert ke tabel mentors
        if ($role == 'mentor') {
            $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian'] ?? '');
            $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
            
            $stmt_m = mysqli_prepare($conn, "INSERT INTO mentors (user_id, keahlian, bio, status_open) VALUES (?, ?, ?, 1)");
            mysqli_stmt_bind_param($stmt_m, "iss", $user_id, $keahlian, $bio);
            mysqli_stmt_execute($stmt_m);
            mysqli_stmt_close($stmt_m);
        }
        
        // Jika peserta_magang, insert ke tabel peserta_magang
        if ($role == 'peserta_magang') {
            $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
            $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
            $jenis_instansi = mysqli_real_escape_string($conn, $_POST['jenis_instansi'] ?? '');
            $nama_instansi = mysqli_real_escape_string($conn, $_POST['nama_instansi'] ?? '');
            $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan'] ?? '');
            $nim_nis = mysqli_real_escape_string($conn, $_POST['nim_nis'] ?? '');
            
            // Status belum lengkap karena data dari admin mungkin tidak lengkap
            $status_biodata = (!empty($no_hp) && !empty($jenis_kelamin) && !empty($nama_instansi)) ? 'lengkap' : 'belum_lengkap';
            
            $stmt_p = mysqli_prepare($conn, "INSERT INTO peserta_magang (user_id, no_hp, jenis_kelamin, tanggal_lahir, alamat, jenis_instansi, nama_instansi, jurusan, semester_kelas, nim_nis, status_biodata) VALUES (?, ?, ?, CURDATE(), '', ?, ?, ?, '', ?, ?)");
            mysqli_stmt_bind_param($stmt_p, "isssssss", $user_id, $no_hp, $jenis_kelamin, $jenis_instansi, $nama_instansi, $jurusan, $nim_nis, $status_biodata);
            mysqli_stmt_execute($stmt_p);
            mysqli_stmt_close($stmt_p);
        }
        
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'User ' . ucfirst($role) . ' berhasil ditambahkan', '../pages/dashboard_admin.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Email sudah terdaftar atau terjadi kesalahan');
    }
}

// ============= PESERTA MAGANG =============
if ($action == 'create_peserta_magang') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'peserta_magang')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);
    
    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Berhasil!', 'Peserta magang berhasil ditambahkan', '../pages/dashboard_admin.php');
    } else {
        showError('Email sudah terdaftar atau terjadi kesalahan');
    }
}

// UPDATE PESERTA MAGANG (Dari Admin Dashboard)
if ($action == 'update_peserta_magang') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = ?, email = ? WHERE id = ? AND role = 'peserta_magang'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        showAlert('success', 'Berhasil!', 'Data peserta magang diupdate', '../pages/dashboard_admin.php');
    } else {
        showError('Gagal update data');
    }
}

// DELETE PESERTA MAGANG
if ($action == 'delete_peserta_magang') {
    $user_id = intval($_POST['user_id']);
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "DELETE FROM jurnal WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM absensi WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM pendaftaran_magang WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM peserta_magang WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM sertifikat WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role = 'peserta_magang'");
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'Peserta magang dihapus', '../pages/dashboard_admin.php');
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

// UPDATE MENTOR
if ($action == 'update_mentor') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status_open = intval($_POST['status_open']);
    
    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
        mysqli_stmt_execute($stmt);
        
        $stmt_m = mysqli_prepare($conn, "UPDATE mentors SET keahlian = ?, bio = ?, status_open = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_m, "ssii", $keahlian, $bio, $status_open, $user_id);
        mysqli_stmt_execute($stmt_m);
        
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'Data mentor diupdate', '../pages/dashboard_admin.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal update data mentor');
    }
}

// DELETE MENTOR
if ($action == 'delete_mentor') {
    $user_id = intval($_POST['user_id']);
    mysqli_begin_transaction($conn);
    try {
        mysqli_query($conn, "UPDATE pendaftaran_magang SET mentor_id = NULL WHERE mentor_id = $user_id");
        mysqli_query($conn, "DELETE FROM mentors WHERE user_id = $user_id");
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role = 'mentor'");
        mysqli_commit($conn);
        showAlert('success', 'Berhasil!', 'Mentor dihapus', '../pages/dashboard_admin.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal menghapus data mentor');
    }
}

// ============= UPDATE PROFILE SENDIRI =============

// UPDATE PROFILE PESERTA MAGANG (Self)
if ($action == 'update_profile_peserta_magang') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Data biodata
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir'] ?? '');
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
    $jenis_instansi = mysqli_real_escape_string($conn, $_POST['jenis_instansi'] ?? '');
    $nama_instansi = mysqli_real_escape_string($conn, $_POST['nama_instansi'] ?? '');
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan'] ?? '');
    $semester_kelas = mysqli_real_escape_string($conn, $_POST['semester_kelas'] ?? '');
    $nim_nis = mysqli_real_escape_string($conn, $_POST['nim_nis'] ?? '');
    
    mysqli_begin_transaction($conn);
    try {
        // Update tabel users
        $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Cek apakah sudah ada data di peserta_magang
        $check = mysqli_query($conn, "SELECT id FROM peserta_magang WHERE user_id = $user_id");
        
        $status_biodata = (!empty($no_hp) && !empty($jenis_kelamin) && !empty($tanggal_lahir) && !empty($alamat) && !empty($nama_instansi) && !empty($jurusan)) ? 'lengkap' : 'belum_lengkap';
        
        if (mysqli_num_rows($check) > 0) {
            // Update
            $query_p = "UPDATE peserta_magang SET no_hp = ?, jenis_kelamin = ?, tanggal_lahir = ?, alamat = ?, jenis_instansi = ?, nama_instansi = ?, jurusan = ?, semester_kelas = ?, nim_nis = ?, status_biodata = ? WHERE user_id = ?";
            $stmt_p = mysqli_prepare($conn, $query_p);
            mysqli_stmt_bind_param($stmt_p, "ssssssssssi", $no_hp, $jenis_kelamin, $tanggal_lahir, $alamat, $jenis_instansi, $nama_instansi, $jurusan, $semester_kelas, $nim_nis, $status_biodata, $user_id);
        } else {
            // Insert
            $query_p = "INSERT INTO peserta_magang (user_id, no_hp, jenis_kelamin, tanggal_lahir, alamat, jenis_instansi, nama_instansi, jurusan, semester_kelas, nim_nis, status_biodata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_p = mysqli_prepare($conn, $query_p);
            mysqli_stmt_bind_param($stmt_p, "issssssssss", $user_id, $no_hp, $jenis_kelamin, $tanggal_lahir, $alamat, $jenis_instansi, $nama_instansi, $jurusan, $semester_kelas, $nim_nis, $status_biodata);
        }
        mysqli_stmt_execute($stmt_p);
        mysqli_stmt_close($stmt_p);
        
        mysqli_commit($conn);
        
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        showAlert('success', 'Berhasil', 'Profil berhasil diupdate', '../pages/profile_peserta_magang.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal update profil');
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

// UPDATE PROFILE MENTOR (Self)
if ($action == 'update_profile_mentor') {
    $user_id = intval($_POST['user_id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian'] ?? '');
    $bio = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    
    mysqli_begin_transaction($conn);
    try {
        $query = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $nama, $email, $user_id);
        mysqli_stmt_execute($stmt);
        
        $query_m = "UPDATE mentors SET keahlian = ?, bio = ? WHERE user_id = ?";
        $stmt_m = mysqli_prepare($conn, $query_m);
        mysqli_stmt_bind_param($stmt_m, "ssi", $keahlian, $bio, $user_id);
        mysqli_stmt_execute($stmt_m);
        
        mysqli_commit($conn);
        
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        showAlert('success', 'Berhasil', 'Profil Mentor berhasil diupdate', '../pages/profile_mentor.php');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        showError('Gagal update profil');
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
        $redirect = '../pages/profile_peserta_magang.php'; // Default
        if ($user['role'] == 'admin') $redirect = '../pages/profile_admin.php';
        if ($user['role'] == 'mentor') $redirect = '../pages/profile_mentor.php';
        
        showAlert('success', 'Berhasil', 'Password berhasil diubah', $redirect);
    } else {
        showError('Gagal mengubah password');
    }
}

// ============= UPLOAD SURAT PENGANTAR =============
if ($action == 'upload_surat_pengantar') {
    $user_id = intval($_POST['user_id']);
    
    if (isset($_FILES['surat_pengantar']) && $_FILES['surat_pengantar']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['surat_pengantar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            showError('Format file tidak valid. Gunakan PDF, JPG, atau PNG');
        }
        
        $new_filename = 'surat_pengantar_' . $user_id . '_' . time() . '.' . $ext;
        $upload_path = '../assets/uploads/' . $new_filename;
        
        if (move_uploaded_file($_FILES['surat_pengantar']['tmp_name'], $upload_path)) {
            $query = "UPDATE peserta_magang SET surat_pengantar = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);
            mysqli_stmt_execute($stmt);
            
            showAlert('success', 'Berhasil', 'Surat pengantar berhasil diupload', '../pages/profile_peserta_magang.php');
        } else {
            showError('Gagal upload file');
        }
    } else {
        showError('Pilih file untuk diupload');
    }
}
?>