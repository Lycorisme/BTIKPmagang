<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Data biodata peserta magang
    $no_hp = isset($_POST['no_hp']) ? mysqli_real_escape_string($conn, $_POST['no_hp']) : '';
    $jenis_kelamin = isset($_POST['jenis_kelamin']) ? mysqli_real_escape_string($conn, $_POST['jenis_kelamin']) : '';
    $tanggal_lahir = isset($_POST['tanggal_lahir']) ? mysqli_real_escape_string($conn, $_POST['tanggal_lahir']) : '';
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, $_POST['alamat']) : '';
    $jenis_instansi = isset($_POST['jenis_instansi']) ? mysqli_real_escape_string($conn, $_POST['jenis_instansi']) : '';
    $nama_instansi = isset($_POST['nama_instansi']) ? mysqli_real_escape_string($conn, $_POST['nama_instansi']) : '';
    $jurusan = isset($_POST['jurusan']) ? mysqli_real_escape_string($conn, $_POST['jurusan']) : '';
    $semester_kelas = isset($_POST['semester_kelas']) ? mysqli_real_escape_string($conn, $_POST['semester_kelas']) : '';
    $nim_nis = isset($_POST['nim_nis']) ? mysqli_real_escape_string($conn, $_POST['nim_nis']) : '';
    
    // Validasi role - hanya peserta_magang yang bisa daftar sendiri
    $valid_roles = ['peserta_magang'];
    if (!in_array($role, $valid_roles)) {
        showError('Registrasi Gagal!', 'Registrasi hanya tersedia untuk Peserta Magang/PKL');
        exit();
    }
    
    // Validasi input kosong
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        showError('Registrasi Gagal!', 'Semua field harus diisi');
        exit();
    }
    
    // Validasi biodata peserta magang
    if ($role == 'peserta_magang') {
        if (empty($no_hp) || empty($jenis_kelamin) || empty($tanggal_lahir) || empty($alamat) ||
            empty($jenis_instansi) || empty($nama_instansi) || empty($jurusan) || 
            empty($semester_kelas) || empty($nim_nis)) {
            showError('Registrasi Gagal!', 'Semua data diri dan asal instansi pendidikan wajib diisi');
            exit();
        }
    }
    
    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        showError('Registrasi Gagal!', 'Format email tidak valid');
        exit();
    }
    
    // Validasi panjang password
    if (strlen($password) < 6) {
        showError('Registrasi Gagal!', 'Password minimal 6 karakter');
        exit();
    }
    
    // Validasi password
    if ($password !== $confirm_password) {
        showError('Registrasi Gagal!', 'Password dan konfirmasi password tidak cocok');
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
        showError('Registrasi Gagal!', 'Email sudah terdaftar');
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
        
        // Jika role peserta_magang, insert ke tabel peserta_magang
        if ($role == 'peserta_magang') {
            $peserta_query = "INSERT INTO peserta_magang 
                (user_id, no_hp, jenis_kelamin, tanggal_lahir, alamat, jenis_instansi, nama_instansi, jurusan, semester_kelas, nim_nis, status_biodata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'lengkap')";
            $peserta_stmt = mysqli_prepare($conn, $peserta_query);
            mysqli_stmt_bind_param($peserta_stmt, "isssssssss", 
                $user_id, $no_hp, $jenis_kelamin, $tanggal_lahir, $alamat, 
                $jenis_instansi, $nama_instansi, $jurusan, $semester_kelas, $nim_nis);
            mysqli_stmt_execute($peserta_stmt);
            mysqli_stmt_close($peserta_stmt);
        }
        
        // Jika role mentor, insert ke tabel mentors (untuk admin yang membuat mentor)
        if ($role == 'mentor') {
            $keahlian = mysqli_real_escape_string($conn, $_POST['keahlian']);
            $bio = mysqli_real_escape_string($conn, $_POST['bio']);
            
            // Validasi keahlian dan bio untuk mentor
            if (empty($keahlian) || empty($bio)) {
                mysqli_rollback($conn);
                showError('Registrasi Gagal!', 'Keahlian dan bio wajib diisi untuk mentor');
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
        
        showSuccess('Registrasi Berhasil!', 'Silakan login dengan akun Anda');
        
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        showError('Registrasi Gagal!', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
    exit();
} else {
    header('Location: ../pages/register.php');
    exit();
}

// Function untuk menampilkan error
function showError($title, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title ?></title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'error',
                title: '<?= $title ?>',
                text: '<?= $message ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../pages/register.php';
            });
        </script>
    </body>
    </html>
    <?php
}

// Function untuk menampilkan success
function showSuccess($title, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $title ?></title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <script>
            Swal.fire({
                icon: 'success',
                title: '<?= $title ?>',
                text: '<?= $message ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '../pages/login.php';
            });
        </script>
    </body>
    </html>
    <?php
}
?>