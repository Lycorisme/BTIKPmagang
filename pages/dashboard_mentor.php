<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Statistik
$query_lowongan = "SELECT COUNT(*) as total FROM lowongan WHERE mentor_id = '$user_id' AND status = 'open'";
$result_lowongan = mysqli_query($conn, $query_lowongan);
$total_lowongan = mysqli_fetch_assoc($result_lowongan)['total'];

$query_lamaran = "SELECT COUNT(*) as total FROM lamaran WHERE mentor_id = '$user_id' AND status = 'proses'";
$result_lamaran = mysqli_query($conn, $query_lamaran);
$total_lamaran = mysqli_fetch_assoc($result_lamaran)['total'];

$query_pemagang = "SELECT COUNT(*) as total FROM lamaran WHERE mentor_id = '$user_id' AND status = 'diterima'";
$result_pemagang = mysqli_query($conn, $query_pemagang);
$total_pemagang = mysqli_fetch_assoc($result_pemagang)['total'];

$query_jurnal = "SELECT COUNT(*) as total FROM jurnal WHERE mentor_id = '$user_id' AND feedback IS NULL";
$result_jurnal = mysqli_query($conn, $query_jurnal);
$total_jurnal_pending = mysqli_fetch_assoc($result_jurnal)['total'];

// Lamaran terbaru
$query_lamaran_baru = "SELECT l.*, u.nama as nama_mahasiswa, lo.judul as judul_lowongan 
                       FROM lamaran l 
                       JOIN users u ON l.user_id = u.id 
                       JOIN lowongan lo ON l.lowongan_id = lo.id 
                       WHERE l.mentor_id = '$user_id' AND l.status = 'proses' 
                       ORDER BY l.tgl_melamar DESC LIMIT 5";
$result_lamaran_baru = mysqli_query($conn, $query_lamaran_baru);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-speedometer2"></i> Dashboard Mentor
    </h2>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Lowongan Aktif</h6>
                            <h2 class="mt-2 mb-0"><?= $total_lowongan ?></h2>
                        </div>
                        <i class="bi bi-briefcase display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Lamaran Baru</h6>
                            <h2 class="mt-2 mb-0"><?= $total_lamaran ?></h2>
                        </div>
                        <i class="bi bi-file-earmark-text display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Pemagang</h6>
                            <h2 class="mt-2 mb-0"><?= $total_pemagang ?></h2>
                        </div>
                        <i class="bi bi-people display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Jurnal Pending</h6>
                            <h2 class="mt-2 mb-0"><?= $total_jurnal_pending ?></h2>
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
                        <div class="col-md-4">
                            <a href="pemagang_list.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-people"></i><br>Kelola Pemagang
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="jurnal_monitor.php" class="btn btn-outline-info w-100">
                                <i class="bi bi-journal-check"></i><br>Review Jurnal
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="profile_mentor.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-person-circle"></i><br>Edit Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lamaran Terbaru -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Lamaran Baru</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_lamaran_baru) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mahasiswa</th>
                                        <th>Lowongan</th>
                                        <th>Tanggal Melamar</th>
                                        <th>CV</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($result_lamaran_baru)): ?>
                                    <tr>
                                        <td><?= $row['nama_mahasiswa'] ?></td>
                                        <td><?= $row['judul_lowongan'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['tgl_melamar'])) ?></td>
                                        <td>
                                            <?php if ($row['file_cv']): ?>
                                                <a href="../assets/uploads/<?= $row['file_cv'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-file-pdf"></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $row['id'] ?>">
                                                <i class="bi bi-check-circle"></i> Terima
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $row['id'] ?>">
                                                <i class="bi bi-x-circle"></i> Tolak
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal Terima -->
                                    <div class="modal fade" id="approveModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Terima Lamaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="../process/lamaran_process.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="lamaran_id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="status" value="diterima">
                                                        <p>Terima lamaran dari <strong><?= $row['nama_mahasiswa'] ?></strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success">Ya, Terima</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal Tolak -->
                                    <div class="modal fade" id="rejectModal<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Tolak Lamaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="../process/lamaran_process.php">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="lamaran_id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="status" value="ditolak">
                                                        <p>Tolak lamaran dari <strong><?= $row['nama_mahasiswa'] ?></strong>?</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Tolak</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="pemagang_list.php" class="btn btn-warning">Lihat Semua Lamaran</a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Belum ada lamaran baru saat ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>