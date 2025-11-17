<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Statistik
$query_mahasiswa = "SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'";
$total_mahasiswa = mysqli_fetch_assoc(mysqli_query($conn, $query_mahasiswa))['total'];

$query_mentor = "SELECT COUNT(*) as total FROM users WHERE role = 'mentor'";
$total_mentor = mysqli_fetch_assoc(mysqli_query($conn, $query_mentor))['total'];

$query_lowongan = "SELECT COUNT(*) as total FROM lowongan WHERE status = 'open'";
$total_lowongan = mysqli_fetch_assoc(mysqli_query($conn, $query_lowongan))['total'];

$query_lamaran = "SELECT COUNT(*) as total FROM lamaran";
$total_lamaran = mysqli_fetch_assoc(mysqli_query($conn, $query_lamaran))['total'];

// Data Mahasiswa
$query_list_mahasiswa = "SELECT * FROM users WHERE role = 'mahasiswa' ORDER BY id DESC";
$result_mahasiswa = mysqli_query($conn, $query_list_mahasiswa);

// Data Mentor
$query_list_mentor = "SELECT u.*, m.keahlian, m.bio, m.status_open FROM users u JOIN mentors m ON u.id = m.user_id WHERE u.role = 'mentor' ORDER BY u.id DESC";
$result_mentor = mysqli_query($conn, $query_list_mentor);

// Data Lowongan
$query_list_lowongan = "SELECT l.*, u.nama as nama_mentor FROM lowongan l JOIN users u ON l.mentor_id = u.id ORDER BY l.id DESC";
$result_lowongan = mysqli_query($conn, $query_list_lowongan);
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
                            <h6 class="card-title mb-0">Total Mahasiswa</h6>
                            <h2 class="mt-2 mb-0"><?= $total_mahasiswa ?></h2>
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
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Lowongan Aktif</h6>
                            <h2 class="mt-2 mb-0"><?= $total_lowongan ?></h2>
                        </div>
                        <i class="bi bi-briefcase display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Lamaran</h6>
                            <h2 class="mt-2 mb-0"><?= $total_lamaran ?></h2>
                        </div>
                        <i class="bi bi-file-text display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Master Data Mahasiswa -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people"></i> Master Data Mahasiswa</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addMahasiswaModal">
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
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($result_mahasiswa, 0);
                        while ($row = mysqli_fetch_assoc($result_mahasiswa)): 
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editMahasiswaModal<?= $row['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteMahasiswaModal<?= $row['id'] ?>">
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
                                    <?= $row['status_open'] ? 'Open' : 'Closed' ?>
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
    
    <!-- Master Data Lowongan -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-briefcase"></i> Master Data Lowongan</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addLowonganModal">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Mentor</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($result_lowongan, 0);
                        while ($row = mysqli_fetch_assoc($result_lowongan)): 
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['judul'] ?></td>
                            <td><?= $row['nama_mentor'] ?></td>
                            <td>
                                <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                                <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $row['status'] == 'open' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editLowonganModal<?= $row['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteLowonganModal<?= $row['id'] ?>">
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