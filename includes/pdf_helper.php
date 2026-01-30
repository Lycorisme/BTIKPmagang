<?php
// Load autoloader Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Fungsi helper untuk generate PDF
function generatePDF($html, $filename, $paper = 'A4', $orientation = 'portrait') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Penting untuk load gambar (logo)
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper($paper, $orientation);
    $dompdf->render();
    
    // Stream file ke browser (Download)
    $dompdf->stream($filename, ["Attachment" => true]);
    exit();
}
?>
