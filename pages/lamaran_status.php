<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$user_id = $_SESSION['user_id'];

// Filter berdasarkan status
$filter_query = "";
if (isset($_GET['status']) && $_GET['status'] != 'all') {
    $status = mysqli_real_escape_string($conn, $_GET['status']);
    $filter_query = " AND l.status = '$status'";
}

// Ambil semua lamaran mahasiswa
$query = "SELECT l.*, lo.judul as judul_lowongan, u.nama as nama_mentor, lo.tgl_mulai, lo.tgl_selesai 
          FROM lamaran l 
          JOIN lowongan lo ON l.lowongan_id = lo.id 
          JOIN users u ON l.mentor_id = u.id 
          WHERE l.user_id = '$user_id' $filter_query
          ORDER BY l.tgl_melamar DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-list-check"></i> Daftar Pendaftaran & Lamaran Magang
    </h2>
    
    <!-- Filter Status -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?status=all" class="btn btn-outline-primary <?= !isset($_GET['status']) || $_GET['status'] == 'all' ? 'active' : '' ?>">
                    <i class="bi bi-list"></i> Semua
                </a>
                <a href="?status=proses" class="btn btn-outline-warning <?= isset($_GET['status']) && $_GET['status'] == 'proses' ? 'active' : '' ?>">
                    <i class="bi bi-clock-history"></i> Proses
                </a>
                <a href="?status=diterima" class="btn btn-outline-success <?= isset($_GET['status']) && $_GET['status'] == 'diterima' ? 'active' : '' ?>">
                    <i class="bi bi-check-circle"></i> Diterima
                </a>
                <a href="?status=ditolak" class="btn btn-outline-danger <?= isset($_GET['status']) && $_GET['status'] == 'ditolak' ? 'active' : '' ?>">
                    <i class="bi bi-x-circle"></i> Ditolak
                </a>
            </div>
        </div>
    </div>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="card-title mb-3">
                                    <i class="bi bi-briefcase text-primary"></i> 
                                    <?= htmlspecialchars($row['judul_lowongan']) ?>
                                </h5>
                                <p class="mb-2">
                                    <i class="bi bi-person-badge text-primary"></i>
                                    <strong>Mentor:</strong> <?= htmlspecialchars($row['nama_mentor']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar text-info"></i>
                                    <strong>Tanggal Melamar:</strong> <?= date('d F Y', strtotime($row['tgl_melamar'])) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar-check text-success"></i>
                                    <strong>Periode Magang:</strong> 
                                    <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                    <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($row['status'] == 'proses'): ?>
                                    <span class="badge bg-warning text-dark fs-6 mb-3">
                                        <i class="bi bi-clock-history"></i> Sedang Diproses
                                    </span>
                                <?php elseif ($row['status'] == 'diterima'): ?>
                                    <span class="badge bg-success fs-6 mb-3">
                                        <i class="bi bi-check-circle"></i> Diterima
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6 mb-3">
                                        <i class="bi bi-x-circle"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                                
                                <br>
                                
                                <div class="btn-group-vertical w-100" role="group">
                                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </button>
                                    
                                    <?php if ($row['file_cv']): ?>
                                        <a href="../assets/uploads/<?= $row['file_cv'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-file-pdf"></i> Lihat CV
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($row['status'] == 'diterima'): ?>
                                        <a href="cetak_sertifikat.php?lamaran_id=<?= $row['id'] ?>" class="btn btn-success btn-sm" target="_blank">
                                            <i class="bi bi-download"></i> Download Sertifikat
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Detail Lamaran -->
            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-list-check"></i> Detail Lamaran
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Lowongan:</strong></div>
                                <div class="col-md-8"><?= htmlspecialchars($row['judul_lowongan']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Mentor:</strong></div>
                                <div class="col-md-8"><?= htmlspecialchars($row['nama_mentor']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Tanggal Melamar:</strong></div>
                                <div class="col-md-8"><?= date('d F Y', strtotime($row['tgl_melamar'])) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Periode Magang:</strong></div>
                                <div class="col-md-8">
                                    <?= date('d F Y', strtotime($row['tgl_mulai'])) ?> sampai 
                                    <?= date('d F Y', strtotime($row['tgl_selesai'])) ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>Status:</strong></div>
                                <div class="col-md-8">
                                    <?php if ($row['status'] == 'proses'): ?>
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="bi bi-clock-history"></i> Sedang Diproses
                                        </span>
                                    <?php elseif ($row['status'] == 'diterima'): ?>
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle"></i> Diterima
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger fs-6">
                                            <i class="bi bi-x-circle"></i> Ditolak
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($row['file_cv']): ?>
                            <div class="row mb-3">
                                <div class="col-md-4"><strong>CV Anda:</strong></div>
                                <div class="col-md-8">
                                    <a href="../assets/uploads/<?= $row['file_cv'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-pdf"></i> Lihat/Download CV
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] == 'diterima'): ?>
                            <hr>
                            <div class="alert alert-success">
                                <h6><i class="bi bi-check-circle"></i> Selamat! Lamaran Anda Diterima</h6>
                                <p class="mb-0">Anda dapat mulai mengisi jurnal magang dan mendapatkan sertifikat setelah menyelesaikan program magang.</p>
                            </div>
                            <?php elseif ($row['status'] == 'proses'): ?>
                            <hr>
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-clock-history"></i> Menunggu Review</h6>
                                <p class="mb-0">Lamaran Anda sedang direview oleh mentor. Harap menunggu konfirmasi lebih lanjut.</p>
                            </div>
                            <?php else: ?>
                            <hr>
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-x-circle"></i> Lamaran Ditolak</h6>
                                <p class="mb-0">Maaf, lamaran Anda ditolak. Anda dapat mencoba melamar lowongan lain yang tersedia.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <?php if ($row['status'] == 'diterima'): ?>
                                <a href="cetak_sertifikat.php?lamaran_id=<?= $row['id'] ?>" class="btn btn-success" target="_blank">
                                    <i class="bi bi-download"></i> Download Sertifikat
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            <?php if (isset($_GET['status']) && $_GET['status'] != 'all'): ?>
                Anda belum memiliki lamaran dengan status "<?= ucfirst($_GET['status']) ?>".
            <?php else: ?>
                Anda belum memiliki lamaran. 
            <?php endif; ?>
            <a href="lowongan_list.php" class="alert-link">Cari lowongan sekarang</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>