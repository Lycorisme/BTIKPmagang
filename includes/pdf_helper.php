<?php
/**
 * PDF Helper - Optimized for Free Hosting (InfinityFree)
 * Dengan error handling dan memory optimization
 */

// Suppress errors dan tangani sendiri
error_reporting(0);
ini_set('display_errors', 0);

// Set memory limit dan execution time jika diizinkan
@ini_set('memory_limit', '256M');
@set_time_limit(120);

// Load autoloader Composer
$autoload_path = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($autoload_path)) {
    die(json_encode([
        'error' => true,
        'message' => 'Composer autoload tidak ditemukan. Jalankan: composer install'
    ]));
}

require_once $autoload_path;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate PDF dengan error handling
 * @param string $html - HTML content
 * @param string $filename - Nama file output
 * @param string $paper - Ukuran kertas (A4, Letter, dll)
 * @param string $orientation - portrait atau landscape
 * @return void
 */
function generatePDF($html, $filename, $paper = 'A4', $orientation = 'portrait') {
    try {
        $options = new Options();
        
        // Optimasi untuk hosting dengan resource terbatas
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false); // Disable untuk hemat memory
        $options->set('isPhpEnabled', false);
        $options->set('isFontSubsettingEnabled', true); // Hemat memory font
        $options->set('defaultFont', 'DejaVu Sans');
        
        // Set temporary folder (gunakan folder yang bisa ditulis)
        $tempDir = dirname(__DIR__) . '/tmp';
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }
        if (is_writable($tempDir)) {
            $options->set('tempDir', $tempDir);
        }
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();
        
        // Clear output buffer sebelum streaming
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Send headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Output PDF
        echo $dompdf->output();
        exit();
        
    } catch (Exception $e) {
        // Log error jika memungkinkan
        $logFile = dirname(__DIR__) . '/tmp/pdf_error.log';
        @file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n", FILE_APPEND);
        
        // Tampilkan pesan error yang user-friendly
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Error PDF</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '</head><body class="bg-light">';
        echo '<div class="container mt-5">';
        echo '<div class="alert alert-danger">';
        echo '<h4><i class="bi bi-exclamation-triangle"></i> Gagal Generate PDF</h4>';
        echo '<p>Terjadi kesalahan saat membuat file PDF. Silakan coba lagi atau hubungi administrator.</p>';
        echo '<hr>';
        echo '<p class="mb-0"><small>Error: ' . htmlspecialchars($e->getMessage()) . '</small></p>';
        echo '</div>';
        echo '<a href="javascript:history.back()" class="btn btn-primary">Kembali</a>';
        echo '</div></body></html>';
        exit();
    }
}

/**
 * Fungsi alternatif: Generate PDF sederhana tanpa library berat
 * Menggunakan print CSS sebagai fallback
 */
function generatePrintablePage($html, $title) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head>';
    echo '<title>' . htmlspecialchars($title) . '</title>';
    echo '<style>
        @media print {
            body { margin: 0; padding: 20px; }
            .no-print { display: none !important; }
        }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4a5568; color: white; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .btn { padding: 10px 20px; background: #0d6efd; color: white; border: none; cursor: pointer; border-radius: 5px; text-decoration: none; }
    </style>';
    echo '</head><body>';
    echo '<div class="no-print" style="margin-bottom: 20px; text-align: center;">';
    echo '<button onclick="window.print()" class="btn">🖨️ Cetak / Save as PDF</button> ';
    echo '<a href="javascript:history.back()" class="btn" style="background: #6c757d;">← Kembali</a>';
    echo '</div>';
    echo $html;
    echo '<script>
        // Auto print after 1 second
        // setTimeout(function() { window.print(); }, 1000);
    </script>';
    echo '</body></html>';
    exit();
}
?>
