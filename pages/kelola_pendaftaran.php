<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Process update status pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $pendaftaran_id = intval($_POST['pendaftaran_id'] ?? 0);
    
    if ($action == 'terima' && $pendaftaran_id > 0) {
        $mentor_id = intval($_POST['mentor_id']);
        $tgl_mulai = mysqli_real_escape_string($conn, $_POST['tgl_mulai']);
        $tgl_selesai = mysqli_real_escape_string($conn, $_POST['tgl_selesai']);
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
        $today = date('Y-m-d');
        
        $query = "UPDATE pendaftaran_magang SET 
                  status = 'diterima', 
                  mentor_id = ?, 
                  tgl_mulai = ?, 
                  tgl_selesai = ?, 
                  tgl_diterima = ?, 
                  catatan_admin = ?
                  WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issssi", $mentor_id, $tgl_mulai, $tgl_selesai, $today, $catatan, $pendaftaran_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>Swal.fire({icon:'success',title:'Berhasil!',text:'Peserta magang berhasil diterima'}).then(()=>location.reload());</script>";
    } elseif ($action == 'tolak' && $pendaftaran_id > 0) {
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');
        
        $query = "UPDATE pendaftaran_magang SET status = 'ditolak', catatan_admin = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $catatan, $pendaftaran_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>Swal.fire({icon:'info',title:'Berhasil!',text:'Pendaftaran telah ditolak'}).then(()=>location.reload());</script>";
    } elseif ($action == 'selesaikan' && $pendaftaran_id > 0) {
        $tgl_selesai_aktual = date('Y-m-d');
        
        $query = "UPDATE pendaftaran_magang SET status = 'selesai', tgl_selesai_aktual = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $tgl_selesai_aktual, $pendaftaran_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>Swal.fire({icon:'success',title:'Berhasil!',text:'Masa magang telah selesai'}).then(()=>location.reload());</script>";
    }
}

// Filter status
$status_filter = $_GET['status'] ?? 'all';
$where_clause = "";
if ($status_filter != 'all') {
    $where_clause = "WHERE pd.status = '$status_filter'";
}

// Ambil data pendaftaran
$query = "SELECT pd.*, u.nama, u.email, pm.nama_instansi, pm.jurusan, pm.jenis_instansi, pm.surat_pengantar, pm.no_hp,
                 mentor.nama as nama_mentor
          FROM pendaftaran_magang pd
          JOIN users u ON pd.user_id = u.id
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id
          LEFT JOIN users mentor ON pd.mentor_id = mentor.id
          $where_clause
          ORDER BY pd.tgl_daftar DESC";
$result = mysqli_query($conn, $query);

// Ambil daftar mentor untuk dropdown
$query_mentor = "SELECT u.id, u.nama, m.keahlian FROM users u 
                 JOIN mentors m ON u.id = m.user_id 
                 WHERE m.status_open = 1 
                 ORDER BY u.nama";
