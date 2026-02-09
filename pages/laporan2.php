<?php
session_start();
require_once '../config/database.php';
include '../includes/header.php';
include '../includes/auth.php';

checkLogin();
checkRole(['admin']);

// Ambil data mentor dengan statistik (menggunakan tabel yang ada)
$query = "SELECT u.*, m.keahlian, m.bio, m.status_open,
          (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id) as total_bimbingan,
          (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id AND pm.status = 'diterima') as bimbingan_aktif,
          (SELECT COUNT(*) FROM pendaftaran_magang pm WHERE pm.mentor_id = u.id AND pm.status = 'selesai') as bimbingan_selesai
          FROM users u 
          LEFT JOIN mentors m ON u.id = m.user_id 
          WHERE u.role = 'mentor' 
          ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);

// Error handling - cek apakah query berhasil
if (!$result) {
    // Fallback: query sederhana jika tabel mentors tidak ada
    $query_simple = "SELECT * FROM users WHERE role = 'mentor' ORDER BY id DESC";
    $result = mysqli_query($conn, $query_simple);
    $is_simple_mode = true;
} else {
    $is_simple_mode = false;
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph"></i> Laporan Data Mentor</h2>
        <div class="btn-group">
            <a href="../process/export_pdf.php?type=laporan2" class="btn btn-danger" title="Download langsung sebagai PDF">
                <i class="bi bi-file-pdf"></i> Download PDF
            </a>
            <a href="../process/print_report.php?type=laporan2" class="btn btn-primary" target="_blank" title="Buka halaman cetak (alternatif jika download gagal)">
                <i class="bi bi-printer"></i> Cetak Laporan
            </a>
        </div>
    </div>
    
    <?php if (!$result || mysqli_num_rows($result) == 0): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Belum ada data mentor terdaftar.
    </div>
    <?php else: ?>
    
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Rekap Data Mentor & Aktivitas Pembimbingan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Mentor</th>
                            <th>Keahlian</th>
                            <th>Status</th>
                            <th>Total Bimbingan</th>
                            <th>Aktif</th>
                            <th>Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $total_mentor = 0;
                        $total_bimbingan_all = 0;
                        $total_selesai_all = 0;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $total_mentor++;
                            $total_bimbingan_all += $row['total_bimbingan'] ?? 0;
                            $total_selesai_all += $row['bimbingan_selesai'] ?? 0;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= htmlspecialchars($row['nama']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['keahlian'] ?? '-') ?></td>
                            <td>
                                <?php if (isset($row['status_open']) && $row['status_open']): ?>
                                    <span class="badge bg-success">Menerima</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tutup</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $row['total_bimbingan'] ?? 0 ?></td>
                            <td class="text-center">
                                <span class="badge bg-info"><?= $row['bimbingan_aktif'] ?? 0 ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success"><?= $row['bimbingan_selesai'] ?? 0 ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4">TOTAL</th>
                            <th class="text-center"><?= $total_bimbingan_all ?></th>
                            <th></th>
                            <th class="text-center"><?= $total_selesai_all ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-4">
                <h6>Ringkasan:</h6>
                <ul>
                    <li>Total Mentor Terdaftar: <strong><?= $total_mentor ?></strong></li>
                    <li>Total Bimbingan: <strong><?= $total_bimbingan_all ?></strong></li>
                    <li>Total Bimbingan Selesai: <strong><?= $total_selesai_all ?></strong></li>
                </ul>
            </div>
        </div>
        <div class="card-footer text-muted">
            Dicetak pada: <?= date('d F Y H:i:s') ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>