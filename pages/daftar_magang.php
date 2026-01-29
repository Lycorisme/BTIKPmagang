<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['peserta_magang']);

$user_id = $_SESSION['user_id'];

// Cek biodata lengkap
$query_biodata = "SELECT * FROM peserta_magang WHERE user_id = '$user_id' AND status_biodata = 'lengkap'";
$result_biodata = mysqli_query($conn, $query_biodata);
$biodata = mysqli_fetch_assoc($result_biodata);

if (!$biodata || empty($biodata['surat_pengantar'])) {
    ?>
    <div class="container my-5">
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle"></i> Data Belum Lengkap</h5>
            <p>Anda harus melengkapi biodata dan upload surat pengantar magang sebelum dapat mendaftar magang/PKL.</p>
            <a href="profile_peserta_magang.php" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Lengkapi Data
            </a>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit();
}

// Cek apakah sudah ada pendaftaran aktif
$query_existing = "SELECT * FROM pendaftaran_magang WHERE user_id = '$user_id' AND status IN ('pending', 'diterima')";
$result_existing = mysqli_query($conn, $query_existing);
if (mysqli_num_rows($result_existing) > 0) {
    $existing = mysqli_fetch_assoc($result_existing);
    ?>
    <div class="container my-5">
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Pendaftaran Sudah Ada</h5>
            <p>Anda sudah memiliki pendaftaran magang dengan status: <strong><?= ucfirst($existing['status']) ?></strong></p>
            <a href="dashboard_peserta_magang.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
    exit();
}

// Process pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tgl_mulai = mysqli_real_escape_string($conn, $_POST['tgl_mulai']);
    $tgl_selesai = mysqli_real_escape_string($conn, $_POST['tgl_selesai']);
    $today = date('Y-m-d');
    
    $query = "INSERT INTO pendaftaran_magang (user_id, tgl_daftar, tgl_mulai, tgl_selesai, status) 
              VALUES (?, ?, ?, ?, 'pending')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $today, $tgl_mulai, $tgl_selesai);
    
    if (mysqli_stmt_execute($stmt)) {
        ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Pendaftaran Berhasil!',
                text: 'Pendaftaran magang/PKL Anda telah dikirim. Silakan tunggu konfirmasi dari admin.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard_peserta_magang.php';
            });
        </script>
        <?php
    } else {
        ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Pendaftaran Gagal!',
                text: 'Terjadi kesalahan. Silakan coba lagi.',
                confirmButtonText: 'OK'
            });
        </script>
        <?php
    }
    mysqli_stmt_close($stmt);
}

// Ambil data user dan biodata
$query_user = "SELECT u.*, pm.* FROM users u 
               JOIN peserta_magang pm ON u.id = pm.user_id 
               WHERE u.id = '$user_id'";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="bi bi-clipboard-plus"></i> Daftar Magang/PKL
    </h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Formulir Pendaftaran Magang/PKL</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Informasi:</strong> Mentor pembimbing akan ditentukan oleh admin setelah pendaftaran Anda disetujui.
                        </div>
                        
                        <h5 class="mb-3 text-primary">Data Peserta</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" value="<?= $user['nama'] ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= $user['email'] ?>" readonly>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Asal Instansi</label>
                                <input type="text" class="form-control" value="<?= $user['nama_instansi'] ?> (<?= $user['jenis_instansi'] ?>)" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jurusan/Program Studi</label>
                                <input type="text" class="form-control" value="<?= $user['jurusan'] ?>" readonly>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3 text-primary">Rencana Magang</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Rencana Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tgl_mulai" required 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rencana Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tgl_selesai" required 
                                       min="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="agree" required>
                            <label class="form-check-label" for="agree">
                                Saya menyatakan bahwa data yang saya berikan adalah benar dan saya siap mengikuti peraturan magang/PKL di BTIKP
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send"></i> Kirim Pendaftaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h5>
                </div>
                <div class="card-body">
                    <h6>Alur Pendaftaran:</h6>
                    <ol class="ps-3">
                        <li>Lengkapi biodata dan upload surat pengantar ✓</li>
                        <li>Isi formulir pendaftaran magang</li>
                        <li>Tunggu verifikasi admin</li>
                        <li>Jika diterima, mentor akan ditetapkan oleh admin</li>
                        <li>Mulai magang sesuai jadwal</li>
                    </ol>
                    
                    <hr>
                    
                    <h6>Dokumen yang Diperlukan:</h6>
                    <ul class="ps-3">
                        <li>Surat pengantar dari instansi pendidikan ✓</li>
                        <li>Data diri lengkap ✓</li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-file-check"></i> Status Dokumen</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Biodata Lengkap
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-check-circle text-success"></i> Surat Pengantar
                        <?php if ($biodata['surat_pengantar']): ?>
                        <a href="../assets/uploads/<?= $biodata['surat_pengantar'] ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
