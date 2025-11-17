<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$lowongan_id = isset($_GET['id']) ? $_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Ambil detail lowongan
$query = "SELECT l.*, u.nama as nama_mentor, u.email as email_mentor, m.keahlian, m.bio 
          FROM lowongan l 
          JOIN mentors m ON l.mentor_id = m.user_id 
          JOIN users u ON m.user_id = u.id 
          WHERE l.id = '$lowongan_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: lowongan_list.php');
    exit();
}

$lowongan = mysqli_fetch_assoc($result);

// Cek apakah sudah pernah melamar
$check_query = "SELECT * FROM lamaran WHERE user_id = '$user_id' AND lowongan_id = '$lowongan_id'";
$check_result = mysqli_query($conn, $check_query);
$sudah_melamar = mysqli_num_rows($check_result) > 0;
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="lowongan_list.php">Lowongan</a></li>
            <li class="breadcrumb-item active">Detail Lowongan</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h3><?= $lowongan['judul'] ?></h3>
                        <span class="badge bg-<?= $lowongan['status'] == 'open' ? 'success' : 'secondary' ?> fs-6">
                            <?= ucfirst($lowongan['status']) ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mt-4">Deskripsi Lowongan</h5>
                    <p class="text-justify"><?= nl2br($lowongan['deskripsi']) ?></p>
                    
                    <h5 class="mt-4">Periode Magang</h5>
                    <p>
                        <i class="bi bi-calendar-check text-info"></i>
                        <strong>Mulai:</strong> <?= date('d F Y', strtotime($lowongan['tgl_mulai'])) ?><br>
                        <i class="bi bi-calendar-x text-danger"></i>
                        <strong>Selesai:</strong> <?= date('d F Y', strtotime($lowongan['tgl_selesai'])) ?>
                    </p>
                    
                    <?php if ($sudah_melamar): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Anda sudah melamar di lowongan ini. 
                            <a href="lamaran_status.php">Cek status lamaran Anda</a>
                        </div>
                    <?php else: ?>
                        <?php if ($lowongan['status'] == 'open'): ?>
                            <button class="btn btn-primary btn-lg w-100 mt-3" data-bs-toggle="modal" data-bs-target="#applyModal">
                                <i class="bi bi-send-fill"></i> Lamar Sekarang
                            </button>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i> Lowongan ini sudah ditutup
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Info Mentor -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Mentor</h5>
                </div>
                <div class="card-body text-center">
                    <i class="bi bi-person-circle display-4 text-primary"></i>
                    <h5 class="mt-3"><?= $lowongan['nama_mentor'] ?></h5>
                    <p class="text-muted"><?= $lowongan['email_mentor'] ?></p>
                    
                    <div class="text-start mt-3">
                        <strong>Keahlian:</strong>
                        <p><?= $lowongan['keahlian'] ?></p>
                        
                        <strong>Bio:</strong>
                        <p><?= $lowongan['bio'] ?></p>
                    </div>
                    
                    <a href="mentor_list.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-people"></i> Lihat Semua Mentor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lamar -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-send-fill"></i> Lamar Lowongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/lamaran_process.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="lowongan_id" value="<?= $lowongan_id ?>">
                    <input type="hidden" name="mentor_id" value="<?= $lowongan['mentor_id'] ?>">
                    
                    <div class="alert alert-info">
                        <strong>Lowongan:</strong> <?= $lowongan['judul'] ?><br>
                        <strong>Mentor:</strong> <?= $lowongan['nama_mentor'] ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file_cv" class="form-label">Upload CV (PDF, max 2MB)</label>
                        <input type="file" class="form-control" id="file_cv" name="file_cv" accept=".pdf" required>
                        <div class="form-text">Format: PDF, Ukuran maksimal: 2MB</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Lamaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>