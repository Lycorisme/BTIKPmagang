<?php
/**
 * Konfigurasi Database - Auto Detect Environment
 * Mendukung: Localhost (Laragon/XAMPP) dan InfinityFree Hosting
 */

// Deteksi environment berdasarkan hostname
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Konfigurasi untuk masing-masing environment
if (strpos($host, 'kesug.com') !== false || strpos($host, 'infinityfree') !== false || strpos($host, 'epizy') !== false) {
    // ============ INFINITY FREE HOSTING ============
    define('DB_HOST', 'sql104.infinityfree.com');
    define('DB_USER', 'if0_40670400');
    define('DB_PASS', '4j3gZ4qnq2OB4U');
    define('DB_NAME', 'if0_40670400_magang');
    define('BASE_URL', 'https://tracking-disposisi.kesug.com');
    define('IS_PRODUCTION', true);
} else {
    // ============ LOCALHOST (LARAGON/XAMPP) ============
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'magang');
    define('BASE_URL', 'http://localhost/magang-app');
    define('IS_PRODUCTION', false);
}

// Koneksi menggunakan MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    if (IS_PRODUCTION) {
        die("Maaf, terjadi kesalahan koneksi database. Silakan coba lagi nanti.");
    } else {
        die("Koneksi gagal: " . $conn->connect_error . "<br><br><strong>Pastikan database '" . DB_NAME . "' sudah dibuat!</strong>");
    }
}

// Set charset
$conn->set_charset("utf8mb4");

// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');
?>