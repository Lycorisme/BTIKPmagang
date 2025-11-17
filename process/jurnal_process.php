<?php
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// CREATE - Tambah jurnal baru
if ($action == 'create') {
    $user_id = $_SESSION['user_id'];
    $mentor_id = $_POST['mentor_id'];
    $tanggal = $_POST['tanggal'];
    $aktivitas = mysqli_real_escape_string($conn, $_POST['aktivitas']);
    
    // Upload file pendukung
    $file_penunjang = '';
    if (isset($_FILES['file_penunjang']) && $_FILES['file_penunjang']['error'] == 0) {
        $file_name = $_FILES['file_penunjang']['name'];
        $file_tmp = $_FILES['file_penunjang']['tmp_name'];
        $file_size = $_FILES['file_penunjang']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_ext) && $file_size <= 2097152) { // 2MB
            $new_file_name = 'JURNAL_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = '../assets/uploads/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $file_penunjang = $new_file_name;
            }
        }
    }
    
    $query = "INSERT INTO jurnal (user_id, mentor_id, tanggal, aktivitas, file_penunjang) 
              VALUES ('$user_id', '$mentor_id', '$tanggal', '$aktivitas', '$file_penunjang')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Jurnal berhasil disimpan'
            }).then(() => {
                window.location.href = '../pages/jurnal_mahasiswa.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menyimpan jurnal'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE FEEDBACK - Untuk mentor
if ($action == 'update_feedback') {
    $jurnal_id = $_POST['jurnal_id'];
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    $nilai = isset($_POST['nilai']) ? $_POST['nilai'] : null;
    
    if ($nilai) {
        $query = "UPDATE jurnal SET feedback = '$feedback', nilai = '$nilai' WHERE id = '$jurnal_id'";
    } else {
        $query = "UPDATE jurnal SET feedback = '$feedback' WHERE id = '$jurnal_id'";
    }
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Feedback berhasil diberikan'
            }).then(() => {
                window.location.href = '../pages/jurnal_monitor.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menyimpan feedback'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}
?>