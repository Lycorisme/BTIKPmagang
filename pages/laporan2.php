<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data mentor dengan statistik
$query = "SELECT u.*, m.keahlian, m.status_open,
          (SELECT COUNT(*) FROM lowongan WHERE mentor_id = u.id) as total_lowongan,
          (SELECT COUNT(*) FROM lowongan WHERE mentor_id = u.id AND status = 'open') as lowongan_aktif,
          (SELECT COUNT(*) FROM lamaran WHERE mentor_id = u.id) as total_lamaran,
          (SELECT COUNT(*) FROM lamaran WHERE mentor_id = u.id AND status = 'diterima') as total_pemagang
          FROM users u 
          JOIN mentors m ON u.id = m.user_id 
          WHERE u.role = 'mentor' 
          ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Data Mentor</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Rekap Data Mentor & Aktivitas Pembimbingan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Mentor</th>
                            <th>Keahlian</th>
                            <th>Status</th>
                            <th>Total Lowongan</th>
                            <th>Lowongan Aktif</th>
                            <th>Total Lamaran</th>
                            <th>Total Pemagang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $total_mentor = 0;
                        $total_lowongan_all = 0;
                        $total_pemagang_all = 0;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $total_mentor++;
                            $total_lowongan_all += $row['total_lowongan'];
                            $total_pemagang_all += $row['total_pemagang'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= $row['nama'] ?><br>
                                <small class="text-muted"><?= $row['email'] ?></small>
                            </td>
                            <td><?= $row['keahlian'] ?></td>
                            <td>
                                <?php if ($row['status_open']): ?>
                                    <span class="badge bg-success">Menerima</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tutup</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $row['total_lowongan'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-info"><?= $row['lowongan_aktif'] ?></span>
                            </td>
                            <td class="text-center"><?= $row['total_lamaran'] ?></td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= $row['total_pemagang'] ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4">TOTAL</th>
                            <th class="text-center"><?= $total_lowongan_all ?></th>
                            <th colspan="2"></th>
                            <th class="text-center"><?= $total_pemagang_all ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Ringkasan:</h6>
                <ul>
                    <li>Total Mentor Terdaftar: <strong><?= $total_mentor ?></strong></li>
                    <li>Total Lowongan Dibuat: <strong><?= $total_lowongan_all ?></strong></li>
                    <li>Total Pemagang Aktif: <strong><?= $total_pemagang_all ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>