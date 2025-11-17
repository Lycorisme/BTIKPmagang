<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

$user_id = $_SESSION['user_id'];

// Ambil data admin
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Statistik untuk dashboard admin
$query_total_users = "SELECT 
    COUNT(CASE WHEN role = 'mahasiswa' THEN 1 END) as total_mahasiswa,
    COUNT(CASE WHEN role = 'mentor' THEN 1 END) as total_mentor,
    COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admin
    FROM users";
$result_stats = mysqli_query($conn, $query_total_users);
$stats = mysqli_fetch_assoc($result_stats);

// Aktivitas terakhir
$query_activity = "SELECT 
    'Mahasiswa Baru' as activity_type,
    nama as activity_name,
    created_at as activity_time
    FROM users 
    WHERE role = 'mahasiswa'
    ORDER BY created_at DESC 
    LIMIT 5";
$result_activity = mysqli_query($conn, $query_activity);
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Profile -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                    </div>
                    <h4 class="mb-2"><?= htmlspecialchars($user['nama']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-danger fs-6 mb-4">
                        <i class="bi bi-shield-check"></i> Administrator
                    </span>
                    
                    <div class="text-start mt-4">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">User ID:</span>
                            <strong>#<?= str_pad($user['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Role:</span>
                            <strong><?= ucfirst($user['role']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted">Bergabung:</span>
                            <strong><?= date('d M Y', strtotime($user['created_at'])) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Status:</span>
                            <span class="badge bg-success">Active</span>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil-square"></i> Edit Profil
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="bi bi-key-fill"></i> Ubah Password
                        </button>
                        <a href="dashboard_admin.php" class="btn btn-outline-primary">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Info Akun -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Informasi Akun
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bi bi-person text-primary"></i> Nama Lengkap:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= htmlspecialchars($user['nama']) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bi bi-envelope text-primary"></i> Email:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= htmlspecialchars($user['email']) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bi bi-shield-check text-primary"></i> Role:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge bg-danger">Administrator</span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bi bi-calendar-check text-primary"></i> Terdaftar Sejak:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= date('d F Y, H:i', strtotime($user['created_at'])) ?> WIB
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <strong><i class="bi bi-clock-history text-primary"></i> Last Login:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= date('d F Y, H:i') ?> WIB
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistik Singkat -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up"></i> Statistik Sistem
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-people display-4 text-primary"></i>
                                <h3 class="mt-2 mb-0"><?= $stats['total_mahasiswa'] ?></h3>
                                <p class="text-muted mb-0">Mahasiswa</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-person-badge display-4 text-success"></i>
                                <h3 class="mt-2 mb-0"><?= $stats['total_mentor'] ?></h3>
                                <p class="text-muted mb-0">Mentor</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <i class="bi bi-shield-check display-4 text-danger"></i>
                                <h3 class="mt-2 mb-0"><?= $stats['total_admin'] ?></h3>
                                <p class="text-muted mb-0">Admin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Aktivitas Terakhir -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Aktivitas Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result_activity) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while($activity = mysqli_fetch_assoc($result_activity)): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-person-plus-fill text-primary me-2"></i>
                                    <strong><?= htmlspecialchars($activity['activity_name']) ?></strong> 
                                    <span class="text-muted">bergabung sebagai mahasiswa</span>
                                </div>
                                <small class="text-muted">
                                    <?= date('d M Y, H:i', strtotime($activity['activity_time'])) ?>
                                </small>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Belum ada aktivitas terbaru
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning-fill"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="dashboard_admin.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-speedometer2"></i><br>
                                Dashboard Admin
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="laporan1.php" class="btn btn-outline-success w-100">
                                <i class="bi bi-file-earmark-bar-graph"></i><br>
                                Lihat Laporan
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-outline-info w-100" data-bs-toggle="modal" data-bs-target="#systemInfoModal">
                                <i class="bi bi-info-circle"></i><br>
                                Sistem Info
                            </button>
                        </div>
                        <div class="col-md-6">
                            <a href="../logout.php" class="btn btn-outline-danger w-100">
                                <i class="bi bi-box-arrow-right"></i><br>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Edit Profil Administrator
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_profile_admin">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Update informasi profil Anda di sini
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">
                            <i class="bi bi-person"></i> Nama Lengkap
                        </label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?= htmlspecialchars($user['nama']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Perhatian:</strong> Pastikan email yang Anda masukkan valid dan dapat diakses.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-key-fill"></i> Ubah Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php" id="changePasswordForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-shield-exclamation"></i> 
                        <strong>Keamanan:</strong> Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol.
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">
                            <i class="bi bi-lock"></i> Password Lama
                        </label>
                        <input type="password" class="form-control" id="password_lama" 
                               name="password_lama" required 
                               placeholder="Masukkan password lama Anda">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">
                            <i class="bi bi-lock-fill"></i> Password Baru
                        </label>
                        <input type="password" class="form-control" id="password_baru" 
                               name="password_baru" required 
                               placeholder="Masukkan password baru"
                               minlength="6">
                        <div class="form-text">Minimal 6 karakter</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">
                            <i class="bi bi-check-circle"></i> Konfirmasi Password Baru
                        </label>
                        <input type="password" class="form-control" id="konfirmasi_password" 
                               name="konfirmasi_password" required 
                               placeholder="Konfirmasi password baru"
                               minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key-fill"></i> Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal System Info -->
<div class="modal fade" id="systemInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle"></i> Informasi Sistem
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Nama Sistem:</strong></div>
                    <div class="col-md-8">Sistem Manajemen Magang BTIKP</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Versi:</strong></div>
                    <div class="col-md-8">1.0.0</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Framework:</strong></div>
                    <div class="col-md-8">PHP Native + Bootstrap 5</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Database:</strong></div>
                    <div class="col-md-8">MySQL</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Server:</strong></div>
                    <div class="col-md-8"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>PHP Version:</strong></div>
                    <div class="col-md-8"><?= phpversion() ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Total Users:</strong></div>
                    <div class="col-md-8">
                        <?= $stats['total_mahasiswa'] + $stats['total_mentor'] + $stats['total_admin'] ?> users
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4"><strong>Developer:</strong></div>
                    <div class="col-md-8">BTIKP Development Team</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Validation Script -->
<script>
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const passwordBaru = document.getElementById('password_baru').value;
    const konfirmasiPassword = document.getElementById('konfirmasi_password').value;
    
    if (passwordBaru !== konfirmasiPassword) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password Tidak Cocok!',
            text: 'Password baru dan konfirmasi password tidak sama.',
            confirmButtonText: 'OK'
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>