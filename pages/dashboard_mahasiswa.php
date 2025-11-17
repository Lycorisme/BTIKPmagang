<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$user_id = $_SESSION['user_id'];

// Hitung statistik
$query_lamaran = "SELECT COUNT(*) as total FROM lamaran WHERE user_id = '$user_id'";
$result_lamaran = mysqli_query($conn, $query_lamaran);
$total_lamaran = mysqli_fetch_assoc($result_lamaran)['total'];

$query_diterima = "SELECT COUNT(*) as total FROM lamaran WHERE user_id = '$user_id' AND status = 'diterima'";
$result_diterima = mysqli_query($conn, $query_diterima);
$total_diterima = mysqli_fetch_assoc($result_diterima)['total'];

$query_proses = "SELECT COUNT(*) as total FROM lamaran WHERE user_id = '$user_id' AND status = 'proses'";
$result_proses = mysqli_query($conn, $query_proses);
$total_proses = mysqli_fetch_assoc($result_proses)['total'];

$query_jurnal = "SELECT COUNT(*) as total FROM jurnal WHERE user_id = '$user_id'";
$result_jurnal = mysqli_query($conn, $query_jurnal);
$total_jurnal = mysqli_fetch_assoc($result_jurnal)['total'];

// Ambil lowongan terbaru
$query_lowongan = "SELECT l.*, u.nama as nama_mentor 
                   FROM lowongan l 
                   JOIN mentors m ON l.mentor_id = m.user_id 
                   JOIN users u ON m.user_id = u.id 
                   WHERE l.status = 'open' 
                   ORDER BY l.id DESC LIMIT 5";
$result_lowongan = mysqli_query($conn, $query_lowongan);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-speedometer2"></i> Dashboard Mahasiswa
    </h2>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Lamaran</h6>
                            <h2 class="mt-2 mb-0"><?= $total_lamaran ?></h2>
                        </div>
                        <i class="bi bi-file-text display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Diterima</h6>
                            <h2 class="mt-2 mb-0"><?= $total_diterima ?></h2>
                        </div>
                        <i class="bi bi-check-circle display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Dalam Proses</h6>
                            <h2 class="mt-2 mb-0"><?= $total_proses ?></h2>
                        </div>
                        <i class="bi bi-clock-history display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Jurnal</h6>
                            <h2 class="mt-2 mb-0"><?= $total_jurnal ?></h2>
                        </div>
                        <i class="bi bi-journal-text display-4"></i>
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
                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="lowongan_list.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search"></i><br>Cari Lowongan
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="lamaran_status.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-list-check"></i><br>Status Lamaran
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="jurnal_mahasiswa.php" class="btn btn-outline-info w-100">
                                <i class="bi bi-journal-plus"></i><br>Isi Jurnal
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="mentor_list.php" class="btn btn-outline-warning w-100">
                                <i class="bi bi-people"></i><br>Lihat Mentor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lowongan Terbaru -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-briefcase-fill"></i> Lowongan Magang Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_lowongan) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Judul Lowongan</th>
                                        <th>Mentor</th>
                                        <th>Periode</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result_lowongan)): ?>
                                    <tr>
                                        <td><?= $row['judul'] ?></td>
                                        <td><?= $row['nama_mentor'] ?></td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                            <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Open</span>
                                        </td>
                                        <td>
                                            <a href="lowongan_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="lowongan_list.php" class="btn btn-primary">Lihat Semua Lowongan</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Belum ada lowongan tersedia saat ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>