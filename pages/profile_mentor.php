<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['mentor']);

$user_id = $_SESSION['user_id'];

// Ambil data user dan mentor
$query = "SELECT u.*, m.keahlian, m.bio, m.status_open 
          FROM users u 
          JOIN mentors m ON u.id = m.user_id 
          WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-person-circle"></i> Profil Mentor
    </h2>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle display-1 text-primary"></i>
                        <h4 class="mt-3"><?= $user['nama'] ?></h4>
                        <p class="text-muted"><?= $user['email'] ?></p>
                        <span class="badge bg-primary">Mentor</span>
                        <span class="badge bg-<?= $user['status_open'] ? 'success' : 'secondary' ?>">
                            <?= $user['status_open'] ? 'Menerima Pemagang' : 'Tidak Tersedia' ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Nama:</strong></div>
                        <div class="col-md-8"><?= $user['nama'] ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Email:</strong></div>
                        <div class="col-md-8"><?= $user['email'] ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Keahlian:</strong></div>
                        <div class="col-md-8"><?= $user['keahlian'] ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Bio:</strong></div>
                        <div class="col-md-8"><?= nl2br($user['bio']) ?></div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Status:</strong></div>
                        <div class="col-md-8">
                            <span class="badge bg-<?= $user['status_open'] ? 'success' : 'secondary' ?>">
                                <?= $user['status_open'] ? 'Menerima Pemagang' : 'Tidak Tersedia' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Terdaftar Sejak:</strong></div>
                        <div class="col-md-8"><?= date('d F Y', strtotime($user['created_at'])) ?></div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal">
                            <i class="bi bi-pencil-square"></i> Edit Profil
                        </button>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            <i class="bi bi-key-fill"></i> Ubah Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Profil -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/mentor_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?= $user['nama'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $user['email'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keahlian" class="form-label">Keahlian</label>
                        <input type="text" class="form-control" id="keahlian" name="keahlian" value="<?= $user['keahlian'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio/Deskripsi</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3" required><?= $user['bio'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status_open" class="form-label">Status Penerimaan Pemagang</label>
                        <select class="form-select" id="status_open" name="status_open" required>
                            <option value="1" <?= $user['status_open'] == 1 ? 'selected' : '' ?>>Menerima Pemagang</option>
                            <option value="0" <?= $user['status_open'] == 0 ? 'selected' : '' ?>>Tidak Tersedia</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="passwordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key-fill"></i> Ubah Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/mentor_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                    
                    <div class="mb-3">
                        <label for="password_lama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_baru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Ubah Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>