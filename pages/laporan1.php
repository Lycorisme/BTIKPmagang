<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data peserta magang dengan detail institusi
$query = "SELECT u.*, pm.nama_instansi, pm.jurusan, pm.jenis_instansi,
          (SELECT COUNT(*) FROM absensi a WHERE a.user_id = u.id AND a.status = 'hadir') as total_hadir,
          (SELECT COUNT(*) FROM jurnal j WHERE j.user_id = u.id) as total_jurnal
          FROM users u 
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id
          WHERE u.role = 'peserta_magang' 
          ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Laporan Peserta Magang</h2>
        <div class="btn-group">
            <a href="../process/export_pdf.php?type=laporan1" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak Laporan
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Data Lengkap Peserta Magang</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Info Kontak</th>
                            <th>Institusi Update</th>
                            <th>Asal Instansi</th>
                            <th>Statistik</th>
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
                            <td>
                                <strong><?= $row['nama'] ?></strong><br>
                                <small class="text-muted">ID: <?= $row['id'] ?></small>
                            </td>
                            <td>
                                <i class="bi bi-envelope"></i> <?= $row['email'] ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                            </td>
                            <td>
                                <strong><?= $row['nama_instansi'] ?? '-' ?></strong><br>
                                <small class="text-muted">
                                    <?= $row['jurusan'] ?? '-' ?> 
                                    (<?= $row['jenis_instansi'] ?? '-' ?>)
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-success" title="Total Hadir">Hadir: <?= $row['total_hadir'] ?></span>
                                <span class="badge bg-info" title="Total Jurnal">Jurnal: <?= $row['total_jurnal'] ?></span>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data peserta magang</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Total Peserta: <?= mysqli_num_rows($result) ?></h6>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>