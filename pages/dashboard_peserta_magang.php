<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];

// Cek status biodata
$query_biodata = "SELECT pm.*, pm.surat_pengantar FROM peserta_magang pm WHERE pm.user_id = '$user_id'";
$result_biodata = mysqli_query($conn, $query_biodata);
$biodata = mysqli_fetch_assoc($result_biodata);
$biodata_lengkap = $biodata && $biodata['status_biodata'] == 'lengkap';
$surat_uploaded = $biodata && !empty($biodata['surat_pengantar']);

// Cek status pendaftaran magang dari tabel baru
$query_pendaftaran = "SELECT pd.*, u.nama as nama_mentor 
                      FROM pendaftaran_magang pd 
                      LEFT JOIN users u ON pd.mentor_id = u.id 
                      WHERE pd.user_id = '$user_id' 
                      ORDER BY pd.id DESC LIMIT 1";
$result_pendaftaran = mysqli_query($conn, $query_pendaftaran);
$pendaftaran = mysqli_fetch_assoc($result_pendaftaran);

// Statistik absensi
$query_absensi = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
                  FROM absensi WHERE user_id = '$user_id'";
$result_absensi = mysqli_query($conn, $query_absensi);
$stat_absensi = mysqli_fetch_assoc($result_absensi);

// Statistik jurnal
$query_jurnal = "SELECT COUNT(*) as total FROM jurnal WHERE user_id = '$user_id'";
$result_jurnal = mysqli_query($conn, $query_jurnal);
$total_jurnal = mysqli_fetch_assoc($result_jurnal)['total'];

// Cek sertifikat
$query_sertifikat = "SELECT * FROM sertifikat WHERE user_id = '$user_id'";
$result_sertifikat = mysqli_query($conn, $query_sertifikat);
$sertifikat = mysqli_fetch_assoc($result_sertifikat);

// Cek apakah magang sudah selesai
$magang_selesai = $pendaftaran && $pendaftaran['status'] == 'selesai';

