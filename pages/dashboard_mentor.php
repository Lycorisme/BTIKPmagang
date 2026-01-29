<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Statistik - hanya peserta yang di-assign ke mentor ini oleh admin
$query_aktif = "SELECT COUNT(*) as total FROM pendaftaran_magang WHERE mentor_id = '$user_id' AND status = 'diterima'";
$result_aktif = mysqli_query($conn, $query_aktif);
$total_aktif = $result_aktif ? mysqli_fetch_assoc($result_aktif)['total'] : 0;

$query_selesai = "SELECT COUNT(*) as total FROM pendaftaran_magang WHERE mentor_id = '$user_id' AND status = 'selesai'";
$result_selesai = mysqli_query($conn, $query_selesai);
$total_selesai = $result_selesai ? mysqli_fetch_assoc($result_selesai)['total'] : 0;

// Jurnal yang perlu direview
$query_jurnal = "SELECT COUNT(*) as total FROM jurnal j 
                 JOIN pendaftaran_magang pd ON j.user_id = pd.user_id 
                 WHERE pd.mentor_id = '$user_id' AND (j.status = 'pending' OR j.status IS NULL)";
$result_jurnal = mysqli_query($conn, $query_jurnal);
$total_jurnal_pending = $result_jurnal ? mysqli_fetch_assoc($result_jurnal)['total'] : 0;

// Peserta yang di-assign ke mentor ini
$query_peserta = "SELECT pd.*, u.nama, u.email, pm.nama_instansi, pm.jurusan
                  FROM pendaftaran_magang pd
                  JOIN users u ON pd.user_id = u.id
                  LEFT JOIN peserta_magang pm ON u.id = pm.user_id
                  WHERE pd.mentor_id = '$user_id' AND pd.status = 'diterima'
                  ORDER BY pd.tgl_mulai DESC";
$result_peserta = mysqli_query($conn, $query_peserta);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-speedometer2"></i> Dashboard Mentor
    </h2>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        Anda hanya dapat melihat peserta magang yang telah ditugaskan oleh Admin kepada Anda.
    </div>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Peserta Magang Aktif</h6>
                            <h2 class="mt-2 mb-0"><?= $total_aktif ?></h2>
                        </div>
                        <i class="bi bi-people display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Jurnal Perlu Review</h6>
                            <h2 class="mt-2 mb-0"><?= $total_jurnal_pending ?></h2>
                        </div>
                        <i class="bi bi-journal-text display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Peserta Selesai</h6>
                            <h2 class="mt-2 mb-0"><?= $total_selesai ?></h2>
                        </div>
                        <i class="bi bi-check-circle display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="peserta_mentor.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-people display-6"></i><br>Lihat Peserta Magang
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="jurnal_monitor.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-journal-check display-6"></i><br>Review Jurnal
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="profile_mentor.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-person-circle display-6"></i><br>Edit Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Daftar Peserta Magang Aktif -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-people"></i> Peserta Magang dalam Bimbingan Anda</h5>
        </div>
        <div class="card-body">
            <?php if ($result_peserta && mysqli_num_rows($result_peserta) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Asal Instansi</th>
                            <th>Jurusan</th>
                            <th>Periode</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result_peserta)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= $row['nama'] ?><br>
                                <small class="text-muted"><?= $row['email'] ?></small>
                            </td>
                            <td><?= $row['nama_instansi'] ?></td>
                            <td><?= $row['jurusan'] ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                            </td>
                            <td>
                                <a href="peserta_mentor.php?user_id=<?= $row['user_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> Belum ada peserta magang yang ditugaskan kepada Anda oleh Admin.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>