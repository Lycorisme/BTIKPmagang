<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data mahasiswa dengan statistik lamaran
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM lamaran WHERE user_id = u.id) as total_lamaran,
          (SELECT COUNT(*) FROM lamaran WHERE user_id = u.id AND status = 'diterima') as lamaran_diterima,
          (SELECT COUNT(*) FROM lamaran WHERE user_id = u.id AND status = 'ditolak') as lamaran_ditolak
          FROM users u 
          WHERE u.role = 'mahasiswa' 
          ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Data Mahasiswa</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Rekap Data Mahasiswa & Aktivitas Magang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Terdaftar</th>
                            <th>Total Lamaran</th>
                            <th>Diterima</th>
                            <th>Ditolak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $total_mahasiswa = 0;
                        $total_lamaran_all = 0;
                        $total_diterima_all = 0;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $total_mahasiswa++;
                            $total_lamaran_all += $row['total_lamaran'];
                            $total_diterima_all += $row['lamaran_diterima'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td class="text-center"><?= $row['total_lamaran'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= $row['lamaran_diterima'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger"><?= $row['lamaran_ditolak'] ?></span>
                            </td>
                            <td>
                                <?php if ($row['lamaran_diterima'] > 0): ?>
                                    <span class="badge bg-success">Aktif Magang</span>
                                <?php elseif ($row['total_lamaran'] > 0): ?>
                                    <span class="badge bg-warning text-dark">Sudah Melamar</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Melamar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4">TOTAL</th>
                            <th class="text-center"><?= $total_lamaran_all ?></th>
                            <th class="text-center"><?= $total_diterima_all ?></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Ringkasan:</h6>
                <ul>
                    <li>Total Mahasiswa Terdaftar: <strong><?= $total_mahasiswa ?></strong></li>
                    <li>Total Lamaran Diajukan: <strong><?= $total_lamaran_all ?></strong></li>
                    <li>Total Mahasiswa Diterima Magang: <strong><?= $total_diterima_all ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>