// Cek absensi hari ini
$today = date('Y-m-d');
$query_absensi_today = "SELECT * FROM absensi WHERE user_id = '$user_id' AND tanggal = '$today'";
$result_absensi_today = mysqli_query($conn, $query_absensi_today);
$absensi_today = mysqli_fetch_assoc($result_absensi_today);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-speedometer2"></i> Dashboard Peserta Magang
    </h2>
    
    <?php if (!$biodata_lengkap || !$surat_uploaded): ?>
    <!-- Alert: Wajib Lengkapi Data -->
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5><i class="bi bi-exclamation-triangle"></i> Perhatian!</h5>
        <p>Untuk dapat mendaftar magang/PKL, Anda wajib melengkapi:</p>
        <ul class="mb-0">
            <?php if (!$biodata_lengkap): ?>
            <li>Biodata dan data asal instansi pendidikan</li>
            <?php endif; ?>
            <?php if (!$surat_uploaded): ?>
            <li>Upload surat pengantar magang dari instansi pendidikan</li>
            <?php endif; ?>
        </ul>
        <a href="profile_peserta_magang.php" class="btn btn-warning mt-2">
            <i class="bi bi-pencil"></i> Lengkapi Data
        </a>
    </div>
    <?php endif; ?>
    
    <!-- Status Pendaftaran Magang -->
    <?php if ($pendaftaran): ?>
    <div class="card mb-4 border-<?= $pendaftaran['status'] == 'diterima' ? 'success' : ($pendaftaran['status'] == 'pending' ? 'warning' : ($pendaftaran['status'] == 'selesai' ? 'info' : 'danger')) ?>">
        <div class="card-header bg-<?= $pendaftaran['status'] == 'diterima' ? 'success' : ($pendaftaran['status'] == 'pending' ? 'warning' : ($pendaftaran['status'] == 'selesai' ? 'info' : 'danger')) ?> text-white">
            <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Status Pendaftaran Magang/PKL</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Tanggal Daftar:</strong> <?= date('d F Y', strtotime($pendaftaran['tgl_daftar'])) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-<?= $pendaftaran['status'] == 'diterima' ? 'success' : ($pendaftaran['status'] == 'pending' ? 'warning' : ($pendaftaran['status'] == 'selesai' ? 'info' : 'danger')) ?>">
                            <?= ucfirst($pendaftaran['status']) ?>
                        </span>
                    </p>
                    <?php if ($pendaftaran['status'] == 'diterima' || $pendaftaran['status'] == 'selesai'): ?>
                    <p><strong>Periode Magang:</strong><br>
                        <?= date('d F Y', strtotime($pendaftaran['tgl_mulai'])) ?> - 
                        <?= date('d F Y', strtotime($pendaftaran['tgl_selesai'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if ($pendaftaran['mentor_id'] && ($pendaftaran['status'] == 'diterima' || $pendaftaran['status'] == 'selesai')): ?>
                    <p><strong>Mentor Pembimbing:</strong><br>
                        <span class="text-primary"><i class="bi bi-person-badge"></i> <?= $pendaftaran['nama_mentor'] ?></span>
                    </p>
                    <?php endif; ?>
                    <?php if ($pendaftaran['catatan_admin']): ?>
                    <p><strong>Catatan Admin:</strong><br>
                        <em><?= $pendaftaran['catatan_admin'] ?></em>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Hadir</h6>
                            <h2 class="mt-2 mb-0"><?= $stat_absensi['hadir'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-calendar-check display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Jurnal</h6>
                            <h2 class="mt-2 mb-0"><?= $total_jurnal ?></h2>
                        </div>
                        <i class="bi bi-journal-text display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Izin/Sakit</h6>
                            <h2 class="mt-2 mb-0"><?= ($stat_absensi['izin'] ?? 0) + ($stat_absensi['sakit'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-<?= $magang_selesai ? 'info' : 'secondary' ?> text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Sertifikat</h6>
                            <h5 class="mt-2 mb-0"><?= $magang_selesai ? 'Tersedia' : 'Belum' ?></h5>
                        </div>
                        <i class="bi bi-award display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if ($pendaftaran && $pendaftaran['status'] == 'diterima'): ?>
                        <!-- Absensi Hari Ini -->
                        <div class="col-md-3">
                            <a href="absensi.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-calendar-check display-6"></i><br>
                                <?php if (!$absensi_today): ?>
                                Absen Masuk
                                <?php elseif (!$absensi_today['jam_keluar']): ?>
                                Absen Pulang
                                <?php else: ?>
                                Riwayat Absensi
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="jurnal_peserta.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-journal-plus display-6"></i><br>Isi Jurnal
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$pendaftaran && $biodata_lengkap && $surat_uploaded): ?>
                        <div class="col-md-4">
                            <a href="daftar_magang.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-clipboard-plus display-6"></i><br>Daftar Magang/PKL
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3">
                            <a href="profile_peserta_magang.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-person-circle display-6"></i><br>Edit Profil
                            </a>
                        </div>
                        
                        <?php if ($magang_selesai): ?>
                        <div class="col-md-3">
                            <a href="cetak_sertifikat.php?id=<?= $pendaftaran['id'] ?>" class="btn btn-outline-info w-100 py-3" target="_blank">
                                <i class="bi bi-award display-6"></i><br>Cetak Sertifikat
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($pendaftaran && $pendaftaran['status'] == 'diterima'): ?>
    <!-- Absensi Bulan Ini -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar3"></i> Riwayat Absensi Bulan Ini</h5>
                </div>
                <div class="card-body">
                    <?php
                    $bulan_ini = date('Y-m');
                    $query_absensi_bulan = "SELECT * FROM absensi WHERE user_id = '$user_id' AND tanggal LIKE '$bulan_ini%' ORDER BY tanggal DESC LIMIT 10";
                    $result_absensi_bulan = mysqli_query($conn, $query_absensi_bulan);
                    ?>
                    <?php if (mysqli_num_rows($result_absensi_bulan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($abs = mysqli_fetch_assoc($result_absensi_bulan)): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($abs['tanggal'])) ?></td>
                                    <td><?= $abs['jam_masuk'] ? date('H:i', strtotime($abs['jam_masuk'])) : '-' ?></td>
                                    <td><?= $abs['jam_keluar'] ? date('H:i', strtotime($abs['jam_keluar'])) : '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $abs['status'] == 'hadir' ? 'success' : ($abs['status'] == 'izin' ? 'warning' : ($abs['status'] == 'sakit' ? 'info' : 'danger')) ?>">
                                            <?= ucfirst($abs['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $abs['keterangan'] ?: '-' ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="absensi.php" class="btn btn-info">Lihat Semua Absensi</a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Belum ada data absensi bulan ini.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