$result_mentor = mysqli_query($conn, $query_mentor);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-clipboard-check"></i> Kelola Pendaftaran Magang/PKL
    </h2>
    
    <!-- Filter Status -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?status=all" class="btn btn-outline-primary <?= $status_filter == 'all' ? 'active' : '' ?>">
                    Semua
                </a>
                <a href="?status=pending" class="btn btn-outline-warning <?= $status_filter == 'pending' ? 'active' : '' ?>">
                    Pending
                </a>
                <a href="?status=diterima" class="btn btn-outline-success <?= $status_filter == 'diterima' ? 'active' : '' ?>">
                    Diterima
                </a>
                <a href="?status=ditolak" class="btn btn-outline-danger <?= $status_filter == 'ditolak' ? 'active' : '' ?>">
                    Ditolak
                </a>
                <a href="?status=selesai" class="btn btn-outline-info <?= $status_filter == 'selesai' ? 'active' : '' ?>">
                    Selesai
                </a>
            </div>
        </div>
    </div>
    
    <!-- Tabel Pendaftaran -->
    <div class="card">
        <div class="card-body">
            <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Asal Instansi</th>
                            <th>Jurusan</th>
                            <th>Tanggal Daftar</th>
                            <th>Periode</th>
                            <th>Mentor</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= $row['nama'] ?><br>
                                <small class="text-muted"><?= $row['email'] ?></small>
                            </td>
                            <td><?= $row['nama_instansi'] ?> <small>(<?= $row['jenis_instansi'] ?>)</small></td>
                            <td><?= $row['jurusan'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_daftar'])) ?></td>
                            <td>
                                <?php if ($row['tgl_mulai']): ?>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['nama_mentor']): ?>
                                <span class="text-primary"><?= $row['nama_mentor'] ?></span>
                                <?php else: ?>
                                <span class="text-muted">Belum ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'diterima' ? 'success' : ($row['status'] == 'pending' ? 'warning' : ($row['status'] == 'selesai' ? 'info' : 'danger')) ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['surat_pengantar']): ?>
                                <a href="../assets/uploads/<?= $row['surat_pengantar'] ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Lihat Surat Pengantar">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($row['status'] == 'pending'): ?>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#terimaModal<?= $row['id'] ?>" title="Terima">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#tolakModal<?= $row['id'] ?>" title="Tolak">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                <?php elseif ($row['status'] == 'diterima'): ?>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#selesaiModal<?= $row['id'] ?>" title="Selesaikan Magang">
                                    <i class="bi bi-check2-all"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id'] ?>" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal Terima -->
                        <div class="modal fade" id="terimaModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title"><i class="bi bi-check-circle"></i> Terima Pendaftaran</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="terima">
                                            <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                            
                                            <p>Terima pendaftaran dari <strong><?= $row['nama'] ?></strong>?</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Pilih Mentor Pembimbing <span class="text-danger">*</span></label>
                                                <select class="form-select" name="mentor_id" required>
                                                    <option value="">Pilih Mentor</option>
                                                    <?php 
                                                    mysqli_data_seek($result_mentor, 0);
                                                    while ($m = mysqli_fetch_assoc($result_mentor)): 
                                                    ?>
                                                    <option value="<?= $m['id'] ?>"><?= $m['nama'] ?> - <?= $m['keahlian'] ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="tgl_mulai" value="<?= $row['tgl_mulai'] ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control" name="tgl_selesai" value="<?= $row['tgl_selesai'] ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Catatan (Opsional)</label>
                                                <textarea class="form-control" name="catatan" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Terima Pendaftaran</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Tolak -->
                        <div class="modal fade" id="tolakModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title"><i class="bi bi-x-circle"></i> Tolak Pendaftaran</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="tolak">
                                            <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                            
                                            <p>Tolak pendaftaran dari <strong><?= $row['nama'] ?></strong>?</p>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Alasan Penolakan</label>
                                                <textarea class="form-control" name="catatan" rows="3" placeholder="Berikan alasan penolakan"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak Pendaftaran</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Selesaikan -->
                        <div class="modal fade" id="selesaiModal<?= $row['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title"><i class="bi bi-check2-all"></i> Selesaikan Masa Magang</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="selesaikan">
                                            <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                                            
                                            <p>Selesaikan masa magang untuk <strong><?= $row['nama'] ?></strong>?</p>
                                            <p>Setelah diselesaikan, peserta dapat menerima sertifikat.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-info">Selesaikan</button>
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
                                        <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detail Pendaftaran</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-borderless">
                                            <tr><th width="40%">Nama</th><td><?= $row['nama'] ?></td></tr>
                                            <tr><th>Email</th><td><?= $row['email'] ?></td></tr>
                                            <tr><th>No. HP</th><td><?= $row['no_hp'] ?: '-' ?></td></tr>
                                            <tr><th>Asal Instansi</th><td><?= $row['nama_instansi'] ?> (<?= $row['jenis_instansi'] ?>)</td></tr>
                                            <tr><th>Jurusan</th><td><?= $row['jurusan'] ?></td></tr>
                                            <tr><th>Tanggal Daftar</th><td><?= date('d F Y', strtotime($row['tgl_daftar'])) ?></td></tr>
                                            <tr><th>Status</th><td><span class="badge bg-<?= $row['status'] == 'diterima' ? 'success' : ($row['status'] == 'pending' ? 'warning' : ($row['status'] == 'selesai' ? 'info' : 'danger')) ?>"><?= ucfirst($row['status']) ?></span></td></tr>
                                            <?php if ($row['nama_mentor']): ?>
                                            <tr><th>Mentor</th><td><?= $row['nama_mentor'] ?></td></tr>
                                            <?php endif; ?>
                                            <?php if ($row['catatan_admin']): ?>
                                            <tr><th>Catatan Admin</th><td><?= $row['catatan_admin'] ?></td></tr>
                                            <?php endif; ?>
                                        </table>
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
                <i class="bi bi-info-circle"></i> Tidak ada data pendaftaran.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
