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
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($result_jurnal, 0);
                            while ($row = mysqli_fetch_assoc($result_jurnal)): 
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td><?= $row['nama_mentor'] ?></td>
                                <td><?= substr($row['aktivitas'], 0, 80) ?>...</td>
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
                                        <span class="badge bg-success">Sudah</span>
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
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Modal Detail LENGKAP -->
                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">
                                                <i class="bi bi-journal-text"></i> Detail Jurnal
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Tanggal:</strong></div>
                                                <div class="col-md-9"><?= date('d F Y', strtotime($row['tanggal'])) ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Mentor:</strong></div>
                                                <div class="col-md-9"><?= $row['nama_mentor'] ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Aktivitas:</strong></div>
                                                <div class="col-md-9"><?= nl2br(htmlspecialchars($row['aktivitas'])) ?></div>
                                            </div>
                                            <?php if ($row['file_penunjang']): ?>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>File Pendukung:</strong></div>
                                                <div class="col-md-9">
                                                    <a href="../assets/uploads/<?= $row['file_penunjang'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-download"></i> Download File
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['feedback']): ?>
                                            <hr>
                                            <h6 class="text-primary"><i class="bi bi-chat-dots"></i> Feedback Mentor:</h6>
                                            <div class="alert alert-light">
                                                <?= nl2br(htmlspecialchars($row['feedback'])) ?>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Nilai:</strong></div>
                                                <div class="col-md-9">
                                                    <?= $row['nilai'] ? '<span class="badge bg-success fs-6">' . $row['nilai'] . '</span>' : '<span class="text-muted">Belum dinilai</span>' ?>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <hr>
                                            <div class="alert alert-warning">
                                                <i class="bi bi-info-circle"></i> Belum ada feedback dari mentor
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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