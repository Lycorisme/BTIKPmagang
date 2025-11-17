<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil semua data jurnal
$query = "SELECT j.*, u.nama as nama_mahasiswa, um.nama as nama_mentor 
          FROM jurnal j 
          JOIN users u ON j.user_id = u.id 
          JOIN users um ON j.mentor_id = um.id 
          ORDER BY j.tanggal DESC";
$result = mysqli_query($conn, $query);

// Statistik jurnal
$total_jurnal = mysqli_num_rows($result);
$query_reviewed = "SELECT COUNT(*) as total FROM jurnal WHERE feedback IS NOT NULL";
$total_reviewed = mysqli_fetch_assoc(mysqli_query($conn, $query_reviewed))['total'];
$total_pending = $total_jurnal - $total_reviewed;

// Rata-rata nilai
$query_avg = "SELECT AVG(nilai) as rata FROM jurnal WHERE nilai IS NOT NULL";
$rata_nilai = mysqli_fetch_assoc(mysqli_query($conn, $query_avg))['rata'];
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Aktivitas Jurnal</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><?= $total_jurnal ?></h3>
                    <p class="mb-0">Total Jurnal</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= $total_reviewed ?></h3>
                    <p class="mb-0">Sudah Direview</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?= $total_pending ?></h3>
                    <p class="mb-0">Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3><?= $rata_nilai ? number_format($rata_nilai, 2) : '-' ?></h3>
                    <p class="mb-0">Rata-rata Nilai</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Detail Aktivitas Jurnal Magang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
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
                        <?php 
                        $no = 1;
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['nama_mahasiswa'] ?></td>
                            <td><?= $row['nama_mentor'] ?></td>
                            <td><?= substr($row['aktivitas'], 0, 60) ?>...</td>
                            <td class="text-center">
                                <?php if ($row['feedback']): ?>
                                    <span class="badge bg-success">Sudah</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Belum</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($row['nilai']): ?>
                                    <span class="badge bg-primary"><?= $row['nilai'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Ringkasan:</h6>
                <ul>
                    <li>Total Jurnal yang Diinput: <strong><?= $total_jurnal ?></strong></li>
                    <li>Jurnal yang Sudah Direview: <strong><?= $total_reviewed ?></strong></li>
                    <li>Jurnal Pending Review: <strong><?= $total_pending ?></strong></li>
                    <li>Persentase Review: <strong><?= $total_jurnal > 0 ? round(($total_reviewed / $total_jurnal) * 100, 2) : 0 ?>%</strong></li>
                    <li>Rata-rata Nilai: <strong><?= $rata_nilai ? number_format($rata_nilai, 2) : 'Belum ada nilai' ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>