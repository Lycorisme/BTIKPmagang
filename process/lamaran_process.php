<?php
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// CREATE - Melamar lowongan
if ($action == 'create') {
    $user_id = $_SESSION['user_id'];
    $lowongan_id = $_POST['lowongan_id'];
    $mentor_id = $_POST['mentor_id'];
    $tgl_melamar = date('Y-m-d');
    
    // Upload CV
    $file_cv = '';
    if (isset($_FILES['file_cv']) && $_FILES['file_cv']['error'] == 0) {
        $file_name = $_FILES['file_cv']['name'];
        $file_tmp = $_FILES['file_cv']['tmp_name'];
        $file_size = $_FILES['file_cv']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext == 'pdf' && $file_size <= 2097152) { // 2MB
            $new_file_name = 'CV_' . $user_id . '_' . time() . '.pdf';
            $upload_path = '../assets/uploads/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $file_cv = $new_file_name;
            }
        }
    }
    
    $query = "INSERT INTO lamaran (user_id, lowongan_id, mentor_id, tgl_melamar, file_cv, status) 
              VALUES ('$user_id', '$lowongan_id', '$mentor_id', '$tgl_melamar', '$file_cv', 'proses')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lamaran Anda berhasil dikirim'
            }).then(() => {
                window.location.href = '../pages/lamaran_status.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengirim lamaran'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE STATUS - Untuk mentor
if ($action == 'update_status') {
    $lamaran_id = $_POST['lamaran_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE lamaran SET status = '$status' WHERE id = '$lamaran_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status lamaran berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/pemagang_list.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate status'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}
?>