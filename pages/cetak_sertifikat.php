<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang', 'admin']); // Allow admin test

$user_id = $_SESSION['user_id'];
$pendaftaran_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'view'; // mode: view (html) or download (pdf)

// Admin override for testing
$admin_override = ($_SESSION['role'] == 'admin');

// Query
$query = "SELECT pd.*, 
          u.nama as nama_peserta, 
          um.nama as nama_mentor,
          pm.jurusan, pm.nama_instansi
          FROM pendaftaran_magang pd 
          JOIN users u ON pd.user_id = u.id 
          LEFT JOIN users um ON pd.mentor_id = um.id 
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id
          WHERE pd.id = '$pendaftaran_id' AND pd.status = 'selesai'";

// Jika bukan admin, harus punya sendiri
if (!$admin_override) {
    $query .= " AND pd.user_id = '$user_id'";
}

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data tidak ditemukan.'); window.location='dashboard_peserta_magang.php';</script>";
    exit();
}

$data = mysqli_fetch_assoc($result);

// CSS for Certificate
$css = "
body { font-family: 'Times New Roman', serif; color: #333; }
.certificate { border: 10px solid #1a4d80; padding: 40px; position: relative; width: 100%; height: 90%; }
.header { text-align: center; margin-bottom: 30px; }
.title { font-size: 48px; font-weight: bold; color: #1a4d80; text-transform: uppercase; margin: 0; }
.subtitle { font-size: 16px; color: #666; text-transform: uppercase; letter-spacing: 2px; }
.content { text-align: center; margin: 40px 0; }
.name { font-size: 36px; font-weight: bold; margin: 20px 0; border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 5px; font-family: Helvetica, sans-serif; }
.text { font-size: 16px; line-height: 1.6; }
.signatures { margin-top: 60px; width: 100%; }
.sig-box { width: 45%; float: left; text-align: center; }
.sig-box.right { float: right; }
.line { border-top: 1px solid #000; width: 80%; margin: 60px auto 5px; }
.logo { width: 80px; margin-bottom: 10px; }
";

// Build HTML for PDF
$html = '
<html>
<head>
    <style>' . $css . '</style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <!-- Jika ingin logo, gunakan path absolut atau base64. Sementara text dulu agar aman -->
            <div class="title">SERTIFIKAT</div>
            <div class="subtitle">Nomor: ' . str_pad($data['id'], 5, '0', STR_PAD_LEFT) . '/MAGANG/BTIKP/' . date('Y') . '</div>
        </div>
        
        <div class="content">
            <p class="text">Diberikan kepada:</p>
            <div class="name">' . strtoupper($data['nama_peserta']) . '</div>
            <p class="text">
                Dari <strong>' . $data['nama_instansi'] . '</strong><br><br>
                Telah sukses menyelesaikan program<br>
                <strong>Praktek Kerja Lapangan (PKL) / Magang</strong><br>
                di BTIKP Provinsi Kalimantan Selatan<br><br>
                Periode: ' . date('d F Y', strtotime($data['tgl_mulai'])) . ' - ' . date('d F Y', strtotime($data['tgl_selesai'])) . '
            </p>
        </div>
        
        <div class="signatures">
            <div class="sig-box">
                <br><br>
                Pembimbing Lapangan
                <div class="line"></div>
                <strong>' . ($data['nama_mentor'] ?? 'Mentor Pembimbing') . '</strong>
            </div>
            <div class="sig-box right">
                Banjarmasin, ' . date('d F Y', strtotime($data['tgl_selesai_aktual'])) . '<br>
                Kepala BTIKP
                <div class="line"></div>
                <strong>NAMA KEPALA DINAS</strong><br>
                NIP. 19283746 564738 1 001
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
</body>
</html>';

if ($mode == 'download') {
    require_once '../includes/pdf_helper.php';
    generatePDF($html, 'Sertifikat_' . str_replace(' ', '_', $data['nama_peserta']) . '.pdf', 'A4', 'landscape');
} else {
    // Preview Mode (HTML)
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Preview Sertifikat</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <style>
            .preview-container {
                max-width: 900px;
                margin: 30px auto;
                box-shadow: 0 0 20px rgba(0,0,0,0.2);
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="container text-center my-4">
            <a href="?id=<?= $pendaftaran_id ?>&mode=download" class="btn btn-danger btn-lg">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <a href="dashboard_peserta_magang.php" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="preview-container bg-white p-5">
            <?= $html ?>
        </div>
    </body>
    </html>
    <?php
}
?>