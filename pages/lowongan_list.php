<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

// Ambil semua lowongan yang open
$query = "SELECT l.*, u.nama as nama_mentor, m.keahlian 
          FROM lowongan l 
          JOIN mentors m ON l.mentor_id = m.user_id 
          JOIN users u ON m.user_id = u.id 
          WHERE l.status = 'open' 
          ORDER BY l.id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-briefcase-fill"></i> Daftar Lowongan Magang
    </h2>
    
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="Cari lowongan..." value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="keahlian" placeholder="Filter berdasarkan keahlian..." value="<?= isset($_GET['keahlian']) ? $_GET['keahlian'] : '' ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title"><?= $row['judul'] ?></h5>
                            <span class="badge bg-success">Open</span>
                        </div>
                        
                        <p class="card-text text-muted">
                            <?= substr($row['deskripsi'], 0, 150) ?>...
                        </p>
                        
                        <div class="mb-2">
                            <i class="bi bi-person-badge text-primary"></i>
                            <strong>Mentor:</strong> <?= $row['nama_mentor'] ?>
                        </div>
                        
                        <div class="mb-2">
                            <i class="bi bi-star-fill text-warning"></i>
                            <strong>Keahlian:</strong> <?= $row['keahlian'] ?>
                        </div>
                        
                        <div class="mb-3">
                            <i class="bi bi-calendar-check text-info"></i>
                            <strong>Periode:</strong> 
                            <?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - 
                            <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?>
                        </div>
                        
                        <a href="lowongan_detail.php?id=<?= $row['id'] ?>" class="btn btn-primary w-100">
                            <i class="bi bi-eye"></i> Lihat Detail & Lamar
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Tidak ada lowongan tersedia saat ini.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>