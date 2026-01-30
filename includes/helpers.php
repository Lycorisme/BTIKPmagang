<?php
/**
 * Helper Functions
 * Fungsi-fungsi pembantu untuk aplikasi
 */

/**
 * Mendapatkan base URL aplikasi
 * Otomatis mendeteksi localhost atau hosting
 */
function getBaseUrl() {
    if (defined('BASE_URL')) {
        return BASE_URL;
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Deteksi apakah di hosting atau localhost
    if (strpos($host, 'kesug.com') !== false || strpos($host, 'infinityfree') !== false) {
        return $protocol . '://' . $host;
    } else {
        return $protocol . '://' . $host . '/magang-app';
    }
}

/**
 * Mendapatkan path relatif ke root
 */
function getRelativePath() {
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
    $depth = substr_count($scriptPath, '/') - 1;
    
    if ($depth <= 1) {
        return './';
    }
    
    return str_repeat('../', $depth - 1);
}

/**
 * Format tanggal Indonesia
 */
function formatTanggalIndo($tanggal, $format = 'full') {
    if (empty($tanggal)) return '-';
    
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    $d = date('d', $timestamp);
    $m = (int)date('m', $timestamp);
    $y = date('Y', $timestamp);
    
    switch ($format) {
        case 'short':
            return $d . '/' . $m . '/' . $y;
        case 'medium':
            return $d . ' ' . substr($bulan[$m], 0, 3) . ' ' . $y;
        case 'full':
        default:
            return $d . ' ' . $bulan[$m] . ' ' . $y;
    }
}

/**
 * Format waktu
 */
function formatWaktu($waktu) {
    if (empty($waktu)) return '-';
    return date('H:i', strtotime($waktu));
}

/**
 * Generate nomor sertifikat
 */
function generateNomorSertifikat($userId, $tahun = null) {
    $tahun = $tahun ?? date('Y');
    $bulan = date('m');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return "BTIKP/MAGANG/{$tahun}/{$bulan}/{$userId}-{$random}";
}

/**
 * Sanitize filename untuk upload
 */
function sanitizeFilename($filename) {
    $info = pathinfo($filename);
    $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $info['filename']);
    $ext = strtolower($info['extension'] ?? '');
    return $name . '.' . $ext;
}

/**
 * Cek apakah sedang dalam mode production
 */
function isProduction() {
    return defined('IS_PRODUCTION') && IS_PRODUCTION === true;
}

/**
 * Debug helper (hanya tampil di development)
 */
function debug($data, $die = false) {
    if (!isProduction()) {
        echo '<pre style="background:#1e1e1e;color:#f1f1f1;padding:15px;border-radius:5px;overflow:auto;">';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }
}
?>
