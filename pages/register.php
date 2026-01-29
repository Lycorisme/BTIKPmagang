<?php 
session_start();
include '../includes/header.php';
include '../includes/auth.php';
redirectIfLoggedIn();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">
                        <i class="bi bi-person-plus-fill"></i> Registrasi Peserta Magang/PKL
                    </h3>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Informasi:</strong> Halaman ini khusus untuk pendaftaran Peserta Magang/PKL. 
                        Akun mentor dan admin akan dibuat oleh Administrator.
                    </div>
                    
                    <form id="registerForm" method="POST" action="../process/register_process.php">
                        <!-- Role tersembunyi, hanya peserta_magang yang bisa daftar -->
                        <input type="hidden" name="role" value="peserta_magang">
                        
                        <h5 class="mb-3 text-primary"><i class="bi bi-person"></i> Data Akun</h5>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3 text-primary"><i class="bi bi-card-text"></i> Data Diri</h5>
                        
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No. HP/WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="no_hp" name="no_hp" required placeholder="contoh: 08123456789">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2" required placeholder="Masukkan alamat lengkap Anda"></textarea>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3 text-primary"><i class="bi bi-building"></i> Asal Instansi Pendidikan</h5>
                        
                        <div class="mb-3">
                            <label for="jenis_instansi" class="form-label">Jenis Instansi <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_instansi" name="jenis_instansi" required>
                                <option value="">Pilih Jenis Instansi</option>
                                <option value="SMK">SMK (Sekolah Menengah Kejuruan)</option>
                                <option value="Universitas">Universitas/Perguruan Tinggi</option>
                                <option value="Politeknik">Politeknik</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_instansi" class="form-label">Nama Instansi Pendidikan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_instansi" name="nama_instansi" required placeholder="contoh: SMK Negeri 1 Samarinda / Universitas Mulawarman">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jurusan" class="form-label">Jurusan/Program Studi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="jurusan" name="jurusan" required placeholder="contoh: Teknik Informatika">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="semester_kelas" class="form-label">Semester/Kelas <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="semester_kelas" name="semester_kelas" required placeholder="contoh: Semester 5 / Kelas XI">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nim_nis" class="form-label">NIM/NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nim_nis" name="nim_nis" required placeholder="Masukkan NIM atau NIS Anda">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-person-plus-fill"></i> Daftar Sekarang
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

<?php include '../includes/footer.php'; ?>