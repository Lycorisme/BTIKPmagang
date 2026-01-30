<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data pendaftaran yang sudah selesai (Berhak Sertifikat)
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

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-award"></i> Laporan Sertifikat Magang</h2>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
    
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Daftar Penerima Sertifikat (Magang Selesai)</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Peserta di bawah ini telah menyelesaikan magang dan berhak mendapatkan sertifikat.
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Instansi & Jurusan</th>
                            <th>Mentor Pembimbing</th>
                            <th>Periode Magang</th>
                            <th>Tgl Selesai</th>
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
                                <strong><?= $row['nama_peserta'] ?></strong><br>
                                <small class="text-muted"><?= $row['email'] ?></small>
                            </td>
                            <td>
                                <?= $row['nama_instansi'] ?><br>
                                <small class="text-muted"><?= $row['jurusan'] ?></small>
                            </td>
                            <td><?= $row['nama_mentor'] ?? '-' ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_selesai_aktual'])) ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada peserta yang menyelesaikan magang</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Total Sertifikat Diterbitkan: <?= mysqli_num_rows($result) ?></h6>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>