<?php
// 1. NYALAKAN ERROR REPORTING (Supaya tidak blank screen jika ada error)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 2. CEK KONEKSI DATABASE
// Pastikan path ini sesuai dengan struktur folder Anda
if (!file_exists('../config/database.php')) {
    die("Error Fatal: File database tidak ditemukan di ../config/database.php");
}
require_once '../config/database.php';

// 3. CEK REQUEST
// Mencegah akses langsung via URL browser (GET request)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<h1>Akses Ditolak</h1>";
    echo "<p>File ini hanya memproses data dari formulir (POST).</p>";
    echo "<a href='../pages/jurnal_mahasiswa.php'>Kembali</a>";
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

// =========================================================
// ACTION: CREATE (Tambah Jurnal)
// =========================================================
if ($action == 'create') {
    $user_id = intval($_SESSION['user_id']);
    $mentor_id = intval($_POST['mentor_id']);
    $tanggal = $_POST['tanggal'];
    $aktivitas = mysqli_real_escape_string($conn, $_POST['aktivitas']);
    
    // Validasi input
    if (empty($tanggal) || empty($aktivitas)) {
        showError('Tanggal dan aktivitas wajib diisi');
    }
    
    // Upload file pendukung
    $file_penunjang = '';
    if (isset($_FILES['file_penunjang']) && $_FILES['file_penunjang']['error'] == 0) {
        $file_name = $_FILES['file_penunjang']['name'];
        $file_tmp = $_FILES['file_penunjang']['tmp_name'];
        $file_size = $_FILES['file_penunjang']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        // Cek ekstensi dan ukuran (max 5MB)
        if (in_array($file_ext, $allowed_ext) && $file_size <= 5242880) {
            $new_file_name = 'JURNAL_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_dir = '../assets/uploads/';
            $upload_path = $upload_dir . $new_file_name;
            
            // Buat folder jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $file_penunjang = $new_file_name;
            } else {
                showError('Gagal mengupload file ke folder tujuan');
            }
        } else {
            showError('File tidak valid atau terlalu besar (Max 5MB)');
        }
    }
    
    $query = "INSERT INTO jurnal (user_id, mentor_id, tanggal, aktivitas, file_penunjang) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisss", $user_id, $mentor_id, $tanggal, $aktivitas, $file_penunjang);
        
        if (mysqli_stmt_execute($stmt)) {
            showSuccess('Jurnal berhasil disimpan');
        } else {
            // Hapus file jika insert db gagal
            if ($file_penunjang && file_exists('../assets/uploads/' . $file_penunjang)) {
                unlink('../assets/uploads/' . $file_penunjang);
            }
            showError('Gagal menyimpan data ke database: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    } else {
        showError('Query Error: ' . mysqli_error($conn));
    }
}

// =========================================================
// ACTION: UPDATE (Edit Jurnal)
// =========================================================
elseif ($action == 'update') {
    $jurnal_id = intval($_POST['jurnal_id']);
    $user_id = intval($_SESSION['user_id']);
    $tanggal = $_POST['tanggal'];
    $aktivitas = mysqli_real_escape_string($conn, $_POST['aktivitas']);
    
    if (empty($tanggal) || empty($aktivitas)) {
        showError('Tanggal dan aktivitas wajib diisi');
    }
    
    // Cek kepemilikan & ambil file lama
    $check_query = "SELECT file_penunjang FROM jurnal WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $jurnal_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        showError('Anda tidak memiliki akses edit untuk jurnal ini');
    }
    
    $data = mysqli_fetch_assoc($result);
    $old_file = $data['file_penunjang'];
    $file_penunjang = $old_file; // Default pakai file lama
    
    // Proses upload file baru jika ada
    if (isset($_FILES['file_penunjang']) && $_FILES['file_penunjang']['error'] == 0) {
        // ... (Logika upload sama seperti create, disingkat) ...
        $file_name = $_FILES['file_penunjang']['name'];
        $file_tmp = $_FILES['file_penunjang']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = 'JURNAL_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = '../assets/uploads/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus file lama
                if ($old_file && file_exists('../assets/uploads/' . $old_file)) {
                    unlink('../assets/uploads/' . $old_file);
                }
                $file_penunjang = $new_file_name;
            }
        }
    }
    
    $query = "UPDATE jurnal SET tanggal = ?, aktivitas = ?, file_penunjang = ? WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssii", $tanggal, $aktivitas, $file_penunjang, $jurnal_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        showSuccess('Jurnal berhasil diupdate');
    } else {
        showError('Gagal update database');
    }
}

// =========================================================
// ACTION: DELETE (Hapus Jurnal)
// =========================================================
elseif ($action == 'delete') {
    $jurnal_id = intval($_POST['jurnal_id']);
    $user_id = intval($_SESSION['user_id']);
    
    // Ambil info file dulu
    $query = "SELECT file_penunjang FROM jurnal WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $jurnal_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $file_to_delete = $row['file_penunjang'];
        
        // Hapus Data
        $del_query = "DELETE FROM jurnal WHERE id = ? AND user_id = ?";
        $del_stmt = mysqli_prepare($conn, $del_query);
        mysqli_stmt_bind_param($del_stmt, "ii", $jurnal_id, $user_id);
        
        if (mysqli_stmt_execute($del_stmt)) {
            // Hapus Fisik File
            if ($file_to_delete && file_exists('../assets/uploads/' . $file_to_delete)) {
                unlink('../assets/uploads/' . $file_to_delete);
            }
            showSuccess('Jurnal berhasil dihapus');
        } else {
            showError('Gagal menghapus data');
        }
    } else {
        showError('Jurnal tidak ditemukan atau akses ditolak');
    }
}

// Jika action tidak dikenali
else {
    // Jika update feedback/nilai (untuk mentor), tambahkan blok if ($action == 'update_feedback') disini
    // Jika tidak ada action sama sekali:
    showError('Action tidak valid');
}

// =========================================================
// FUNGSI BANTUAN (HELPER FUNCTIONS)
// =========================================================

function showSuccess($message) {
    echo "<!DOCTYPE html><html><head>";
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "</head><body>";
    echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '$message',
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            window.location.href = '../pages/jurnal_mahasiswa.php';
        });
    </script>";
    echo "</body></html>";
    exit();
}

function showError($message) {
    echo "<!DOCTYPE html><html><head>";
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "</head><body>";
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '$message'
        }).then(() => {
            window.history.back();
        });
    </script>";
    echo "</body></html>";
    exit();
}
?>