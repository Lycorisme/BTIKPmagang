<?php
session_start();
require_once '../config/database.php';
include '../includes/auth.php';

checkLogin();
// Allow both mahasiswa/peserta_magang and admin/mentor if needed, but primarily participants
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];
$pendaftaran_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data pendaftaran magang yang sudah selesai
// Gunakan LEFT JOIN untuk mentor jaga-jaga jika mentor dihapus/null
$query = "SELECT pd.*, 
          u.nama as nama_peserta, 
          um.nama as nama_mentor,
          pm.jurusan, pm.nama_instansi
          FROM pendaftaran_magang pd 
          JOIN users u ON pd.user_id = u.id 
          LEFT JOIN users um ON pd.mentor_id = um.id 
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id
          WHERE pd.id = '$pendaftaran_id' AND pd.user_id = '$user_id' AND pd.status = 'selesai'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    // Redirect jika tidak ditemukan atau status bukan selesai
    echo "<script>alert('Sertifikat tidak tersedia atau magang belum selesai.'); window.location='dashboard_peserta_magang.php';</script>";
    exit();
}

$data = mysqli_fetch_assoc($result);

// Hitung durasi magang
$tgl_mulai = new DateTime($data['tgl_mulai']);
$tgl_selesai = new DateTime($data['tgl_selesai']); // Gunakan tgl_selesai rencana, atau tgl_selesai_aktual jika ada
if (!empty($data['tgl_selesai_aktual'])) {
    $tgl_selesai = new DateTime($data['tgl_selesai_aktual']);
}
$durasi = $tgl_mulai->diff($tgl_selesai);
$durasi_text = $durasi->days . ' hari';
// Atau hitung bulan
$months = $durasi->m + ($durasi->y * 12);
if ($months > 0) {
    $durasi_text = $months . ' Bulan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Magang - <?= $data['nama_peserta'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
        .certificate {
            width: 100%;
            max-width: 900px;
            margin: 30px auto;
            padding: 50px;
            position: relative;
            border: 15px solid #1a4d80;
            background: #fff;
            color: #333;
            font-family: 'Times New Roman', serif;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .certificate-inner {
            border: 2px solid #ddd;
            padding: 30px;
            position: relative;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .certificate-title {
            font-size: 56px;
            font-weight: bold;
            color: #1a4d80;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 5px;
        }
        .certificate-subtitle {
            font-size: 18px;
            color: #666;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .certificate-body {
            text-align: center;
            margin: 40px 0;
        }
        .recipient-name {
            font-size: 42px;
            font-weight: bold;
            color: #000;
            margin: 20px 0;
            font-family: 'Arial', sans-serif;
            border-bottom: 1px solid #ccc;
            display: inline-block;
            padding-bottom: 10px;
        }
        .certificate-text {
            font-size: 18px;
            line-height: 1.8;
            color: #444;
            margin: 20px 0;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            padding: 0 50px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            width: 200px;
            border-top: 1px solid #000;
            margin: 70px auto 10px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 20px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            width: 400px;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print text-center my-4">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="bi bi-printer"></i> Cetak / Simpan PDF
            </button>
            <a href="dashboard_peserta_magang.php" class="btn btn-secondary btn-lg">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="certificate">
            <img src="../assets/img/logo.png" class="watermark" alt="Logo">
            <div class="certificate-inner">
                <div class="certificate-header">
                    <img src="../assets/img/logo.png" class="logo" alt="Logo BTIKP">
                    <h1 class="certificate-title">SERTIFIKAT</h1>
                    <p class="certificate-subtitle">Nomor: <?= str_pad($data['id'], 5, '0', STR_PAD_LEFT) ?>/MAGANG/BTIKP/<?= date('Y') ?></p>
                </div>
                
                <div class="certificate-body">
                    <p class="certificate-text">Diberikan kepada:</p>
                    
                    <h2 class="recipient-name"><?= $data['nama_peserta'] ?></h2>
                    
                    <p class="certificate-text">
                        Dari <strong><?= $data['nama_instansi'] ?></strong><br>
                        Telah sukses menyelesaikan program <strong>Praktek Kerja Lapangan (PKL) / Magang</strong><br>
                        di <strong>BTIKP Provinsi Kalimantan Selatan</strong><br>
                        Periode: <strong><?= date('d F Y', strtotime($data['tgl_mulai'])) ?></strong> sampai <strong><?= date('d F Y', strtotime($data['tgl_selesai'])) ?></strong>
                    </p>
                </div>
                
                <div class="signature-section">
                    <div class="signature-box">
                        <br><br>
                        <p>Pembimbing Lapangan</p>
                        <div class="signature-line"></div>
                        <p><strong><?= $data['nama_mentor'] ?? '_____________________' ?></strong></p>
                    </div>
                    <div class="signature-box">
                        <p>Banjarmasin, <?= date('d F Y', strtotime($data['tgl_selesai_aktual'] ?? date('Y-m-d'))) ?></p>
                        <p>Kepala BTIKP</p>
                        <div class="signature-line"></div>
                        <p><strong>NAMA KEPALA DINAS</strong><br>NIP. 12345678 123456 1 001</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>