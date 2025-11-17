<!-- File ini berisi semua modal untuk dashboard admin -->
<!-- Letakkan sebelum closing tag body di dashboard_admin.php -->

<!-- ========== MODAL MAHASISWA ========== -->

<!-- Modal Tambah Mahasiswa -->
<div class="modal fade" id="addMahasiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_mahasiswa">
                    
                    <div class="mb-3">
                        <label for="nama_mhs" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_mhs" name="nama" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email_mhs" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_mhs" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_mhs" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_mhs" name="password" required>
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
// Reset pointer untuk modal edit/delete mahasiswa
mysqli_data_seek($result_mahasiswa, 0);
while ($row = mysqli_fetch_assoc($result_mahasiswa)): 
?>
<!-- Modal Edit Mahasiswa -->
<div class="modal fade" id="editMahasiswaModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_mahasiswa">
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

<!-- Modal Delete Mahasiswa -->
<div class="modal fade" id="deleteMahasiswaModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_mahasiswa">
                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                    <p>Apakah Anda yakin ingin menghapus mahasiswa <strong><?= $row['nama'] ?></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>

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
                            <option value="1" <?= $row['status_open'] == 1 ? 'selected' : '' ?>>Menerima Pemagang</option>
                            <option value="0" <?= $row['status_open'] == 0 ? 'selected' : '' ?>>Tidak Tersedia</option>
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
<?php endwhile; ?>

<!-- ========== MODAL LOWONGAN ========== -->

<!-- Modal Tambah Lowongan -->
<div class="modal fade" id="addLowonganModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Lowongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_lowongan">
                    
                    <div class="mb-3">
                        <label for="judul_lowongan" class="form-label">Judul Lowongan</label>
                        <input type="text" class="form-control" id="judul_lowongan" name="judul" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi_lowongan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi_lowongan" name="deskripsi" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mentor_lowongan" class="form-label">Mentor</label>
                        <select class="form-select" id="mentor_lowongan" name="mentor_id" required>
                            <option value="">Pilih Mentor</option>
                            <?php 
                            mysqli_data_seek($result_mentor, 0);
                            while ($m = mysqli_fetch_assoc($result_mentor)): 
                            ?>
                                <option value="<?= $m['id'] ?>"><?= $m['nama'] ?> - <?= $m['keahlian'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tgl_mulai_lowongan" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="tgl_mulai_lowongan" name="tgl_mulai" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tgl_selesai_lowongan" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="tgl_selesai_lowongan" name="tgl_selesai" required>
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
mysqli_data_seek($result_lowongan, 0);
while ($row = mysqli_fetch_assoc($result_lowongan)): 
?>
<!-- Modal Edit Lowongan -->
<div class="modal fade" id="editLowonganModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Lowongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_lowongan">
                    <input type="hidden" name="lowongan_id" value="<?= $row['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Judul Lowongan</label>
                        <input type="text" class="form-control" name="judul" value="<?= $row['judul'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" required><?= $row['deskripsi'] ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mentor</label>
                        <select class="form-select" name="mentor_id" required>
                            <?php 
                            mysqli_data_seek($result_mentor, 0);
                            while ($m = mysqli_fetch_assoc($result_mentor)): 
                            ?>
                                <option value="<?= $m['id'] ?>" <?= $m['id'] == $row['mentor_id'] ? 'selected' : '' ?>>
                                    <?= $m['nama'] ?> - <?= $m['keahlian'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tgl_mulai" value="<?= $row['tgl_mulai'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="tgl_selesai" value="<?= $row['tgl_selesai'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="open" <?= $row['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="closed" <?= $row['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
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

<!-- Modal Delete Lowongan -->
<div class="modal fade" id="deleteLowonganModal<?= $row['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Hapus Lowongan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="../process/admin_process.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_lowongan">
                    <input type="hidden" name="lowongan_id" value="<?= $row['id'] ?>">
                    <p>Apakah Anda yakin ingin menghapus lowongan <strong><?= $row['judul'] ?></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endwhile; ?>