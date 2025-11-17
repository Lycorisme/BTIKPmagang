<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mahasiswa']);

// Ambil semua mentor
$query = "SELECT u.*, m.keahlian, m.bio, m.status_open, m.foto,
          (SELECT COUNT(*) FROM lowongan WHERE mentor_id = m.user_id AND status = 'open') as jumlah_lowongan
          FROM users u 
          JOIN mentors m ON u.id = m.user_id 
          WHERE u.role = 'mentor'
          ORDER BY m.status_open DESC, u.nama ASC";
$result = mysqli_query($conn, $query);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-people-fill"></i> Daftar Mentor
    </h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row g-4">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-person-circle display-4 text-primary"></i>
                        <h5 class="card-title mt-3"><?= $row['nama'] ?></h5>
                        <p class="text-muted"><?= $row['email'] ?></p>
                        
                        <div class="mb-2">
                            <span class="badge bg-<?= $row['status_open'] ? 'success' : 'secondary' ?>">
                                <?= $row['status_open'] ? 'Menerima Pemagang' : 'Tidak Tersedia' ?>
                            </span>
                        </div>
                        
                        <hr>
                        
                        <div class="text-start mb-3">
                            <strong><i class="bi bi-star-fill text-warning"></i> Keahlian:</strong>
                            <p class="mb-2"><?= $row['keahlian'] ?></p>
                            
                            <strong><i class="bi bi-info-circle text-info"></i> Bio:</strong>
                            <p class="mb-2"><?= substr($row['bio'], 0, 100) ?>...</p>
                            
                            <strong><i class="bi bi-briefcase text-primary"></i> Lowongan Tersedia:</strong>
                            <p><?= $row['jumlah_lowongan'] ?> lowongan</p>
                        </div>
                        
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#mentorModal<?= $row['id'] ?>">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal Detail Mentor -->
            <div class="modal fade" id="mentorModal<?= $row['id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-person-badge"></i> Detail Mentor
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-4">
                                <i class="bi bi-person-circle display-1 text-primary"></i>
                                <h4 class="mt-3"><?= $row['nama'] ?></h4>
                                <p class="text-muted"><?= $row['email'] ?></p>
                                <span class="badge bg-<?= $row['status_open'] ? 'success' : 'secondary' ?> fs-6">
                                    <?= $row['status_open'] ? 'Menerima Pemagang' : 'Tidak Tersedia' ?>
                                </span>
                            </div>
                            
                            <hr>
                            
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Keahlian:</strong></div>
                                <div class="col-md-9"><?= $row['keahlian'] ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Bio:</strong></div>
                                <div class="col-md-9"><?= nl2br($row['bio']) ?></div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Lowongan Tersedia:</strong></div>
                                <div class="col-md-9"><?= $row['jumlah_lowongan'] ?> lowongan aktif</div>
                            </div>
                            
                            <?php if ($row['jumlah_lowongan'] > 0): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="bi bi-info-circle"></i> Mentor ini memiliki lowongan yang tersedia. 
                                    <a href="lowongan_list.php">Lihat semua lowongan</a>
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
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada mentor tersedia saat ini.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>