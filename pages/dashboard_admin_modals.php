<!-- File ini berisi semua modal untuk dashboard admin -->
<!-- Letakkan sebelum closing tag body di dashboard_admin.php -->

<!-- ========== MODAL TAMBAH USER UNIVERSAL ========== -->

<!-- Modal Tambah User (dengan pilihan role) -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="mb-3">
                        <label for="role_user" class="form-label">Role User <span class="text-danger">*</span></label>
                        <select class="form-select" id="role_user" name="role" required onchange="toggleRoleFields()">
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="peserta_magang">Peserta Magang</option>
                            <option value="mentor">Mentor</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_user" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_user" name="nama" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email_user" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email_user" name="email" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_user" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_user" name="password" required>
                    </div>
                    
                    <!-- Field khusus untuk Mentor -->
                    <div id="mentorFieldsAdd" style="display: none;">
                        <hr>
                        <h6 class="text-success"><i class="bi bi-person-badge"></i> Data Mentor</h6>
                        <div class="mb-3">
                            <label for="keahlian_user" class="form-label">Keahlian</label>
                            <input type="text" class="form-control" id="keahlian_user" name="keahlian" placeholder="contoh: Web Development, Mobile App, Data Science">
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio_user" class="form-label">Bio/Deskripsi</label>
                            <textarea class="form-control" id="bio_user" name="bio" rows="3" placeholder="Ceritakan tentang pengalaman dan keahlian"></textarea>
                        </div>
                    </div>
                    
                    <!-- Field khusus untuk Peserta Magang -->
                    <div id="pesertaFieldsAdd" style="display: none;">
                        <hr>
                        <h6 class="text-primary"><i class="bi bi-person"></i> Data Peserta Magang</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="tel" class="form-control" name="no_hp" placeholder="08123456789">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <select class="form-select" name="jenis_kelamin">
                                    <option value="">Pilih</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Instansi</label>
                                <select class="form-select" name="jenis_instansi">
                                    <option value="">Pilih</option>
                                    <option value="SMK">SMK</option>
                                    <option value="Universitas">Universitas</option>
                                    <option value="Politeknik">Politeknik</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Instansi</label>
                                <input type="text" class="form-control" name="nama_instansi" placeholder="Nama sekolah/kampus">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jurusan</label>
                                <input type="text" class="form-control" name="jurusan" placeholder="Jurusan/Prodi">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIM/NIS</label>
                                <input type="text" class="form-control" name="nim_nis" placeholder="NIM atau NIS">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleRoleFields() {
    const role = document.getElementById('role_user').value;
    const mentorFields = document.getElementById('mentorFieldsAdd');
    const pesertaFields = document.getElementById('pesertaFieldsAdd');
    
    mentorFields.style.display = role === 'mentor' ? 'block' : 'none';
    pesertaFields.style.display = role === 'peserta_magang' ? 'block' : 'none';
    
    // Toggle required untuk mentor fields
    document.getElementById('keahlian_user').required = role === 'mentor';
    document.getElementById('bio_user').required = role === 'mentor';
}
</script>

<!-- ========== MODAL PESERTA MAGANG ========== -->

<?php 
// Reset pointer untuk modal edit/delete peserta magang
if (isset($result_peserta)) {
    mysqli_data_seek($result_peserta, 0);
    while ($row = mysqli_fetch_assoc($result_peserta)): 
?>
<!-- Modal Edit Peserta Magang -->
<div class="modal fade" id="editPesertaModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Peserta Magang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_peserta_magang">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" value="<?= $row['nama'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= $row['email'] ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Peserta Magang -->
<div class="modal fade" id="deletePesertaModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Peserta Magang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_peserta_magang">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <p>Apakah Anda yakin ingin menghapus peserta magang <strong><?= $row['nama'] ?></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
    endwhile;
}
?>

<!-- ========== MODAL MENTOR ========== -->

<!-- Modal Tambah Mentor -->
<div class="modal fade" id="addMentorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Mentor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_mentor">
                    
                    <div class="mb-3">
                        <label for="nama_mentor" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_mentor" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_mentor" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_mentor" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_mentor" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_mentor" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keahlian_mentor" class="form-label">Keahlian</label>
                        <input type="text" class="form-control" id="keahlian_mentor" name="keahlian" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio_mentor" class="form-label">Bio/Deskripsi</label>
                        <textarea class="form-control" id="bio_mentor" name="bio" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
if (isset($result_mentor)) {
    mysqli_data_seek($result_mentor, 0);
    while ($row = mysqli_fetch_assoc($result_mentor)): 
?>
<!-- Modal Edit Mentor -->
<div class="modal fade" id="editMentorModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Mentor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_mentor">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama" value="<?= $row['nama'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= $row['email'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Keahlian</label>
                        <input type="text" class="form-control" name="keahlian" value="<?= $row['keahlian'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bio/Deskripsi</label>
                        <textarea class="form-control" name="bio" rows="3" required><?= $row['bio'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status_open" required>
                            <option value="1" <?= $row['status_open'] == 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= $row['status_open'] == 0 ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Mentor -->
<div class="modal fade" id="deleteMentorModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Mentor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_mentor">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <p>Apakah Anda yakin ingin menghapus mentor <strong><?= $row['nama'] ?></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
    endwhile;
}
?>