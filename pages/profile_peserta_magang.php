<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];

// Ambil data user dan biodata
$query = "SELECT u.*, pm.* FROM users u 
          LEFT JOIN peserta_magang pm ON u.id = pm.user_id 
          WHERE u.id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-person-circle"></i> Profil Peserta Magang
    </h2>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Form Update Profil -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Profil & Biodata</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="../process/admin_process.php">
                        <input type="hidden" name="action" value="update_profile_peserta_magang">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        
                        <!-- Data Akun -->
                        <h6 class="text-primary mb-3"><i class="bi bi-person"></i> Data Akun</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama" value="<?= $user['nama'] ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?= $user['email'] ?>" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Data Diri -->
                        <h6 class="text-primary mb-3"><i class="bi bi-card-text"></i> Data Diri</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP/WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="no_hp" value="<?= $user['no_hp'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" name="jenis_kelamin" required>
                                    <option value="">Pilih</option>
                                    <option value="L" <?= ($user['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= ($user['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_lahir" value="<?= $user['tanggal_lahir'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="alamat" rows="2" required><?= $user['alamat'] ?? '' ?></textarea>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Asal Instansi Pendidikan -->
                        <h6 class="text-primary mb-3"><i class="bi bi-building"></i> Asal Instansi Pendidikan</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Instansi <span class="text-danger">*</span></label>
                                <select class="form-select" name="jenis_instansi" required>
                                    <option value="">Pilih</option>
                                    <option value="SMK" <?= ($user['jenis_instansi'] ?? '') == 'SMK' ? 'selected' : '' ?>>SMK</option>
                                    <option value="Universitas" <?= ($user['jenis_instansi'] ?? '') == 'Universitas' ? 'selected' : '' ?>>Universitas</option>
                                    <option value="Politeknik" <?= ($user['jenis_instansi'] ?? '') == 'Politeknik' ? 'selected' : '' ?>>Politeknik</option>
                                    <option value="Lainnya" <?= ($user['jenis_instansi'] ?? '') == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Instansi Pendidikan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_instansi" value="<?= $user['nama_instansi'] ?? '' ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jurusan/Program Studi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="jurusan" value="<?= $user['jurusan'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester/Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="semester_kelas" value="<?= $user['semester_kelas'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">NIM/NIS <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nim_nis" value="<?= $user['nim_nis'] ?? '' ?>" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Form Ganti Password -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-key"></i> Ganti Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="../process/admin_process.php">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="password_lama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password_baru" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="konfirmasi_password" required>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Ganti Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Status Kelengkapan -->
            <div class="card mb-4">
                <div class="card-header bg-<?= ($user['status_biodata'] ?? '') == 'lengkap' ? 'success' : 'warning' ?> text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Status Kelengkapan</h5>
                </div>
                <div class="card-body">
                    <p>
                        <i class="bi bi-<?= ($user['status_biodata'] ?? '') == 'lengkap' ? 'check-circle text-success' : 'exclamation-circle text-warning' ?>"></i>
                        Biodata: <strong><?= ($user['status_biodata'] ?? 'belum_lengkap') == 'lengkap' ? 'Lengkap' : 'Belum Lengkap' ?></strong>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-<?= !empty($user['surat_pengantar']) ? 'check-circle text-success' : 'exclamation-circle text-warning' ?>"></i>
                        Surat Pengantar: <strong><?= !empty($user['surat_pengantar']) ? 'Sudah Upload' : 'Belum Upload' ?></strong>
                    </p>
                </div>
            </div>
            
            <!-- Upload Surat Pengantar -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Surat Pengantar Magang</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($user['surat_pengantar'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> File sudah diupload
                    </div>
                    <a href="../assets/uploads/<?= $user['surat_pengantar'] ?>" target="_blank" class="btn btn-outline-primary w-100 mb-3">
                        <i class="bi bi-eye"></i> Lihat File
                    </a>
                    <?php endif; ?>
                    
                    <form method="POST" action="../process/admin_process.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload_surat_pengantar">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label"><?= !empty($user['surat_pengantar']) ? 'Ganti File' : 'Upload File' ?></label>
                            <input type="file" class="form-control" name="surat_pengantar" accept=".pdf,.jpg,.jpeg,.png" required>
                            <small class="text-muted">Format: PDF, JPG, PNG (Max 5MB)</small>
                        </div>
                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-upload"></i> Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
