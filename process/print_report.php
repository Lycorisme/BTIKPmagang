<?php
/**
 * Print Report - Alternative to PDF Download
 * Works 100% on free hosting (no memory issues)
 * User dapat print dan save as PDF dari browser
 */
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

checkLogin();
checkRole(['admin']);

$report_type = $_GET['type'] ?? '';

// Get site settings (dengan fallback jika tabel tidak ada)
$site_name = 'Sistem Magang';

// Cek apakah tabel settings ada
$check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'settings'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    $settings_query = "SELECT * FROM settings WHERE id = 1";
    $settings_result = @mysqli_query($conn, $settings_query);
    if ($settings_result && $settings = mysqli_fetch_assoc($settings_result)) {
        $site_name = $settings['site_name'] ?? 'Sistem Magang';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - <?= htmlspecialchars($site_name) ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
        }
        
        * {
            font-family: 'Segoe UI', Arial, sans-serif;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            font-size: 12px;
        }
        
        .print-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header h2 {
            margin: 8px 0 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .header .date {
            margin-top: 10px;
            font-size: 11px;
            color: #888;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background: linear-gradient(135deg, #4a5568, #2d3748);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
        }
        
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f1f5f9;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .summary h4 {
            margin: 0 0 10px;
            color: #333;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            color: white;
        }
        
        .badge-success { background: #48bb78; }
        .badge-warning { background: #ed8936; }
        .badge-danger { background: #f56565; }
        .badge-info { background: #4299e1; }
        .badge-primary { background: #667eea; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-box {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            color: white;
        }
        
        .stat-box h3 {
            margin: 0;
            font-size: 24px;
        }
        
        .stat-box p {
            margin: 5px 0 0;
            font-size: 11px;
            opacity: 0.9;
        }
        
        /* Toolbar */
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .toolbar-spacer {
            height: 70px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .btn-print {
            background: white;
            color: #667eea;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <!-- Toolbar (tidak akan muncul saat print) -->
    <div class="toolbar no-print">
        <button onclick="window.print()" class="btn btn-print">
            🖨️ Cetak / Save as PDF
        </button>
        <a href="javascript:history.back()" class="btn btn-back">
            ← Kembali
        </a>
    </div>
    <div class="toolbar-spacer no-print"></div>

    <div class="print-container">
        <?php
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
                ?>
                <div class="header">
                    <h1><?= htmlspecialchars($site_name) ?></h1>
                    <h2>Laporan Peserta Magang</h2>
                    <p class="date">Dicetak pada: <?= date('d F Y H:i:s') ?></p>
                </div>
                
                <table>
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
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['nama_instansi'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['jurusan'] ?? '-') ?></td>
                            <td class="text-center"><?= $row['total_hadir'] ?></td>
                            <td class="text-center"><?= $row['total_jurnal'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <h4>Total Peserta: <?= $no - 1 ?></h4>
                </div>
                <?php
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
                ?>
                <div class="header">
                    <h1><?= htmlspecialchars($site_name) ?></h1>
                    <h2>Laporan Data Mentor</h2>
                    <p class="date">Dicetak pada: <?= date('d F Y H:i:s') ?></p>
                </div>
                
                <?php if (!$result || mysqli_num_rows($result) == 0): ?>
                <div style="text-align: center; padding: 40px; background: #f0f0f0; border-radius: 8px;">
                    <p>Belum ada data mentor terdaftar.</p>
                </div>
                <?php else: ?>
                <table>
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
                    <tbody>
                        <?php 
                        $no = 1; 
                        $total_bimbingan_all = 0;
                        $total_selesai_all = 0;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $total_bimbingan_all += $row['total_bimbingan'] ?? 0;
                            $total_selesai_all += $row['bimbingan_selesai'] ?? 0;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['keahlian'] ?? '-') ?></td>
                            <td class="text-center">
                                <span class="badge <?= (isset($row['status_open']) && $row['status_open']) ? 'badge-success' : 'badge-warning' ?>">
                                    <?= (isset($row['status_open']) && $row['status_open']) ? 'Menerima' : 'Tutup' ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $row['total_bimbingan'] ?? 0 ?></td>
                            <td class="text-center"><?= $row['bimbingan_aktif'] ?? 0 ?></td>
                            <td class="text-center"><?= $row['bimbingan_selesai'] ?? 0 ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <h4>Ringkasan:</h4>
                    <ul>
                        <li>Total Mentor: <strong><?= $no - 1 ?></strong></li>
                        <li>Total Bimbingan: <strong><?= $total_bimbingan_all ?></strong></li>
                        <li>Total Bimbingan Selesai: <strong><?= $total_selesai_all ?></strong></li>
                    </ul>
                </div>
                <?php endif; ?>
                <?php
                break;
                
            case 'laporan3':
                // Laporan Absensi
                $query = "SELECT a.*, u.nama 
                          FROM absensi a 
                          JOIN users u ON a.user_id = u.id 
                          WHERE u.role = 'peserta_magang'
                          ORDER BY a.tanggal DESC, a.jam_masuk DESC
                          LIMIT 100";
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
                $stats = mysqli_fetch_assoc(mysqli_query($conn, $query_today));
                ?>
                <div class="header">
                    <h1><?= htmlspecialchars($site_name) ?></h1>
                    <h2>Laporan Absensi Peserta</h2>
                    <p class="date">Dicetak pada: <?= date('d F Y H:i:s') ?></p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-box" style="background: #48bb78;">
                        <h3><?= $stats['hadir'] ?? 0 ?></h3>
                        <p>Hadir Hari Ini</p>
                    </div>
                    <div class="stat-box" style="background: #ed8936;">
                        <h3><?= $stats['izin'] ?? 0 ?></h3>
                        <p>Izin Hari Ini</p>
                    </div>
                    <div class="stat-box" style="background: #4299e1;">
                        <h3><?= $stats['sakit'] ?? 0 ?></h3>
                        <p>Sakit Hari Ini</p>
                    </div>
                    <div class="stat-box" style="background: #f56565;">
                        <h3><?= $stats['alpha'] ?? 0 ?></h3>
                        <p>Alpha Hari Ini</p>
                    </div>
                </div>
                
                <table>
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
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): 
                            $badge_class = 'badge-info';
                            switch($row['status']) {
                                case 'hadir': $badge_class = 'badge-success'; break;
                                case 'izin': $badge_class = 'badge-warning'; break;
                                case 'sakit': $badge_class = 'badge-info'; break;
                                case 'alpha': $badge_class = 'badge-danger'; break;
                            }
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-' ?></td>
                            <td><?= $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-' ?></td>
                            <td class="text-center">
                                <span class="badge <?= $badge_class ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php
                break;
                
            case 'laporan4':
                // Laporan Jurnal
                $query = "SELECT j.*, u.nama as nama_mahasiswa, um.nama as nama_mentor 
                          FROM jurnal j 
                          JOIN users u ON j.user_id = u.id 
                          LEFT JOIN users um ON j.mentor_id = um.id 
                          ORDER BY j.tanggal DESC
                          LIMIT 100";
                $result = mysqli_query($conn, $query);
                
                $total_jurnal = mysqli_num_rows($result);
                $query_reviewed = "SELECT COUNT(*) as total FROM jurnal WHERE feedback IS NOT NULL";
                $total_reviewed = mysqli_fetch_assoc(mysqli_query($conn, $query_reviewed))['total'];
                ?>
                <div class="header">
                    <h1><?= htmlspecialchars($site_name) ?></h1>
                    <h2>Laporan Aktivitas Jurnal</h2>
                    <p class="date">Dicetak pada: <?= date('d F Y H:i:s') ?></p>
                </div>
                
                <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="stat-box" style="background: #667eea;">
                        <h3><?= $total_jurnal ?></h3>
                        <p>Total Jurnal</p>
                    </div>
                    <div class="stat-box" style="background: #48bb78;">
                        <h3><?= $total_reviewed ?></h3>
                        <p>Sudah Direview</p>
                    </div>
                    <div class="stat-box" style="background: #ed8936;">
                        <h3><?= $total_jurnal - $total_reviewed ?></h3>
                        <p>Pending Review</p>
                    </div>
                </div>
                
                <table>
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
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['nama_mentor'] ?? '-') ?></td>
                            <td><?= htmlspecialchars(substr($row['aktivitas'], 0, 50)) ?>...</td>
                            <td class="text-center">
                                <span class="badge <?= $row['feedback'] ? 'badge-success' : 'badge-warning' ?>">
                                    <?= $row['feedback'] ? 'Sudah' : 'Belum' ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($row['nilai']): ?>
                                    <span class="badge badge-primary"><?= $row['nilai'] ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php
                break;
                
            case 'laporan5':
                // Laporan Sertifikat
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
                ?>
                <div class="header">
                    <h1><?= htmlspecialchars($site_name) ?></h1>
                    <h2>Laporan Sertifikat Magang</h2>
                    <p class="date">Dicetak pada: <?= date('d F Y H:i:s') ?></p>
                </div>
                
                <div class="summary" style="background: #c6f6d5;">
                    <strong>✓</strong> Peserta di bawah ini telah menyelesaikan magang dan berhak mendapatkan sertifikat.
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Email</th>
                            <th>Instansi</th>
                            <th>Mentor</th>
                            <th>Periode</th>
                            <th>Tgl Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_peserta']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['nama_instansi'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nama_mentor'] ?? '-') ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_selesai_aktual'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <h4>Total Sertifikat Diterbitkan: <?= $no - 1 ?></h4>
                </div>
                <?php
                break;
                
            default:
                echo '<div class="alert" style="background: #fed7d7; padding: 20px; border-radius: 8px; text-align: center;">';
                echo '<h4>⚠️ Laporan tidak ditemukan</h4>';
                echo '<p>Silakan pilih jenis laporan yang valid.</p>';
                echo '</div>';
                break;
        }
        ?>
        
        <div class="footer">
            Generated by <?= htmlspecialchars($site_name) ?> - <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <script>
        // Hint untuk user
        console.log('💡 Tip: Untuk save sebagai PDF, tekan Ctrl+P lalu pilih "Save as PDF" sebagai printer.');
    </script>
</body>
</html>
