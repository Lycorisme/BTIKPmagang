<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Statistik
$query_peserta = "SELECT COUNT(*) as total FROM users WHERE role = 'peserta_magang'";
$total_peserta = mysqli_fetch_assoc(mysqli_query($conn, $query_peserta))['total'];

$query_mentor = "SELECT COUNT(*) as total FROM users WHERE role = 'mentor'";
$total_mentor = mysqli_fetch_assoc(mysqli_query($conn, $query_mentor))['total'];

$query_pendaftaran = "SELECT COUNT(*) as total FROM pendaftaran_magang WHERE status = 'pending'";
$result_pend = mysqli_query($conn, $query_pendaftaran);
$total_pendaftaran = $result_pend ? mysqli_fetch_assoc($result_pend)['total'] : 0;

$query_aktif = "SELECT COUNT(*) as total FROM pendaftaran_magang WHERE status = 'diterima'";
$result_aktif = mysqli_query($conn, $query_aktif);
$total_aktif = $result_aktif ? mysqli_fetch_assoc($result_aktif)['total'] : 0;

// Data Peserta Magang
$query_list_peserta = "SELECT u.*, pm.nama_instansi, pm.jurusan, pm.jenis_instansi 
                       FROM users u 
                       LEFT JOIN peserta_magang pm ON u.id = pm.user_id 
                       WHERE u.role = 'peserta_magang' 
                       ORDER BY u.id DESC";
$result_peserta = mysqli_query($conn, $query_list_peserta);

// Data Mentor
$query_list_mentor = "SELECT u.*, m.keahlian, m.bio, m.status_open 
                      FROM users u 
                      JOIN mentors m ON u.id = m.user_id 
                      WHERE u.role = 'mentor' 
                      ORDER BY u.id DESC";
$result_mentor = mysqli_query($conn, $query_list_mentor);

// Pendaftaran Baru
$query_pendaftaran_baru = "SELECT pd.*, u.nama, u.email, pm.nama_instansi, pm.jurusan, pm.surat_pengantar
                           FROM pendaftaran_magang pd
                           JOIN users u ON pd.user_id = u.id
                           LEFT JOIN peserta_magang pm ON u.id = pm.user_id
                           WHERE pd.status = 'pending'
                           ORDER BY pd.tgl_daftar DESC";
$result_pendaftaran_baru = mysqli_query($conn, $query_pendaftaran_baru);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-speedometer2"></i> Dashboard Admin
    </h2>
    
    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Peserta Magang</h6>
                            <h2 class="mt-2 mb-0"><?= $total_peserta ?></h2>
                        </div>
                        <i class="bi bi-people display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Mentor</h6>
                            <h2 class="mt-2 mb-0"><?= $total_mentor ?></h2>
                        </div>
                        <i class="bi bi-person-badge display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Pendaftaran Baru</h6>
                            <h2 class="mt-2 mb-0"><?= $total_pendaftaran ?></h2>
                        </div>
                        <i class="bi bi-clipboard-check display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Magang Aktif</h6>
                            <h2 class="mt-2 mb-0"><?= $total_aktif ?></h2>
                        </div>
                        <i class="bi bi-person-check display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pendaftaran Baru Menunggu Verifikasi -->
    <?php if ($result_pendaftaran_baru && mysqli_num_rows($result_pendaftaran_baru) > 0): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bell"></i> Pendaftaran Magang Baru Menunggu Verifikasi</h5>
            <a href="kelola_pendaftaran.php" class="btn btn-sm btn-dark">Lihat Semua</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Asal Instansi</th>
                            <th>Jurusan</th>
                            <th>Tanggal Daftar</th>
                            <th>Surat Pengantar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_pendaftaran_baru)): ?>
                        <tr>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['nama_instansi'] ?></td>
                            <td><?= $row['jurusan'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['tgl_daftar'])) ?></td>
                            <td>
                                <?php if ($row['surat_pengantar']): ?>
                                <a href="../assets/uploads/<?= $row['surat_pengantar'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-pdf"></i> Lihat
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="kelola_pendaftaran.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Master Data Peserta Magang -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people"></i> Master Data Peserta Magang</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle"></i> Tambah User
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Asal Instansi</th>
                            <th>Jurusan</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($result_peserta, 0);
                        while ($row = mysqli_fetch_assoc($result_peserta)): 
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['nama_instansi'] ?: '-' ?> <?= $row['jenis_instansi'] ? "({$row['jenis_instansi']})" : '' ?></td>
                            <td><?= $row['jurusan'] ?: '-' ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editPesertaModal<?= $row['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deletePesertaModal<?= $row['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Master Data Mentor -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Master Data Mentor</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addMentorModal">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Keahlian</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($result_mentor, 0);
                        while ($row = mysqli_fetch_assoc($result_mentor)): 
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['keahlian'] ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status_open'] ? 'success' : 'secondary' ?>">
                                    <?= $row['status_open'] ? 'Aktif' : 'Non-Aktif' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editMentorModal<?= $row['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMentorModal<?= $row['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Include semua modal
include 'dashboard_admin_modals.php'; 
?>

<?php include '../includes/footer.php'; ?>