<?php
/**
 * Export PDF - Optimized for Free Hosting
 * With comprehensive error handling
 */

// Error handling untuk hosting gratis
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set limits (mungkin diabaikan oleh hosting, tapi coba)
@ini_set('memory_limit', '256M');
@set_time_limit(120);

// Start output buffering untuk error handling
ob_start();

// Custom error handler
function pdfErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorLog = dirname(__DIR__) . '/tmp/pdf_error.log';
    $message = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    @file_put_contents($errorLog, $message, FILE_APPEND);
    return false;
}
set_error_handler('pdfErrorHandler');

// Custom exception handler
function pdfExceptionHandler($e) {
    ob_end_clean();
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error PDF</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="bg-light"><div class="container mt-5">';
    echo '<div class="alert alert-danger">';
    echo '<h4>⚠️ Gagal Generate PDF</h4>';
    echo '<p>Server tidak dapat memproses permintaan PDF saat ini.</p>';
    echo '<hr><p class="mb-0"><small>Detail: ' . htmlspecialchars($e->getMessage()) . '</small></p>';
    echo '</div>';
    echo '<a href="javascript:history.back()" class="btn btn-primary">← Kembali</a>';
    echo '</div></body></html>';
    exit();
}
set_exception_handler('pdfExceptionHandler');

// Shutdown handler untuk fatal errors
function pdfShutdownHandler() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        ob_end_clean();
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Error PDF</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '</head><body class="bg-light"><div class="container mt-5">';
        echo '<div class="alert alert-warning">';
        echo '<h4>⚠️ Server Error</h4>';
        echo '<p>Terjadi kesalahan server saat generate PDF. Kemungkinan penyebab:</p>';
        echo '<ul>';
        echo '<li>Memory limit hosting terlalu rendah</li>';
        echo '<li>Execution time limit tercapai</li>';
        echo '<li>Library DOMPDF membutuhkan resource lebih besar</li>';
        echo '</ul>';
        echo '<p><strong>Solusi:</strong> Gunakan tombol "Cetak Laporan" untuk print langsung dari browser.</p>';
        echo '</div>';
        echo '<a href="javascript:history.back()" class="btn btn-primary">← Kembali</a>';
        echo '</div></body></html>';
    }
}
register_shutdown_function('pdfShutdownHandler');

session_start();

// Check required files exist
$configPath = '../config/database.php';
$authPath = '../includes/auth.php';
$pdfHelperPath = '../includes/pdf_helper.php';

if (!file_exists($configPath)) {
    throw new Exception('Database config not found');
}
if (!file_exists($authPath)) {
    throw new Exception('Auth helper not found');
}
if (!file_exists($pdfHelperPath)) {
    throw new Exception('PDF helper not found');
}

require_once $configPath;
require_once $authPath;
require_once $pdfHelperPath;

checkLogin();
checkRole(['admin']);

$report_type = $_GET['type'] ?? '';

// Get site settings for header (dengan fallback jika tabel tidak ada)
$site_name = 'Sistem Magang';
$logo_path = '../assets/img/logo.png';

// Cek apakah tabel settings ada
$check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $settings_query = "SELECT * FROM settings WHERE id = 1";
    $settings_result = @mysqli_query($conn, $settings_query);
    if ($settings_result && $settings = mysqli_fetch_assoc($settings_result)) {
        $site_name = $settings['site_name'] ?? 'Sistem Magang';
        $logo_path = '../assets/uploads/' . ($settings['logo'] ?? 'logo.png');
    }
}

