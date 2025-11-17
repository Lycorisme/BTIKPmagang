<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

$user_id = $_SESSION['user_id'];

// Ambil daftar lamaran yang diterima untuk pilihan mentor
$query_lamaran = "SELECT l.mentor_id, u.nama as nama_mentor, lo.judul 
                  FROM lamaran l 
                  JOIN users u ON l.mentor_id = u.id 
                  JOIN lowongan lo ON l.lowongan_id = lo.id
                  WHERE l.user_id = '$user_id' AND l.status = 'diterima'";
$result_lamaran = mysqli_query($conn, $query_lamaran);

// Ambil histori jurnal
$query_jurnal = "SELECT j.*, u.nama as nama_mentor 
                 FROM jurnal j 
                 JOIN users u ON j.mentor_id = u.id 
                 WHERE j.user_id = '$user_id' 
                 ORDER BY j.tanggal DESC";
$result_jurnal = mysqli_query($conn, $query_jurnal);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-journal-text"></i> Progress & Jurnal Magang
    </h2>
    
    <!-- Form Input Jurnal -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-journal-plus"></i> Tambah Jurnal Baru</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="../process/jurnal_process.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="mentor_id" class="form-label">Pilih Mentor/Lowongan</label>
                        <select class="form-select" id="mentor_id" name="mentor_id" required>
                            <option value="">-- Pilih Mentor --</option>
                            <?php 
                            mysqli_data_seek($result_lamaran, 0);
                            while ($row = mysqli_fetch_assoc($result_lamaran)): 
                            ?>
                                <option value="<?= $row['mentor_id'] ?>">
                                    <?= $row['nama_mentor'] ?> - <?= $row['judul'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="aktivitas" class="form-label">Aktivitas/Kegiatan Hari Ini</label>
                        <textarea class="form-control" id="aktivitas" name="aktivitas" rows="4" required placeholder="Deskripsikan aktivitas dan progress Anda hari ini..."></textarea>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="file_penunjang" class="form-label">Upload File Pendukung (Opsional)</label>
                        <input type="file" class="form-control" id="file_penunjang" name="file_penunjang" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">Format: PDF, JPG, PNG. Maksimal 2MB</div>
                    </div>
                    
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Jurnal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Histori Jurnal -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Histori Jurnal</h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result_jurnal) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Mentor</th>
                                <th>Aktivitas</th>
                                <th>File</th>
                                <th>Feedback</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result_jurnal)): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['nama_mentor'] ?></td>
                                <td><?= substr($row['aktivitas'], 0, 100) ?>...</td>
                                <td>
                                    <?php if ($row['file_penunjang']): ?>
                                        <a href="../assets/uploads/<?= $row['file_penunjang'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['feedback']): ?>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#feedbackModal<?= $row['id'] ?>">
                                            <i class="bi bi-chat-dots"></i> Lihat
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['nilai']): ?>
                                        <span class="badge bg-success"><?= $row['nilai'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <!-- Modal Feedback -->
                            <?php if ($row['feedback']): ?>
                            <div class="modal fade" id="feedbackModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="bi bi-chat-dots"></i> Feedback Mentor
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Tanggal:</strong> <?= date('d F Y', strtotime($row['tanggal'])) ?></p>
                                            <p><strong>Mentor:</strong> <?= $row['nama_mentor'] ?></p>
                                            <p><strong>Nilai:</strong> 
                                                <?= $row['nilai'] ? '<span class="badge bg-success">' . $row['nilai'] . '</span>' : '<span class="badge bg-secondary">Belum dinilai</span>' ?>
                                            </p>
                                            <hr>
                                            <p><strong>Feedback:</strong></p>
                                            <p><?= nl2br($row['feedback']) ?></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> Belum ada jurnal yang tercatat. Mulai isi jurnal Anda sekarang!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>