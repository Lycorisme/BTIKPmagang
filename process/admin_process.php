<?php
session_start();
require_once '../config/database.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';

// ============= MAHASISWA =============
// CREATE MAHASISWA
if ($action == 'create_mahasiswa') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'mahasiswa')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Mahasiswa berhasil ditambahkan'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Email sudah terdaftar'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE MAHASISWA
if ($action == 'update_mahasiswa') {
    $user_id = $_POST['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data mahasiswa berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate data'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// DELETE MAHASISWA
if ($action == 'delete_mahasiswa') {
    $user_id = $_POST['user_id'];
    
    $query = "DELETE FROM users WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Mahasiswa berhasil dihapus'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menghapus data'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// ============= MENTOR =============
// CREATE MENTOR
if ($action == 'create_mentor') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    
    $query = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$password', 'mentor')";
    
    if (mysqli_query($conn, $query)) {
        $user_id = mysqli_insert_id($conn);
        
        $query_mentor = "INSERT INTO mentors (user_id, keahlian, bio, status_open) VALUES ('$user_id', '$keahlian', '$bio', 1)";
        mysqli_query($conn, $query_mentor);
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Mentor berhasil ditambahkan'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Email sudah terdaftar'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE MENTOR
if ($action == 'update_mentor') {
    $user_id = $_POST['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $status_open = $_POST['status_open'];
    
    $query_user = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$user_id'";
    $query_mentor = "UPDATE mentors SET keahlian = '$keahlian', bio = '$bio', status_open = '$status_open' WHERE user_id = '$user_id'";
    
    if (mysqli_query($conn, $query_user) && mysqli_query($conn, $query_mentor)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data mentor berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate data'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// DELETE MENTOR
if ($action == 'delete_mentor') {
    $user_id = $_POST['user_id'];
    
    mysqli_query($conn, "DELETE FROM mentors WHERE user_id = '$user_id'");
    $query = "DELETE FROM users WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Mentor berhasil dihapus'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menghapus data'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// ============= LOWONGAN =============
// CREATE LOWONGAN
if ($action == 'create_lowongan') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $mentor_id = $_POST['mentor_id'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    
    $query = "INSERT INTO lowongan (judul, deskripsi, mentor_id, tgl_mulai, tgl_selesai, status) 
              VALUES ('$judul', '$deskripsi', '$mentor_id', '$tgl_mulai', '$tgl_selesai', 'open')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lowongan berhasil ditambahkan'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menambahkan lowongan'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE LOWONGAN
if ($action == 'update_lowongan') {
    $lowongan_id = $_POST['lowongan_id'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $mentor_id = $_POST['mentor_id'];
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $status = $_POST['status'];
    
    $query = "UPDATE lowongan SET judul = '$judul', deskripsi = '$deskripsi', mentor_id = '$mentor_id', 
              tgl_mulai = '$tgl_mulai', tgl_selesai = '$tgl_selesai', status = '$status' 
              WHERE id = '$lowongan_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lowongan berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat mengupdate lowongan'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// DELETE LOWONGAN
if ($action == 'delete_lowongan') {
    $lowongan_id = $_POST['lowongan_id'];
    
    $query = "DELETE FROM lowongan WHERE id = '$lowongan_id'";
    
    if (mysqli_query($conn, $query)) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Lowongan berhasil dihapus'
            }).then(() => {
                window.location.href = '../pages/dashboard_admin.php';
            });
        </script>";
    } else {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan saat menghapus lowongan'
            }).then(() => {
                window.history.back();
            });
        </script>";
    }
}

// UPDATE PROFILE MAHASISWA (untuk mahasiswa edit profil sendiri)
if ($action == 'update_profile_mahasiswa') {
    $user_id = $_POST['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $query = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/profile_mahasiswa.php';
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

// CHANGE PASSWORD (untuk mahasiswa/mentor)
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
                window.location.href = '../pages/profile_mahasiswa.php';
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

// UPDATE PROFILE ADMIN (untuk admin edit profil sendiri)
if ($action == 'update_profile_admin') {
    $user_id = $_POST['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Cek apakah email sudah digunakan user lain
    $check_email = "SELECT id FROM users WHERE email = '$email' AND id != '$user_id'";
    $result_check = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result_check) > 0) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Email sudah digunakan oleh user lain'
            }).then(() => {
                window.history.back();
            });
        </script>";
        exit();
    }
    
    $query = "UPDATE users SET nama = '$nama', email = '$email' WHERE id = '$user_id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['nama'] = $nama;
        $_SESSION['email'] = $email;
        
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diupdate'
            }).then(() => {
                window.location.href = '../pages/profile_admin.php';
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
?>