// Common PDF Header styles
$pdf_styles = '
    <style>
        * { font-family: DejaVu Sans, Arial, sans-serif; }
        body { margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 20px; color: #333; }
        .header h2 { margin: 5px 0; font-size: 16px; color: #666; font-weight: normal; }
        .header p { margin: 5px 0; font-size: 12px; color: #888; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4a5568; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f7fafc; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 10px; font-size: 10px; color: white; }
        .badge-success { background-color: #48bb78; }
        .badge-warning { background-color: #ed8936; }
        .badge-danger { background-color: #f56565; }
        .badge-info { background-color: #4299e1; }
        .badge-primary { background-color: #5a67d8; }
        .stats-row { display: table; width: 100%; margin-bottom: 20px; }
        .stats-box { display: table-cell; text-align: center; padding: 15px; border: 1px solid #ddd; }
        .stats-box h3 { margin: 0; font-size: 24px; }
        .stats-box p { margin: 5px 0 0; font-size: 12px; color: #666; }
        .summary { margin-top: 20px; padding: 15px; background-color: #f7fafc; border-radius: 5px; }
        .summary h4 { margin: 0 0 10px; color: #333; }
        .summary ul { margin: 0; padding-left: 20px; }
        .summary li { margin: 5px 0; }
        .text-center { text-align: center; }
        .text-muted { color: #888; }
    </style>
';

// PDF Header template
function getPdfHeader($title, $site_name) {
    return '
        <div class="header">
            <h1>' . htmlspecialchars($site_name) . '</h1>
            <h2>' . htmlspecialchars($title) . '</h2>
            <p>Dicetak pada: ' . date('d F Y H:i:s') . '</p>
        </div>
    ';
}

// PDF Footer
$pdf_footer = '<div class="footer">Generated by ' . htmlspecialchars($site_name) . ' - ' . date('d/m/Y H:i') . '</div>';

switch ($report_type) {
    case 'laporan1':
        // Laporan Peserta Magang
        $query = "SELECT u.*, pm.nama_instansi, pm.jurusan, pm.jenis_instansi,
                  (SELECT COUNT(*) FROM absensi a WHERE a.user_id = u.id AND a.status = 'hadir') as total_hadir,
                  (SELECT COUNT(*) FROM jurnal j WHERE j.user_id = u.id) as total_jurnal
                  FROM users u 
                  LEFT JOIN peserta_magang pm ON u.id = pm.user_id
                  WHERE u.role = 'peserta_magang' 
                  ORDER BY u.id DESC";
        $result = mysqli_query($conn, $query);
        
        $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>';
        $html .= getPdfHeader('Laporan Peserta Magang', $site_name);
        $html .= '<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peserta</th>
                    <th>Email</th>
                    <th>Asal Instansi</th>
                    <th>Jurusan</th>
                    <th>Hadir</th>
                    <th>Jurnal</th>
                </tr>
            </thead>
            <tbody>';
        
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['nama']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['nama_instansi'] ?? '-') . '</td>
                <td>' . htmlspecialchars($row['jurusan'] ?? '-') . '</td>
                <td class="text-center">' . $row['total_hadir'] . '</td>
                <td class="text-center">' . $row['total_jurnal'] . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '<div class="summary"><h4>Total Peserta: ' . ($no - 1) . '</h4></div>';
        $html .= $pdf_footer . '</body></html>';
        
        generatePDF($html, 'Laporan_Peserta_Magang_' . date('Y-m-d') . '.pdf');
        break;

    case 'laporan2':
        // Laporan Data Mentor
        $query = "SELECT u.*, m.keahlian, m.status_open,
                  (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id) as total_bimbingan,
                  (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id AND pm.status = 'diterima') as bimbingan_aktif,
                  (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id AND pm.status = 'selesai') as bimbingan_selesai
                  FROM users u 
                  LEFT JOIN mentors m ON u.id = m.user_id 
                  WHERE u.role = 'mentor' 
                  ORDER BY u.id DESC";
        $result = mysqli_query($conn, $query);
        
        // Fallback jika query gagal
        if (!$result) {
            $query = "SELECT * FROM users WHERE role = 'mentor' ORDER BY id DESC";
            $result = mysqli_query($conn, $query);
        }
        
        $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>';
        $html .= getPdfHeader('Laporan Data Mentor', $site_name);
        $html .= '<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mentor</th>
                    <th>Email</th>
                    <th>Keahlian</th>
                    <th>Status</th>
                    <th>Total Bimbingan</th>
                    <th>Aktif</th>
                    <th>Selesai</th>
                </tr>
            </thead>
            <tbody>';
        
        $no = 1;
        $total_mentor = 0;
        $total_bimbingan_all = 0;
        $total_selesai_all = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $total_mentor++;
            $total_bimbingan_all += $row['total_bimbingan'] ?? 0;
            $total_selesai_all += $row['bimbingan_selesai'] ?? 0;
            $status_badge = (isset($row['status_open']) && $row['status_open']) ? '<span class="badge badge-success">Menerima</span>' : '<span class="badge badge-warning">Tutup</span>';
            
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['nama']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['keahlian'] ?? '-') . '</td>
                <td class="text-center">' . $status_badge . '</td>
                <td class="text-center">' . ($row['total_bimbingan'] ?? 0) . '</td>
                <td class="text-center">' . ($row['bimbingan_aktif'] ?? 0) . '</td>
                <td class="text-center">' . ($row['bimbingan_selesai'] ?? 0) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '<div class="summary">
            <h4>Ringkasan:</h4>
            <ul>
                <li>Total Mentor Terdaftar: <strong>' . $total_mentor . '</strong></li>
                <li>Total Bimbingan: <strong>' . $total_bimbingan_all . '</strong></li>
                <li>Total Bimbingan Selesai: <strong>' . $total_selesai_all . '</strong></li>
            </ul>
        </div>';
        $html .= $pdf_footer . '</body></html>';
        
        generatePDF($html, 'Laporan_Data_Mentor_' . date('Y-m-d') . '.pdf');
        break;

    case 'laporan3':
        // Laporan Absensi Peserta
        $query = "SELECT a.*, u.nama 
                  FROM absensi a 
                  JOIN users u ON a.user_id = u.id 
                  WHERE u.role = 'peserta_magang'
                  ORDER BY a.tanggal DESC, a.jam_masuk DESC";
        $result = mysqli_query($conn, $query);
        
        // Statistics
        $today = date('Y-m-d');
        $query_today = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                        SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
                        FROM absensi WHERE tanggal = '$today'";
        $stats_today = mysqli_fetch_assoc(mysqli_query($conn, $query_today));
        
        $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>';
        $html .= getPdfHeader('Laporan Absensi Peserta', $site_name);
        
        // Stats boxes
        $html .= '<table style="margin-bottom: 20px;">
            <tr>
                <td style="text-align: center; background-color: #48bb78; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . ($stats_today['hadir'] ?? 0) . '</strong><br>Hadir Hari Ini
                </td>
                <td style="text-align: center; background-color: #ed8936; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . ($stats_today['izin'] ?? 0) . '</strong><br>Izin Hari Ini
                </td>
                <td style="text-align: center; background-color: #4299e1; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . ($stats_today['sakit'] ?? 0) . '</strong><br>Sakit Hari Ini
                </td>
                <td style="text-align: center; background-color: #f56565; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . ($stats_today['alpha'] ?? 0) . '</strong><br>Alpha Hari Ini
                </td>
            </tr>
        </table>';
        
        $html .= '<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Peserta</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>';
        
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $badge_class = 'badge-info';
            switch($row['status']) {
                case 'hadir': $badge_class = 'badge-success'; break;
                case 'izin': $badge_class = 'badge-warning'; break;
                case 'sakit': $badge_class = 'badge-info'; break;
                case 'alpha': $badge_class = 'badge-danger'; break;
            }
            
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                <td>' . htmlspecialchars($row['nama']) . '</td>
                <td>' . ($row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-') . '</td>
                <td>' . ($row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-') . '</td>
                <td class="text-center"><span class="badge ' . $badge_class . '">' . ucfirst($row['status']) . '</span></td>
                <td>' . htmlspecialchars($row['keterangan'] ?? '-') . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= $pdf_footer . '</body></html>';
        
        generatePDF($html, 'Laporan_Absensi_Peserta_' . date('Y-m-d') . '.pdf');
        break;

    case 'laporan4':
        // Laporan Aktivitas Jurnal
        $query = "SELECT j.*, u.nama as nama_mahasiswa, um.nama as nama_mentor 
                  FROM jurnal j 
                  JOIN users u ON j.user_id = u.id 
                  LEFT JOIN users um ON j.mentor_id = um.id 
                  ORDER BY j.tanggal DESC";
        $result = mysqli_query($conn, $query);
        
        // Statistics
        $total_jurnal = mysqli_num_rows($result);
        $query_reviewed = "SELECT COUNT(*) as total FROM jurnal WHERE feedback IS NOT NULL";
        $total_reviewed = mysqli_fetch_assoc(mysqli_query($conn, $query_reviewed))['total'];
        $total_pending = $total_jurnal - $total_reviewed;
        
        $query_avg = "SELECT AVG(nilai) as rata FROM jurnal WHERE nilai IS NOT NULL";
        $rata_nilai = mysqli_fetch_assoc(mysqli_query($conn, $query_avg))['rata'];
        
        $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>';
        $html .= getPdfHeader('Laporan Aktivitas Jurnal', $site_name);
        
        // Stats boxes
        $html .= '<table style="margin-bottom: 20px;">
            <tr>
                <td style="text-align: center; background-color: #5a67d8; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . $total_jurnal . '</strong><br>Total Jurnal
                </td>
                <td style="text-align: center; background-color: #48bb78; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . $total_reviewed . '</strong><br>Sudah Direview
                </td>
                <td style="text-align: center; background-color: #ed8936; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . $total_pending . '</strong><br>Pending Review
                </td>
                <td style="text-align: center; background-color: #4299e1; color: white; padding: 15px;">
                    <strong style="font-size: 18px;">' . ($rata_nilai ? number_format($rata_nilai, 2) : '-') . '</strong><br>Rata-rata Nilai
                </td>
            </tr>
        </table>';
        
        $html .= '<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Mahasiswa</th>
                    <th>Mentor</th>
                    <th>Aktivitas</th>
                    <th>Feedback</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>';
        
        $no = 1;
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            $feedback_badge = $row['feedback'] ? '<span class="badge badge-success">Sudah</span>' : '<span class="badge badge-warning">Belum</span>';
            $nilai = $row['nilai'] ? '<span class="badge badge-primary">' . $row['nilai'] . '</span>' : '-';
            
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . date('d/m/Y', strtotime($row['tanggal'])) . '</td>
                <td>' . htmlspecialchars($row['nama_mahasiswa']) . '</td>
                <td>' . htmlspecialchars($row['nama_mentor'] ?? '-') . '</td>
                <td>' . htmlspecialchars(substr($row['aktivitas'], 0, 50)) . '...</td>
                <td class="text-center">' . $feedback_badge . '</td>
                <td class="text-center">' . $nilai . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '<div class="summary">
            <h4>Ringkasan:</h4>
            <ul>
                <li>Total Jurnal yang Diinput: <strong>' . $total_jurnal . '</strong></li>
                <li>Jurnal yang Sudah Direview: <strong>' . $total_reviewed . '</strong></li>
                <li>Jurnal Pending Review: <strong>' . $total_pending . '</strong></li>
            </ul>
        </div>';
        $html .= $pdf_footer . '</body></html>';
        
        generatePDF($html, 'Laporan_Aktivitas_Jurnal_' . date('Y-m-d') . '.pdf');
        break;

    case 'laporan5':
        // Laporan Sertifikat Magang
        $query = "SELECT pd.*, 
                  u.nama as nama_peserta, u.email,
                  pm.nama_instansi, pm.jurusan,
                  mentor.nama as nama_mentor
                  FROM pendaftaran_magang pd
                  JOIN users u ON pd.user_id = u.id
                  LEFT JOIN peserta_magang pm ON u.id = pm.user_id
                  LEFT JOIN users mentor ON pd.mentor_id = mentor.id
                  WHERE pd.status = 'selesai'
                  ORDER BY pd.tgl_selesai_aktual DESC, pd.id DESC";
        $result = mysqli_query($conn, $query);
        
        $html = '<!DOCTYPE html><html><head>' . $pdf_styles . '</head><body>';
        $html .= getPdfHeader('Laporan Sertifikat Magang', $site_name);
        
        $html .= '<div style="background-color: #c6f6d5; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            <strong>✓</strong> Peserta di bawah ini telah menyelesaikan magang dan berhak mendapatkan sertifikat.
        </div>';
        
        $html .= '<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peserta</th>
                    <th>Email</th>
                    <th>Instansi</th>
                    <th>Jurusan</th>
                    <th>Mentor</th>
                    <th>Periode Magang</th>
                    <th>Tgl Selesai</th>
                </tr>
            </thead>
            <tbody>';
        
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['nama_peserta']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['nama_instansi'] ?? '-') . '</td>
                <td>' . htmlspecialchars($row['jurusan'] ?? '-') . '</td>
                <td>' . htmlspecialchars($row['nama_mentor'] ?? '-') . '</td>
                <td>' . date('d/m/Y', strtotime($row['tgl_mulai'])) . ' - ' . date('d/m/Y', strtotime($row['tgl_selesai'])) . '</td>
                <td>' . date('d/m/Y', strtotime($row['tgl_selesai_aktual'])) . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        $html .= '<div class="summary"><h4>Total Sertifikat Diterbitkan: ' . ($no - 1) . '</h4></div>';
        $html .= $pdf_footer . '</body></html>';
        
        generatePDF($html, 'Laporan_Sertifikat_Magang_' . date('Y-m-d') . '.pdf');
        break;

    default:
        header('Location: ../pages/dashboard_admin.php');
        exit;
}
?>
