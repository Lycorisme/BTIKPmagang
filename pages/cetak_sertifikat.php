<?php
session_start();
require_once '../config/database.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$user_id = $_SESSION['user_id'];
$lamaran_id = isset($_GET['lamaran_id']) ? $_GET['lamaran_id'] : 0;

// Ambil data lamaran yang diterima
$query = "SELECT l.*, lo.judul as judul_lowongan, lo.tgl_mulai, lo.tgl_selesai,
          u.nama as nama_mahasiswa, um.nama as nama_mentor 
          FROM lamaran l 
          JOIN lowongan lo ON l.lowongan_id = lo.id 
          JOIN users u ON l.user_id = u.id 
          JOIN users um ON l.mentor_id = um.id 
          WHERE l.id = '$lamaran_id' AND l.user_id = '$user_id' AND l.status = 'diterima'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: lamaran_status.php');
    exit();
}

$data = mysqli_fetch_assoc($result);

// Hitung durasi magang
$tgl_mulai = new DateTime($data['tgl_mulai']);
$tgl_selesai = new DateTime($data['tgl_selesai']);
$durasi = $tgl_mulai->diff($tgl_selesai);
$durasi_text = $durasi->days . ' hari';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        .certificate {
            width: 100%;
            max-width: 800px;
            margin: 30px auto;
            padding: 60px;
            border: 10px solid #0d6efd;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .certificate-title {
            font-size: 48px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 10px;
        }
        .certificate-subtitle {
            font-size: 18px;
            color: #6c757d;
        }
        .certificate-body {
            text-align: center;
            margin: 40px 0;
        }
        .recipient-name {
            font-size: 36px;
            font-weight: bold;
            color: #212529;
            margin: 20px 0;
            text-decoration: underline;
        }
        .certificate-text {
            font-size: 16px;
            line-height: 1.8;
            color: #495057;
            margin: 20px 0;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-around;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 2px solid #212529;
            margin: 50px auto 10px;
        }
        .ornament {
            font-size: 48px;
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print text-center my-4">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> Cetak / Download PDF
            </button>
            <a href="lamaran_status.php" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="certificate">
            <div class="certificate-header">
                <div class="ornament">★</div>
                <h1 class="certificate-title">SERTIFIKAT</h1>
                <p class="certificate-subtitle">PROGRAM MAGANG</p>
            </div>
            
            <div class="certificate-body">
                <p class="certificate-text">Diberikan kepada:</p>
                
                <h2 class="recipient-name"><?= $data['nama_mahasiswa'] ?></h2>
                
                <p class="certificate-text">
                    Telah menyelesaikan program magang dengan judul<br>
                    <strong>"<?= $data['judul_lowongan'] ?>"</strong><br>
                    selama <strong><?= $durasi_text ?></strong><br>
                    periode <?= date('d F Y', strtotime($data['tgl_mulai'])) ?> sampai <?= date('d F Y', strtotime($data['tgl_selesai'])) ?>
                </p>
                
                <p class="certificate-text">
                    Di bawah bimbingan mentor:<br>
                    <strong><?= $data['nama_mentor'] ?></strong>
                </p>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong><?= $data['nama_mentor'] ?></strong><br>Mentor</p>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <p><strong>Administrator</strong><br>Sistem Magang</p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    Diterbitkan pada: <?= date('d F Y') ?><br>
                    No. Sertifikat: CERT-<?= str_pad($lamaran_id, 6, '0', STR_PAD_LEFT) ?>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>