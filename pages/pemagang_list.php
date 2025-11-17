<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Ambil semua lamaran untuk mentor ini
$query = "SELECT l.*, u.nama as nama_mahasiswa, u.email as email_mahasiswa, lo.judul as judul_lowongan 
          FROM lamaran l 
          JOIN users u ON l.user_id = u.id 
          JOIN lowongan lo ON l.lowongan_id = lo.id 
          WHERE l.mentor_id = '$user_id' 
          ORDER BY l.tgl_melamar DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-people-fill"></i> Daftar Pemagang & Lamaran
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
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mahasiswa</th>
                                <th>Email</th>
                                <th>Lowongan</th>
                                <th>Tanggal Melamar</th>
                                <th>Status</th>
                                <th>CV</th>
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
                                <td><?= $row['nama_mahasiswa'] ?></td>
                                <td><?= $row['email_mahasiswa'] ?></td>
                                <td><?= $row['judul_lowongan'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tgl_melamar'])) ?></td>
                                <td>
                                    <?php if ($row['status'] == 'proses'): ?>
                                        <span class="badge bg-warning text-dark">Proses</span>
                                    <?php elseif ($row['status'] == 'diterima'): ?>
                                        <span class="badge bg-success">Diterima</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
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
                                    <?php if ($row['status'] == 'proses'): ?>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $row['id'] ?>">
                                            <i class="bi bi-check-circle"></i> Terima
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $row['id'] ?>">
                                            <i class="bi bi-x-circle"></i> Tolak
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    <?php endif; ?>
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
                                                <p>Terima lamaran dari <strong><?= $row['nama_mahasiswa'] ?></strong> untuk lowongan <strong><?= $row['judul_lowongan'] ?></strong>?</p>
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
                                                <p>Tolak lamaran dari <strong><?= $row['nama_mahasiswa'] ?></strong> untuk lowongan <strong><?= $row['judul_lowongan'] ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Ya, Tolak</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail Lamaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>Nama:</strong></div>
                                                <div class="col-8"><?= $row['nama_mahasiswa'] ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>Email:</strong></div>
                                                <div class="col-8"><?= $row['email_mahasiswa'] ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>Lowongan:</strong></div>
                                                <div class="col-8"><?= $row['judul_lowongan'] ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>Tanggal:</strong></div>
                                                <div class="col-8"><?= date('d F Y', strtotime($row['tgl_melamar'])) ?></div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>Status:</strong></div>
                                                <div class="col-8">
                                                    <?php if ($row['status'] == 'proses'): ?>
                                                        <span class="badge bg-warning text-dark">Proses</span>
                                                    <?php elseif ($row['status'] == 'diterima'): ?>
                                                        <span class="badge bg-success">Diterima</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Ditolak</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($row['file_cv']): ?>
                                            <div class="row mb-2">
                                                <div class="col-4"><strong>CV:</strong></div>
                                                <div class="col-8">
                                                    <a href="../assets/uploads/<?= $row['file_cv'] ?>" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-file-pdf"></i> Download CV
                                                    </a>
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
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada lamaran untuk lowongan Anda.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>