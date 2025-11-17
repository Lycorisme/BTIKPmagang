<?php
/**
 * Setup Helper File
 * Jalankan file ini sekali setelah instalasi untuk memastikan folder dan permission sudah benar
 * Akses: http://localhost/magang-app/setup.php
 */

echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Setup Sistem Magang</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body>";
echo "<div class='container my-5'>";
echo "<h2 class='text-center mb-4'>🚀 Setup Sistem Magang</h2>";

$errors = [];
$success = [];

// Cek koneksi database
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-primary text-white'><strong>1. Cek Koneksi Database</strong></div>";
echo "<div class='card-body'>";

if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    
    if ($conn->connect_error) {
        $errors[] = "Koneksi database gagal: " . $conn->connect_error;
        echo "<div class='alert alert-danger'>❌ Koneksi database gagal!</div>";
    } else {
        $success[] = "Koneksi database berhasil";
        echo "<div class='alert alert-success'>✅ Koneksi database berhasil!</div>";
        
        // Cek tabel
        $tables = ['users', 'mentors', 'lowongan', 'lamaran', 'jurnal'];
        $table_check = true;
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                $table_check = false;
                $errors[] = "Tabel '$table' tidak ditemukan";
                echo "<div class='alert alert-warning'>⚠️ Tabel '$table' tidak ditemukan. Harap import database terlebih dahulu.</div>";
            }
        }
        
        if ($table_check) {
            echo "<div class='alert alert-info'>✅ Semua tabel database ditemukan!</div>";
            $success[] = "Semua tabel database tersedia";
        }
    }
} else {
    $errors[] = "File config/database.php tidak ditemukan";
    echo "<div class='alert alert-danger'>❌ File config/database.php tidak ditemukan!</div>";
}

echo "</div></div>";

// Cek dan buat folder uploads
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-success text-white'><strong>2. Setup Folder Uploads</strong></div>";
echo "<div class='card-body'>";

$upload_dir = 'assets/uploads';

if (!file_exists('assets')) {
    if (mkdir('assets', 0777, true)) {
        echo "<div class='alert alert-success'>✅ Folder 'assets' berhasil dibuat</div>";
        $success[] = "Folder assets dibuat";
    } else {
        echo "<div class='alert alert-danger'>❌ Gagal membuat folder 'assets'</div>";
        $errors[] = "Gagal membuat folder assets";
    }
}

if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "<div class='alert alert-success'>✅ Folder '$upload_dir' berhasil dibuat</div>";
        $success[] = "Folder uploads dibuat";
    } else {
        echo "<div class='alert alert-danger'>❌ Gagal membuat folder '$upload_dir'</div>";
        $errors[] = "Gagal membuat folder uploads";
    }
} else {
    echo "<div class='alert alert-info'>✅ Folder '$upload_dir' sudah ada</div>";
    $success[] = "Folder uploads sudah tersedia";
}

// Cek permission
if (is_writable($upload_dir)) {
    echo "<div class='alert alert-success'>✅ Folder '$upload_dir' dapat ditulis (writable)</div>";
    $success[] = "Permission folder uploads OK";
} else {
    echo "<div class='alert alert-warning'>⚠️ Folder '$upload_dir' tidak dapat ditulis. Jalankan: chmod 777 $upload_dir</div>";
    $errors[] = "Permission folder uploads perlu diperbaiki";
}

echo "</div></div>";

// Cek file penting
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-info text-white'><strong>3. Cek File Penting</strong></div>";
echo "<div class='card-body'>";

$important_files = [
    'index.php' => 'Entry point',
    'logout.php' => 'Logout handler',
    'config/database.php' => 'Database config',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
    'includes/auth.php' => 'Authentication',
    'pages/login.php' => 'Login page',
    'pages/register.php' => 'Register page',
    'pages/home.php' => 'Home page',
    'process/login_process.php' => 'Login process',
    'process/register_process.php' => 'Register process'
];

$file_check = true;
foreach ($important_files as $file => $desc) {
    if (file_exists($file)) {
        echo "<div class='text-success'>✅ $file ($desc)</div>";
    } else {
        echo "<div class='text-danger'>❌ $file ($desc) - TIDAK DITEMUKAN</div>";
        $errors[] = "File $file tidak ditemukan";
        $file_check = false;
    }
}

if ($file_check) {
    $success[] = "Semua file penting tersedia";
}

echo "</div></div>";

// Cek PHP Extensions
echo "<div class='card mb-3'>";
echo "<div class='card-header bg-warning text-dark'><strong>4. Cek PHP Extensions</strong></div>";
echo "<div class='card-body'>";

$required_extensions = ['mysqli', 'fileinfo', 'gd'];
$ext_check = true;

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='text-success'>✅ Extension '$ext' aktif</div>";
    } else {
        echo "<div class='text-danger'>❌ Extension '$ext' tidak aktif</div>";
        $errors[] = "Extension $ext tidak aktif";
        $ext_check = false;
    }
}

if ($ext_check) {
    $success[] = "Semua PHP extension tersedia";
}

echo "<div class='mt-3'><strong>PHP Version:</strong> " . phpversion() . "</div>";

echo "</div></div>";

// Summary
echo "<div class='card'>";
echo "<div class='card-header bg-dark text-white'><strong>📋 Ringkasan Setup</strong></div>";
echo "<div class='card-body'>";

if (count($errors) == 0) {
    echo "<div class='alert alert-success'>";
    echo "<h4>🎉 Setup Berhasil!</h4>";
    echo "<p>Semua komponen sistem sudah siap digunakan.</p>";
    echo "<ul>";
    foreach ($success as $s) {
        echo "<li>$s</li>";
    }
    echo "</ul>";
    echo "<hr>";
    echo "<h5>Langkah Selanjutnya:</h5>";
    echo "<ol>";
    echo "<li>Import database menggunakan script SQL yang disediakan</li>";
    echo "<li>Jalankan file insert_data_dummy.sql untuk data testing</li>";
    echo "<li>Akses sistem di: <a href='index.php'>index.php</a></li>";
    echo "<li>Login dengan kredensial yang ada di file insert_data_dummy.sql</li>";
    echo "<li><strong>PENTING:</strong> Hapus file setup.php ini setelah selesai untuk keamanan</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>⚠️ Ada Masalah yang Perlu Diperbaiki</h4>";
    echo "<ul>";
    foreach ($errors as $e) {
        echo "<li>$e</li>";
    }
    echo "</ul>";
    echo "<p class='mb-0'>Silakan perbaiki masalah di atas kemudian refresh halaman ini.</p>";
    echo "</div>";
}

echo "</div></div>";

// Tombol aksi
echo "<div class='text-center mt-4'>";
echo "<a href='setup.php' class='btn btn-primary'>🔄 Refresh Setup</a> ";
echo "<a href='index.php' class='btn btn-success'>🚀 Ke Halaman Utama</a>";
echo "</div>";

echo "</div>"; // container
echo "</body>";
echo "</html>";
?>