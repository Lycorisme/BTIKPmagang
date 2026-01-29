<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];

// Cek status pendaftaran magang - harus diterima untuk bisa isi jurnal
$query_pendaftaran = "SELECT * FROM pendaftaran_magang WHERE user_id = '$user_id' AND status = 'diterima' LIMIT 1";
$result_pendaftaran = mysqli_query($conn, $query_pendaftaran);
$pendaftaran = mysqli_fetch_assoc($result_pendaftaran);

if (!$pendaftaran) {
    ?>
    <div class="container my-5">
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle"></i> Akses Ditolak</h5>
            <p>Anda belum diterima sebagai peserta magang. Silakan tunggu konfirmasi dari admin.</p>
            <a href="dashboard_peserta_magang.php" class="btn btn-primary">Kembali ke Dashboard</a>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit();
}

// Process submit jurnal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $aktivitas = mysqli_real_escape_string($conn, $_POST['aktivitas']);
    $file_penunjang = '';
    
    // Upload file jika ada
    if (isset($_FILES['file_penunjang']) && $_FILES['file_penunjang']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $filename = $_FILES['file_penunjang']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = 'jurnal_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = '../assets/uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['file_penunjang']['tmp_name'], $upload_path)) {
                $file_penunjang = $new_filename;
            }
        }
    }
    
    // Cek apakah sudah ada jurnal untuk tanggal tersebut
    $check = mysqli_query($conn, "SELECT id FROM jurnal WHERE user_id = '$user_id' AND tanggal = '$tanggal'");
    
    if (mysqli_num_rows($check) > 0) {
        // Update jurnal yang sudah ada
        $jurnal = mysqli_fetch_assoc($check);
        if (!empty($file_penunjang)) {
            $query = "UPDATE jurnal SET aktivitas = ?, file_penunjang = ?, status = 'pending' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ssi", $aktivitas, $file_penunjang, $jurnal['id']);
        } else {
            $query = "UPDATE jurnal SET aktivitas = ?, status = 'pending' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $aktivitas, $jurnal['id']);
        }
        mysqli_stmt_execute($stmt);
        
        echo "<script>Swal.fire({icon:'success',title:'Berhasil!',text:'Jurnal berhasil diupdate'}).then(()=>location.reload());</script>";
    } else {
        // Insert jurnal baru
        $query = "INSERT INTO jurnal (user_id, tanggal, aktivitas, file_penunjang, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $tanggal, $aktivitas, $file_penunjang);
        mysqli_stmt_execute($stmt);
        
        echo "<script>Swal.fire({icon:'success',title:'Berhasil!',text:'Jurnal berhasil disimpan'}).then(()=>location.reload());</script>";
    }
}

// Ambil riwayat jurnal
$bulan = $_GET['bulan'] ?? date('Y-m');
$query_jurnal = "SELECT * FROM jurnal WHERE user_id = '$user_id' AND tanggal LIKE '$bulan%' ORDER BY tanggal DESC";
$result_jurnal = mysqli_query($conn, $query_jurnal);

// Statistik jurnal
$query_stat = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'dikoreksi' THEN 1 ELSE 0 END) as dikoreksi,
                SUM(CASE WHEN status = 'pending' OR status IS NULL THEN 1 ELSE 0 END) as pending
              FROM jurnal WHERE user_id = '$user_id'";
$result_stat = mysqli_query($conn, $query_stat);
$stat = mysqli_fetch_assoc($result_stat);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-journal-text"></i> Jurnal Aktivitas Magang
    </h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Form Input Jurnal -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Input Jurnal Harian</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">File Penunjang (Opsional)</label>
                                <input type="file" class="form-control" name="file_penunjang" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Format: PDF, JPG, PNG, DOC, DOCX</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Aktivitas/Kegiatan Hari Ini <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="aktivitas" rows="5" required placeholder="Jelaskan aktivitas/kegiatan yang Anda lakukan hari ini selama magang..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Jurnal
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Filter Bulan -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Pilih Bulan</label>
                            <input type="month" class="form-control" name="bulan" value="<?= $bulan ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Riwayat Jurnal -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Riwayat Jurnal - <?= date('F Y', strtotime($bulan . '-01')) ?></h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_jurnal) > 0): ?>
                    <div class="accordion" id="jurnalAccordion">
                        <?php $j = 1; while ($jurnal = mysqli_fetch_assoc($result_jurnal)): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $j > 1 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#jurnal<?= $jurnal['id'] ?>">
                                    <span class="me-3"><?= date('d/m/Y', strtotime($jurnal['tanggal'])) ?></span>
                                    <?php if ($jurnal['status'] == 'disetujui'): ?>
                                    <span class="badge bg-success me-2">Disetujui</span>
                                    <?php elseif ($jurnal['status'] == 'dikoreksi'): ?>
                                    <span class="badge bg-warning me-2">Perlu Revisi</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary me-2">Menunggu Review</span>
                                    <?php endif; ?>
                                    <?php if ($jurnal['nilai']): ?>
                                    <span class="badge bg-primary">Nilai: <?= $jurnal['nilai'] ?></span>
                                    <?php endif; ?>
                                </button>
                            </h2>
                            <div id="jurnal<?= $jurnal['id'] ?>" class="accordion-collapse collapse <?= $j == 1 ? 'show' : '' ?>">
                                <div class="accordion-body">
                                    <strong>Aktivitas:</strong>
                                    <p><?= nl2br($jurnal['aktivitas']) ?></p>
                                    
                                    <?php if ($jurnal['file_penunjang']): ?>
                                    <p>
                                        <strong>File:</strong>
                                        <a href="../assets/uploads/<?= $jurnal['file_penunjang'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file"></i> Lihat File
                                        </a>
                                    </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($jurnal['feedback']): ?>
                                    <div class="alert alert-info">
                                        <strong>Feedback Mentor:</strong><br>
                                        <?= $jurnal['feedback'] ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php $j++; endwhile; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Tidak ada jurnal untuk bulan ini.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Statistik Jurnal -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik Jurnal</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-success"><?= $stat['disetujui'] ?? 0 ?></h4>
                            <small>Disetujui</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-warning"><?= $stat['dikoreksi'] ?? 0 ?></h4>
                            <small>Dikoreksi</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-secondary"><?= $stat['pending'] ?? 0 ?></h4>
                            <small>Pending</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5>Total: <?= $stat['total'] ?? 0 ?> jurnal</h5>
                    </div>
                </div>
            </div>
            
            <!-- Tips -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Tips Menulis Jurnal</h5>
                </div>
                <div class="card-body">
                    <ul class="ps-3 mb-0">
                        <li>Tuliskan aktivitas secara detail</li>
                        <li>Sertakan hasil atau output yang dicapai</li>
                        <li>Jelaskan kendala yang dihadapi (jika ada)</li>
                        <li>Upload file penunjang jika memungkinkan</li>
                        <li>Isi jurnal setiap hari kerja</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
