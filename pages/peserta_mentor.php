<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Ambil peserta yang di-assign ke mentor ini
$query = "SELECT pd.*, u.id as user_id, u.nama, u.email, 
                 pm.nama_instansi, pm.jurusan, pm.jenis_instansi, pm.no_hp
          FROM pendaftaran_magang pd
          JOIN users u ON pd.user_id = u.id
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id
          WHERE pd.mentor_id = '$user_id' AND pd.status IN ('diterima', 'selesai')
          ORDER BY pd.status ASC, pd.tgl_mulai DESC";
$result = mysqli_query($conn, $query);

// Jika ada parameter user_id, tampilkan detail
$detail_user = null;
if (isset($_GET['user_id'])) {
    $detail_user_id = intval($_GET['user_id']);
    $query_detail = "SELECT pd.*, u.id as user_id, u.nama, u.email,
                            pm.nama_instansi, pm.jurusan, pm.jenis_instansi, pm.no_hp, pm.alamat, pm.nim_nis
                     FROM pendaftaran_magang pd
                     JOIN users u ON pd.user_id = u.id
                     LEFT JOIN peserta_magang pm ON u.id = pm.user_id
                     WHERE pd.mentor_id = '$user_id' AND u.id = '$detail_user_id'";
    $result_detail = mysqli_query($conn, $query_detail);
    $detail_user = mysqli_fetch_assoc($result_detail);
    
    // Ambil absensi peserta
    $query_absensi = "SELECT * FROM absensi WHERE user_id = '$detail_user_id' ORDER BY tanggal DESC LIMIT 10";
    $result_absensi = mysqli_query($conn, $query_absensi);
    
    // Ambil jurnal peserta
    $query_jurnal = "SELECT * FROM jurnal WHERE user_id = '$detail_user_id' ORDER BY tanggal DESC";
    $result_jurnal = mysqli_query($conn, $query_jurnal);
}

// Process penilaian jurnal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'nilai_jurnal') {
        $jurnal_id = intval($_POST['jurnal_id']);
        $nilai = floatval($_POST['nilai']);
        $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
        $status = $_POST['status_jurnal'];
        $tgl_koreksi = date('Y-m-d');
        
        $query_update = "UPDATE jurnal SET nilai = ?, feedback = ?, status = ?, tgl_koreksi = ?, mentor_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt, "dsssis", $nilai, $feedback, $status, $tgl_koreksi, $user_id, $jurnal_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>Swal.fire({icon:'success',title:'Berhasil!',text:'Jurnal berhasil dinilai'}).then(()=>location.reload());</script>";
    }
}
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-people-fill"></i> Peserta Magang dalam Bimbingan
    </h2>
    
    <?php if ($detail_user): ?>
    <!-- Detail Peserta -->
    <div class="mb-3">
        <a href="peserta_mentor.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Data Peserta</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th>Nama</th><td><?= $detail_user['nama'] ?></td></tr>
                        <tr><th>Email</th><td><?= $detail_user['email'] ?></td></tr>
                        <tr><th>No. HP</th><td><?= $detail_user['no_hp'] ?: '-' ?></td></tr>
                        <tr><th>NIM/NIS</th><td><?= $detail_user['nim_nis'] ?: '-' ?></td></tr>
                        <tr><th>Instansi</th><td><?= $detail_user['nama_instansi'] ?> (<?= $detail_user['jenis_instansi'] ?>)</td></tr>
                        <tr><th>Jurusan</th><td><?= $detail_user['jurusan'] ?></td></tr>
                        <tr><th>Periode</th><td><?= date('d/m/Y', strtotime($detail_user['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($detail_user['tgl_selesai'])) ?></td></tr>
                        <tr><th>Status</th><td>
                            <span class="badge bg-<?= $detail_user['status'] == 'diterima' ? 'success' : 'info' ?>">
                                <?= ucfirst($detail_user['status']) ?>
                            </span>
                        </td></tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Absensi Terbaru -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Riwayat Absensi Terbaru</h5>
                </div>
                <div class="card-body">
                    <?php if ($result_absensi && mysqli_num_rows($result_absensi) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($abs = mysqli_fetch_assoc($result_absensi)): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($abs['tanggal'])) ?></td>
                                    <td><?= $abs['jam_masuk'] ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?></td>
                                    <td><?= $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $abs['status'] == 'hadir' ? 'success' : ($abs['status'] == 'izin' ? 'warning' : ($abs['status'] == 'sakit' ? 'info' : 'danger')) ?>">
                                            <?= ucfirst($abs['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">Belum ada data absensi.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Jurnal -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Jurnal Aktivitas</h5>
                </div>
                <div class="card-body">
                    <?php if ($result_jurnal && mysqli_num_rows($result_jurnal) > 0): ?>
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
                                    
                                    <hr>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nilaiModal<?= $jurnal['id'] ?>">
                                        <i class="bi bi-pencil"></i> Beri Nilai/Feedback
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal Nilai -->
                        <div class="modal fade" id="nilaiModal<?= $jurnal['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Nilai Jurnal - <?= date('d/m/Y', strtotime($jurnal['tanggal'])) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="nilai_jurnal">
                                            <input type="hidden" name="jurnal_id" value="<?= $jurnal['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Nilai (0-100)</label>
                                                <input type="number" class="form-control" name="nilai" min="0" max="100" step="0.01" value="<?= $jurnal['nilai'] ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status_jurnal" required>
                                                    <option value="dikoreksi" <?= $jurnal['status'] == 'dikoreksi' ? 'selected' : '' ?>>Dikoreksi (Perlu Revisi)</option>
                                                    <option value="disetujui" <?= $jurnal['status'] == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Feedback</label>
                                                <textarea class="form-control" name="feedback" rows="3"><?= $jurnal['feedback'] ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php $j++; endwhile; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">Belum ada jurnal dari peserta ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Daftar Peserta -->
    <div class="card">
        <div class="card-body">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Asal Instansi</th>
                            <th>Jurusan</th>
                            <th>Periode</th>
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
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'diterima' ? 'success' : 'info' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="?user_id=<?= $row['user_id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> Belum ada peserta magang yang ditugaskan kepada Anda oleh Admin.
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
