<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Ambil semua jurnal dari pemagang mentor ini
$query = "SELECT j.*, u.nama as nama_mahasiswa, u.email as email_mahasiswa 
          FROM jurnal j 
          JOIN users u ON j.user_id = u.id 
          WHERE j.mentor_id = '$user_id' 
          ORDER BY j.tanggal DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-journal-check"></i> Monitoring Jurnal Pemagang
    </h2>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?filter=all" class="btn btn-outline-primary <?= !isset($_GET['filter']) || $_GET['filter'] == 'all' ? 'active' : '' ?>">
                    Semua Jurnal
                </a>
                <a href="?filter=pending" class="btn btn-outline-warning <?= isset($_GET['filter']) && $_GET['filter'] == 'pending' ? 'active' : '' ?>">
                    Belum Direview
                </a>
                <a href="?filter=reviewed" class="btn btn-outline-success <?= isset($_GET['filter']) && $_GET['filter'] == 'reviewed' ? 'active' : '' ?>">
                    Sudah Direview
                </a>
            </div>
        </div>
    </div>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Mahasiswa</th>
                                <th>Aktivitas</th>
                                <th>File</th>
                                <th>Feedback</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td>
                                    <?= $row['nama_mahasiswa'] ?><br>
                                    <small class="text-muted"><?= $row['email_mahasiswa'] ?></small>
                                </td>
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
                                        <span class="badge bg-warning text-dark">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['nilai']): ?>
                                        <span class="badge bg-primary"><?= $row['nilai'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal<?= $row['id'] ?>">
                                        <i class="bi bi-chat-dots"></i>
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="bi bi-eye"></i> Detail Jurnal
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Tanggal:</strong></div>
                                                <div class="col-md-9"><?= date('d F Y', strtotime($row['tanggal'])) ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Mahasiswa:</strong></div>
                                                <div class="col-md-9"><?= $row['nama_mahasiswa'] ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Aktivitas:</strong></div>
                                                <div class="col-md-9"><?= nl2br($row['aktivitas']) ?></div>
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
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Feedback:</strong></div>
                                                <div class="col-md-9"><?= nl2br($row['feedback']) ?></div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-3"><strong>Nilai:</strong></div>
                                                <div class="col-md-9">
                                                    <?= $row['nilai'] ? '<span class="badge bg-primary fs-6">' . $row['nilai'] . '</span>' : '<span class="text-muted">Belum dinilai</span>' ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal Feedback -->
                            <div class="modal fade" id="feedbackModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="bi bi-chat-dots"></i> Berikan Feedback & Nilai
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="../process/jurnal_process.php">
                                            <div class="modal-body">
                                                <input type="hidden" name="action" value="update_feedback">
                                                <input type="hidden" name="jurnal_id" value="<?= $row['id'] ?>">
                                                
                                                <div class="alert alert-info">
                                                    <strong>Mahasiswa:</strong> <?= $row['nama_mahasiswa'] ?><br>
                                                    <strong>Tanggal:</strong> <?= date('d F Y', strtotime($row['tanggal'])) ?>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="feedback<?= $row['id'] ?>" class="form-label">Feedback</label>
                                                    <textarea class="form-control" id="feedback<?= $row['id'] ?>" name="feedback" rows="4" required><?= $row['feedback'] ?></textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="nilai<?= $row['id'] ?>" class="form-label">Nilai (0-100)</label>
                                                    <input type="number" class="form-control" id="nilai<?= $row['id'] ?>" name="nilai" min="0" max="100" step="0.1" value="<?= $row['nilai'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Feedback</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada jurnal dari pemagang Anda.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>