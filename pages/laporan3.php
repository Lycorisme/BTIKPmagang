<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data lowongan dengan statistik lamaran
$query = "SELECT l.*, u.nama as nama_mentor,
          (SELECT COUNT(*) FROM lamaran WHERE lowongan_id = l.id) as total_pelamar,
          (SELECT COUNT(*) FROM lamaran WHERE lowongan_id = l.id AND status = 'diterima') as total_diterima
          FROM lowongan l 
          JOIN users u ON l.mentor_id = u.id 
          ORDER BY l.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Lowongan Magang</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Rekap Lowongan Magang & Pelamar</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Judul Lowongan</th>
                            <th>Mentor</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Total Pelamar</th>
                            <th>Diterima</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $total_lowongan = 0;
                        $total_pelamar_all = 0;
                        $total_diterima_all = 0;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $total_lowongan++;
                            $total_pelamar_all += $row['total_pelamar'];
                            $total_diterima_all += $row['total_diterima'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <strong><?= $row['judul'] ?></strong><br>
                                <small class="text-muted"><?= substr($row['deskripsi'], 0, 80) ?>...</small>
                            </td>
                            <td><?= $row['nama_mentor'] ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?><br>
                                s/d<br>
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'open'): ?>
                                    <span class="badge bg-success">Open</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $row['total_pelamar'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= $row['total_diterima'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5">TOTAL</th>
                            <th class="text-center"><?= $total_pelamar_all ?></th>
                            <th class="text-center"><?= $total_diterima_all ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Ringkasan:</h6>
                <ul>
                    <li>Total Lowongan: <strong><?= $total_lowongan ?></strong></li>
                    <li>Total Pelamar: <strong><?= $total_pelamar_all ?></strong></li>
                    <li>Total yang Diterima: <strong><?= $total_diterima_all ?></strong></li>
                    <li>Rata-rata Pelamar per Lowongan: <strong><?= $total_lowongan > 0 ? round($total_pelamar_all / $total_lowongan, 2) : 0 ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>