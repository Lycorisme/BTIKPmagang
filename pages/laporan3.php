<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data absensi
$query = "SELECT a.*, u.nama 
          FROM absensi a 
          JOIN users u ON a.user_id = u.id 
          WHERE u.role = 'peserta_magang'
          ORDER BY a.tanggal DESC, a.jam_masuk DESC";
$result = mysqli_query($conn, $query);

// Statistik Hari Ini
$today = date('Y-m-d');
$query_today = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
                FROM absensi WHERE tanggal = '$today'";
$stats_today = mysqli_fetch_assoc(mysqli_query($conn, $query_today));
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-check"></i> Laporan Absensi Peserta</h2>
        <div class="btn-group">
            <a href="../process/export_pdf.php?type=laporan3" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak Laporan
            </button>
        </div>
    </div>
    
    <!-- Statistik Hari Ini -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $stats_today['hadir'] ?? 0 ?></h3>
                    <p class="mb-0">Hadir Hari Ini</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?= $stats_today['izin'] ?? 0 ?></h3>
                    <p class="mb-0">Izin Hari Ini</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= $stats_today['sakit'] ?? 0 ?></h3>
                    <p class="mb-0">Sakit Hari Ini</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3><?= $stats_today['alpha'] ?? 0 ?></h3>
                    <p class="mb-0">Alpha Hari Ini</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Rekap Data Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
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
                        <?php 
                        $no = 1;
                        if(mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-' ?></td>
                            <td><?= $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-' ?></td>
                            <td class="text-center">
                                <?php
                                $badge_color = 'secondary';
                                switch($row['status']) {
                                    case 'hadir': $badge_color = 'success'; break;
                                    case 'izin': $badge_color = 'warning'; break;
                                    case 'sakit': $badge_color = 'info'; break;
                                    case 'alpha': $badge_color = 'danger'; break;
                                }
                                ?>
                                <span class="badge bg-<?= $badge_color ?>"><?= ucfirst($row['status']) ?></span>
                            </td>
                            <td><?= $row['keterangan'] ?? '-' ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data absensi</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>