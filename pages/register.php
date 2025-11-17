<?php 
session_start();
include '../includes/header.php'; 
include '../includes/auth.php';
redirectIfLoggedIn();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-person-plus-fill"></i> Registrasi
                    </h3>
                    
                    <form id="registerForm" method="POST" action="../process/register_process.php">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Daftar Sebagai</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Pilih Role</option>
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="mentor">Mentor</option>
                            </select>
                        </div>
                        
                        <!-- Field khusus untuk Mentor -->
                        <div id="mentorFields" style="display: none;">
                            <div class="mb-3">
                                <label for="keahlian" class="form-label">Keahlian</label>
                                <input type="text" class="form-control" id="keahlian" name="keahlian" placeholder="contoh: Web Development, Mobile App, Data Science">
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio/Deskripsi</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Ceritakan tentang pengalaman dan keahlian Anda"></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-person-plus-fill"></i> Daftar
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <p class="text-center mb-0">
                        Sudah punya akun? <a href="login.php">Login Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tampilkan field mentor jika role mentor dipilih
document.getElementById('role').addEventListener('change', function() {
    const mentorFields = document.getElementById('mentorFields');
    if (this.value === 'mentor') {
        mentorFields.style.display = 'block';
        document.getElementById('keahlian').required = true;
        document.getElementById('bio').required = true;
    } else {
        mentorFields.style.display = 'none';
        document.getElementById('keahlian').required = false;
        document.getElementById('bio').required = false;
    }
});
</script>

<?php include '../includes/footer.php'; ?>