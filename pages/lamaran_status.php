<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$user_id = $_SESSION['user_id'];

// Ambil semua lamaran mahasiswa
$query = "SELECT l.*, lo.judul as judul_lowongan, u.nama as nama_mentor, lo.tgl_mulai, lo.tgl_selesai 
          FROM lamaran l 
          JOIN lowongan lo ON l.lowongan_id = lo.id 
          JOIN users u ON l.mentor_id = u.id 
          WHERE l.user_id = '$user_id' 
          ORDER BY l.tgl_melamar DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-list-check"></i> Status Lamaran & Histori Magang
    </h2>
    
    <!-- Filter Status -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?status=all" class="btn btn-outline-primary <?= !isset($_GET['status']) || $_GET['status'] == 'all' ? 'active' : '' ?>">
                    Semua
                </a>
                <a href="?status=proses" class="btn btn-outline-warning <?= isset($_GET['status']) && $_GET['status'] == 'proses' ? 'active' : '' ?>">
                    Proses
                </a>
                <a href="?status=diterima" class="btn btn-outline-success <?= isset($_GET['status']) && $_GET['status'] == 'diterima' ? 'active' : '' ?>">
                    Diterima
                </a>
                <a href="?status=ditolak" class="btn btn-outline-danger <?= isset($_GET['status']) && $_GET['status'] == 'ditolak' ? 'active' : '' ?>">
                    Ditolak
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
                                <h5 class="card-title"><?= $row['judul_lowongan'] ?></h5>
                                <p class="mb-2">
                                    <i class="bi bi-person-badge text-primary"></i>
                                    <strong>Mentor:</strong> <?= $row['nama_mentor'] ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar text-info"></i>
                                    <strong>Tanggal Melamar:</strong> <?= date('d F Y', strtotime($row['tgl_melamar'])) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar-check text-success"></i>
                                    <strong>Periode:</strong> 
                                    <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                    <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($row['status'] == 'proses'): ?>
                                    <span class="badge bg-warning text-dark fs-6 mb-2">
                                        <i class="bi bi-clock-history"></i> Sedang Diproses
                                    </span>
                                <?php elseif ($row['status'] == 'diterima'): ?>
                                    <span class="badge bg-success fs-6 mb-2">
                                        <i class="bi bi-check-circle"></i> Diterima
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6 mb-2">
                                        <i class="bi bi-x-circle"></i> Ditolak
                                    </span>
                                <?php endif; ?>
                                
                                <br>
                                
                                <?php if ($row['file_cv']): ?>
                                    <a href="../assets/uploads/<?= $row['file_cv'] ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-file-pdf"></i> Lihat CV
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($row['status'] == 'diterima'): ?>
                                    <a href="cetak_sertifikat.php?lamaran_id=<?= $row['id'] ?>" class="btn btn-sm btn-success mt-2">
                                        <i class="bi bi-download"></i> Sertifikat
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Anda belum memiliki lamaran. 
            <a href="lowongan_list.php">Cari lowongan sekarang</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>