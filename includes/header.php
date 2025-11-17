<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Magang</title>
    
    <!-- Bootstrap CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-briefcase-fill"></i> Sistem Magang
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'mahasiswa'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_mahasiswa.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lowongan_list.php">Lowongan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="mentor_list.php">Mentor</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="lamaran_status.php">Status Lamaran</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="jurnal_mahasiswa.php">Jurnal</a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'mentor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_mentor.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="pemagang_list.php">Pemagang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="jurnal_monitor.php">Monitor Jurnal</a>
                            </li>
                        <?php elseif ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard_admin.php">Dashboard</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    Laporan
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="laporan1.php">Laporan 1</a></li>
                                    <li><a class="dropdown-item" href="laporan2.php">Laporan 2</a></li>
                                    <li><a class="dropdown-item" href="laporan3.php">Laporan 3</a></li>
                                    <li><a class="dropdown-item" href="laporan4.php">Laporan 4</a></li>
                                    <li><a class="dropdown-item" href="laporan5.php">Laporan 5</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= $_SESSION['nama'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="profile_<?= $_SESSION['role'] ?>.php">
                                        <i class="bi bi-person"></i> Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="../logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="home.php">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tentang.php">Tentang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>