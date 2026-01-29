<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$current_time = date('H:i:s');

// Cek status pendaftaran magang - harus diterima untuk bisa absen
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

// Cek absensi hari ini
$query_absensi_today = "SELECT * FROM absensi WHERE user_id = '$user_id' AND tanggal = '$today'";
$result_absensi_today = mysqli_query($conn, $query_absensi_today);
$absensi_today = mysqli_fetch_assoc($result_absensi_today);

// Process absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'absen_masuk' && !$absensi_today) {
        $query = "INSERT INTO absensi (user_id, tanggal, jam_masuk, status) VALUES (?, ?, ?, 'hadir')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $today, $current_time);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Absen Masuk Berhasil!',
                text: 'Jam masuk: " . date('H:i') . "',
                confirmButtonText: 'OK'
            }).then(() => { window.location.reload(); });
        </script>";
    } elseif ($action == 'absen_keluar' && $absensi_today && !$absensi_today['jam_keluar']) {
        $query = "UPDATE absensi SET jam_keluar = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $current_time, $absensi_today['id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Absen Pulang Berhasil!',
                text: 'Jam keluar: " . date('H:i') . "',
                confirmButtonText: 'OK'
            }).then(() => { window.location.reload(); });
        </script>";
    } elseif ($action == 'izin' || $action == 'sakit') {
        $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');
        
        if (!$absensi_today) {
            $query = "INSERT INTO absensi (user_id, tanggal, status, keterangan) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $today, $action, $keterangan);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Pengajuan " . ucfirst($action) . " Berhasil!',
                    confirmButtonText: 'OK'
                }).then(() => { window.location.reload(); });
            </script>";
        }
    }
    
    // Refresh data
    $result_absensi_today = mysqli_query($conn, $query_absensi_today);
    $absensi_today = mysqli_fetch_assoc($result_absensi_today);
}

// Ambil riwayat absensi
$bulan = $_GET['bulan'] ?? date('Y-m');
$query_riwayat = "SELECT * FROM absensi WHERE user_id = '$user_id' AND tanggal LIKE '$bulan%' ORDER BY tanggal DESC";
$result_riwayat = mysqli_query($conn, $query_riwayat);

// Statistik bulan ini
$query_stat = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
              FROM absensi WHERE user_id = '$user_id' AND tanggal LIKE '$bulan%'";
$result_stat = mysqli_query($conn, $query_stat);
$stat = mysqli_fetch_assoc($result_stat);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-calendar-check"></i> Absensi Kehadiran
    </h2>
    
    <!-- Absensi Hari Ini -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clock"></i> Absensi Hari Ini - <?= date('d F Y') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (!$absensi_today): ?>
                        <p class="text-muted mb-3">Anda belum absen hari ini</p>
                        <form method="POST" class="d-grid gap-2">
                            <input type="hidden" name="action" value="absen_masuk">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Absen Masuk
                            </button>
                        </form>
                        <hr>
                        <p class="mb-2"><small>Tidak bisa hadir?</small></p>
                        <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#izinModal">
                            <i class="bi bi-envelope"></i> Ajukan Izin
                        </button>
                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#sakitModal">
                            <i class="bi bi-hospital"></i> Ajukan Sakit
                        </button>
                    <?php elseif ($absensi_today['status'] == 'hadir' && !$absensi_today['jam_keluar']): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Anda sudah absen masuk
                            <p class="mb-0 mt-2"><strong>Jam Masuk:</strong> <?= date('H:i', strtotime($absensi_today['jam_masuk'])) ?></p>
                        </div>
                        <form method="POST" class="d-grid">
                            <input type="hidden" name="action" value="absen_keluar">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-box-arrow-right"></i> Absen Pulang
                            </button>
                        </form>
                    <?php elseif ($absensi_today['status'] == 'hadir' && $absensi_today['jam_keluar']): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-check-circle"></i> Absensi hari ini sudah lengkap
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <h6>Jam Masuk</h6>
                                <h4 class="text-success"><?= date('H:i', strtotime($absensi_today['jam_masuk'])) ?></h4>
                            </div>
                            <div class="col-6">
                                <h6>Jam Keluar</h6>
                                <h4 class="text-danger"><?= date('H:i', strtotime($absensi_today['jam_keluar'])) ?></h4>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-<?= $absensi_today['status'] == 'izin' ? 'warning' : 'info' ?>">
                            <i class="bi bi-<?= $absensi_today['status'] == 'izin' ? 'envelope' : 'hospital' ?>"></i>
                            Status hari ini: <strong><?= ucfirst($absensi_today['status']) ?></strong>
                            <?php if ($absensi_today['keterangan']): ?>
                            <p class="mb-0 mt-2"><small>Keterangan: <?= $absensi_today['keterangan'] ?></small></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik Bulan Ini</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <h4 class="text-success"><?= $stat['hadir'] ?? 0 ?></h4>
                            <small>Hadir</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-warning"><?= $stat['izin'] ?? 0 ?></h4>
                            <small>Izin</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-info"><?= $stat['sakit'] ?? 0 ?></h4>
                            <small>Sakit</small>
                        </div>
                        <div class="col-3">
                            <h4 class="text-danger"><?= $stat['alpha'] ?? 0 ?></h4>
                            <small>Alpha</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h5>Total Kehadiran: <?= $stat['total'] ?? 0 ?> hari</h5>
                    </div>
                </div>
            </div>
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
    
    <!-- Riwayat Absensi -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="bi bi-list"></i> Riwayat Absensi - <?= date('F Y', strtotime($bulan . '-01')) ?></h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-' ?></td>
                            <td><?= $row['jam_keluar'] ? date('H:i', strtotime($row['jam_keluar'])) : '-' ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'hadir' ? 'success' : ($row['status'] == 'izin' ? 'warning' : ($row['status'] == 'sakit' ? 'info' : 'danger')) ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= $row['keterangan'] ?: '-' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> Tidak ada data absensi untuk bulan ini.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Izin -->
<div class="modal fade" id="izinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope"></i> Ajukan Izin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="izin">
                    <div class="mb-3">
                        <label class="form-label">Keterangan Izin</label>
                        <textarea class="form-control" name="keterangan" rows="3" required placeholder="Jelaskan alasan izin Anda"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Ajukan Izin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sakit -->
<div class="modal fade" id="sakitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-hospital"></i> Ajukan Sakit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="sakit">
                    <div class="mb-3">
                        <label class="form-label">Keterangan Sakit</label>
                        <textarea class="form-control" name="keterangan" rows="3" required placeholder="Jelaskan kondisi Anda"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">Ajukan Sakit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
