<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil semua data lamaran
$query = "SELECT l.*, u.nama as nama_mahasiswa, um.nama as nama_mentor, lo.judul as judul_lowongan 
          FROM lamaran l 
          JOIN users u ON l.user_id = u.id 
          JOIN users um ON l.mentor_id = um.id 
          JOIN lowongan lo ON l.lowongan_id = lo.id 
          ORDER BY l.tgl_melamar DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik berdasarkan status
$query_stats = "SELECT status, COUNT(*) as jumlah FROM lamaran GROUP BY status";
$result_stats = mysqli_query($conn, $query_stats);
$stats = [];
while ($row = mysqli_fetch_assoc($result_stats)) {
    $stats[$row['status']] = $row['jumlah'];
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Lamaran & Status</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <!-- Statistik Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h3><?= isset($stats['proses']) ? $stats['proses'] : 0 ?></h3>
                    <p class="mb-0">Dalam Proses</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3><?= isset($stats['diterima']) ? $stats['diterima'] : 0 ?></h3>
                    <p class="mb-0">Diterima</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3><?= isset($stats['ditolak']) ? $stats['ditolak'] : 0 ?></h3>
                    <p class="mb-0">Ditolak</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Detail Semua Lamaran</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal Melamar</th>
                            <th>Nama Mahasiswa</th>
                            <th>Lowongan</th>
                            <th>Mentor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_melamar'])) ?></td>
                            <td><?= $row['nama_mahasiswa'] ?></td>
                            <td><?= $row['judul_lowongan'] ?></td>
                            <td><?= $row['nama_mentor'] ?></td>
                            <td>
                                <?php if ($row['status'] == 'proses'): ?>
                                    <span class="badge bg-warning text-dark">Proses</span>
                                <?php elseif ($row['status'] == 'diterima'): ?>
                                    <span class="badge bg-success">Diterima</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Ditolak</span>
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
                    <li>Total Lamaran Masuk: <strong><?= array_sum($stats) ?></strong></li>
                    <li>Dalam Proses: <strong><?= isset($stats['proses']) ? $stats['proses'] : 0 ?></strong></li>
                    <li>Diterima: <strong><?= isset($stats['diterima']) ? $stats['diterima'] : 0 ?></strong></li>
                    <li>Ditolak: <strong><?= isset($stats['ditolak']) ? $stats['ditolak'] : 0 ?></strong></li>
                    <li>Tingkat Penerimaan: <strong>
                        <?php 
                        $total = array_sum($stats);
                        $diterima = isset($stats['diterima']) ? $stats['diterima'] : 0;
                        echo $total > 0 ? round(($diterima / $total) * 100, 2) : 0;
                        ?>%
                    </strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>